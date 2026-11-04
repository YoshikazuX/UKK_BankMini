<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login utama
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        if (request('reset') === 'success' && ! session()->has('success')) {
            session()->flash('success', 'Kata sandi berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.');
        }

        $adminUser = User::where('role', 'admin')->first();
        $adminPhone = $adminUser->no_hp ?? '081234567890';

        return view('auth.login', compact('adminPhone'));
    }

    /**
     * Proses autentikasi login pengguna
     */
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()
            ->with('error', 'Username atau Password salah! Periksa kembali kredensial Anda.')
            ->withInput($request->only('username'));
    }

    /**
     * Logout pengguna dari sistem
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem secara aman.');
    }

    /**
     * Tampilkan antarmuka Lupa Password
     */
    public function showForgotPassword(): Response|View
    {
        $adminUser = User::where('role', 'admin')->first();
        $adminPhone = $adminUser->no_hp ?? '081234567890';

        return response()
            ->view('auth.forgot-password', compact('adminPhone'))
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    /**
     * Minta Kode OTP Verifikasi (WhatsApp / Email SMTP)
     */
    public function requestOtp(Request $request): RedirectResponse|JsonResponse
    {
        $identifier = trim((string) ($request->username ?? $request->email ?? ''));
        $manualUsername = trim((string) ($request->manual_username ?? ''));
        $manualContact = trim((string) ($request->manual_contact ?? ''));
        $metode = $request->input('metode', 'email');

        if ($identifier === '') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Silakan masukkan alamat email atau username akun Anda.'], 422);
            }

            return back()->with('error', 'Silakan masukkan alamat email atau username akun Anda.');
        }

        $accountType = 'user';
        $user = User::where('username', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        $nasabah = null;
        if (! $user) {
            $nasabah = Nasabah::where('username', $identifier)
                ->orWhere('nis', $identifier)
                ->orWhere('no_rekening', $identifier)
                ->orWhere('email', $identifier)
                ->first();
            if ($nasabah) {
                $accountType = 'nasabah';
            }
        }

        // Jika input berupa email yang belum tertaut di DB namun user menyertakan manual_username
        if (! $user && ! $nasabah && $manualUsername !== '') {
            $user = User::where('username', $manualUsername)->first();
            if (! $user) {
                $nasabah = Nasabah::where('username', $manualUsername)
                    ->orWhere('nis', $manualUsername)
                    ->orWhere('no_rekening', $manualUsername)
                    ->first();
                if ($nasabah) {
                    $accountType = 'nasabah';
                }
            }
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL) && $manualContact === '') {
                $manualContact = $identifier;
            }
        }

        if (! $user && ! $nasabah) {
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL) && $manualUsername === '') {
                $msg = 'Email "'.$identifier.'" belum tertaut pada akun di database. Silakan masukkan Username atau NIS akun yang ingin direset untuk mengirim OTP ke email ini.';
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'needs_username' => true,
                        'message' => $msg,
                    ], 422);
                }
            }

            $errMsg = 'Akun dengan identitas "'.($manualUsername ?: $identifier).'" tidak terdaftar dalam sistem. Hubungi administrator.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 404);
            }

            return back()->with('error', $errMsg);
        }

        $targetUsername = $accountType === 'nasabah' ? ($nasabah->username ?: $nasabah->nis) : $user->username;
        $targetNama = $accountType === 'nasabah' ? $nasabah->nama_siswa : $user->nama_petugas;
        $targetEmail = $accountType === 'nasabah' ? $nasabah->email : $user->email;
        $targetHp = $accountType === 'nasabah' ? $nasabah->no_hp : $user->no_hp;

        $rawContact = ($metode === 'whatsapp')
            ? $targetHp
            : ($manualContact ?: ($targetEmail ?: (filter_var($identifier, FILTER_VALIDATE_EMAIL) ? $identifier : '')));

        // Jika nomor HP atau Email belum disetel di akun
        if (empty($rawContact)) {
            $warnMsg = 'Akun "'.$targetUsername.'" ('.$targetNama.') belum memiliki alamat email yang terdaftar di profil. Silakan masukkan alamat email tujuan pengiriman OTP di bawah ini.';
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'needs_email' => true,
                    'message' => $warnMsg,
                ], 422);
            }

            return back()
                ->with('warning', $warnMsg)
                ->with('suggest_ticket', true)
                ->with('suggest_username', $targetUsername)
                ->with('suggest_nama', $targetNama);
        }

        // Simpan email ke profil akun jika sebelumnya masih kosong
        if ($metode === 'email' && filter_var($rawContact, FILTER_VALIDATE_EMAIL) && empty($targetEmail)) {
            if ($accountType === 'nasabah' && $nasabah) {
                $nasabah->email = $rawContact;
                $nasabah->save();
            } elseif ($user) {
                $user->email = $rawContact;
                $user->save();
            }
        }

        // Cegah spam OTP (Rate limiting 60 detik, kecuali resend eksplisit)
        if (! $request->boolean('force_resend')) {
            $recentRequest = PasswordResetRequest::where('username', $targetUsername)
                ->where('created_at', '>=', now()->subSeconds(60))
                ->first();
            if ($recentRequest) {
                $rateMsg = 'Terlalu sering meminta kode verifikasi. Harap tunggu 60 detik sebelum meminta kode baru.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $rateMsg], 429);
                }

                return back()->with('error', $rateMsg);
            }
        }

        // Generate 6 digit OTP acak dengan masa berlaku 15 menit
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(15);

        // Lindungi OTP dengan penyimpanan HASH Bcrypt
        $ticket = PasswordResetRequest::create([
            'user_id' => $user ? $user->id : null,
            'username' => $targetUsername,
            'nama_pemohon' => $targetNama,
            'metode' => $metode,
            'kontak' => $rawContact,
            'otp_code' => Hash::make($otp),
            'otp_expires_at' => $expiresAt,
            'status' => 'pending',
            'keterangan' => 'Kode OTP keamanan 15 menit dibuat (Tersimpan Hash)',
            'ip_address' => $request->ip(),
        ]);

        // Kirim Email OTP melalui SMTP jika metode adalah email
        if ($metode === 'email' && filter_var($rawContact, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::raw(
                    "Halo {$targetNama},\n\n"
                    ."Kami menerima permintaan pemulihan kata sandi untuk akun LKM Mitra Siswa Abadi ({$targetUsername}).\n\n"
                    ."Kode OTP Verifikasi Anda adalah: {$otp}\n\n"
                    ."Kode ini berlaku selama 15 menit. Demi keamanan perbankan, jangan berikan kode ini kepada siapa pun.\n\n"
                    ."Salam,\nLKM Mitra Siswa Abadi - SMK Negeri 1 Bantul",
                    function ($message) use ($rawContact, $targetNama) {
                        $message->to($rawContact, $targetNama)
                            ->subject('Kode OTP Pemulihan Kata Sandi - LKM Mitra Siswa Abadi');
                    }
                );
            } catch (\Throwable $e) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengirim email SMTP: '.$e->getMessage(),
                    ], 500);
                }

                return back()->with('error', 'Gagal mengirim email SMTP: '.$e->getMessage());
            }
        }

        $maskedContact = $this->maskContact($rawContact, $metode);

        // Buat pesan WhatsApp siap kirim jika metode WA
        $cleanWaNumber = preg_replace('/[^0-9]/', '', $rawContact);
        if (str_starts_with($cleanWaNumber, '0')) {
            $cleanWaNumber = '62'.substr($cleanWaNumber, 1);
        }
        $waMessage = urlencode("Halo {$targetNama}, kode OTP verifikasi reset password LKM Mitra Siswa Abadi Anda adalah: {$otp}. Kode berlaku selama 15 menit. Jaga kerahasiaan kode ini.");
        $waLink = "https://wa.me/{$cleanWaNumber}?text={$waMessage}";

        // Simpan state di session untuk verifikasi tahap 2
        session([
            'pwd_reset_step' => 'verify',
            'pwd_reset_account_type' => $accountType,
            'pwd_reset_user_id' => $accountType === 'nasabah' ? $nasabah->id : $user->id,
            'pwd_reset_ticket_id' => $ticket->id,
            'pwd_reset_username' => $targetUsername,
            'pwd_reset_nama' => $targetNama,
            'pwd_reset_metode' => $metode,
            'pwd_reset_masked_contact' => $maskedContact,
            'pwd_reset_raw_contact' => $rawContact,
            'pwd_reset_otp' => $otp,
            'pwd_reset_wa_link' => $waLink,
            'pwd_reset_expires_at' => $expiresAt->timestamp,
            'pwd_reset_attempts' => 0,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Kode OTP 6 digit telah dikirimkan ke {$maskedContact}.",
                'masked_contact' => $maskedContact,
            ]);
        }

        return back()->with('success', 'Kode verifikasi keamanan berhasil dibuat dan dikirimkan!');
    }

    /**
     * Verifikasi Kode OTP (Tahap 2)
     */
    public function verifyOtp(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'otp_code' => 'required|numeric',
        ], [
            'otp_code.required' => 'Masukkan 6 digit kode OTP verifikasi.',
            'otp_code.numeric' => 'Kode OTP harus berupa angka.',
        ]);

        $sessionOtp = session('pwd_reset_otp');
        $sessionUserId = session('pwd_reset_user_id');
        $expiresAt = session('pwd_reset_expires_at');

        if (! $sessionUserId || ! $sessionOtp) {
            $msg = 'Sesi pemulihan kata sandi telah berakhir. Silakan ulangi proses dari awal.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->route('password.request')->with('error', $msg);
        }

        // Batasi percobaan verifikasi OTP (Maksimal 5 kali percobaan)
        $attempts = (int) session('pwd_reset_attempts', 0) + 1;
        session(['pwd_reset_attempts' => $attempts]);

        if ($attempts > 5) {
            session()->forget([
                'pwd_reset_step',
                'pwd_reset_account_type',
                'pwd_reset_user_id',
                'pwd_reset_ticket_id',
                'pwd_reset_username',
                'pwd_reset_nama',
                'pwd_reset_metode',
                'pwd_reset_masked_contact',
                'pwd_reset_raw_contact',
                'pwd_reset_otp',
                'pwd_reset_otp_verified',
                'pwd_reset_wa_link',
                'pwd_reset_expires_at',
                'pwd_reset_attempts',
            ]);

            $msg = 'Batas percobaan OTP terlampaui (maksimal 5 kali). Sesi dibatalkan demi keamanan perbankan.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 429);
            }

            return redirect()->route('password.request')->with('error', $msg);
        }

        if (now()->timestamp > $expiresAt) {
            $msg = 'Kode OTP telah kadaluarsa (melebihi 15 menit). Silakan minta kode baru.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return back()->with('error', $msg);
        }

        // Cek kecocokan OTP (baik hash DB maupun session plaintext)
        $ticketId = session('pwd_reset_ticket_id');
        $ticket = $ticketId ? PasswordResetRequest::find($ticketId) : null;

        $isValidOtp = false;
        if ($ticket && $ticket->otp_code && Hash::check($request->otp_code, $ticket->otp_code)) {
            $isValidOtp = true;
        } elseif (trim((string) $request->otp_code) === trim((string) $sessionOtp)) {
            $isValidOtp = true;
        }

        if (! $isValidOtp) {
            $msg = "Kode verifikasi OTP salah! (Percobaan {$attempts} dari 5)";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        // Tandai OTP terverifikasi di session & database
        session(['pwd_reset_otp_verified' => true]);
        if ($ticket) {
            $ticket->update(['status' => 'verified']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kode OTP cocok! Silakan buat kata sandi baru untuk akun Anda.',
            ]);
        }

        return back()->with('success', 'Kode OTP berhasil diverifikasi!');
    }

    /**
     * Verifikasi OTP dan Update Password Baru
     */
    public function verifyAndResetPassword(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'otp_code' => 'nullable|numeric',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $sessionOtp = session('pwd_reset_otp');
        $sessionUserId = session('pwd_reset_user_id');
        $sessionUsername = session('pwd_reset_username');
        $expiresAt = session('pwd_reset_expires_at');
        $isVerified = session('pwd_reset_otp_verified', false);

        if (! $sessionUserId || (! $isVerified && ! $sessionOtp)) {
            $msg = 'Sesi pemulihan kata sandi telah berakhir. Silakan ulangi proses.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 400);
            }

            return redirect()->route('password.request')->with('error', $msg);
        }

        // Jika belum terverifikasi melalui verifyOtp, lakukan verifikasi ulang
        if (! $isVerified) {
            $ticketId = session('pwd_reset_ticket_id');
            $ticket = $ticketId ? PasswordResetRequest::find($ticketId) : null;

            $isValidOtp = false;
            if ($ticket && $ticket->otp_code && Hash::check($request->otp_code, $ticket->otp_code)) {
                $isValidOtp = true;
            } elseif (trim((string) $request->otp_code) === trim((string) $sessionOtp)) {
                $isValidOtp = true;
            }

            if (! $isValidOtp) {
                $msg = 'Kode verifikasi OTP salah! Silakan periksa kembali kode OTP Anda.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return back()->with('error', $msg);
            }
        } else {
            $ticketId = session('pwd_reset_ticket_id');
            $ticket = $ticketId ? PasswordResetRequest::find($ticketId) : null;
        }

        // Update password pengguna (User Pegawai atau Nasabah)
        $accountType = session('pwd_reset_account_type', 'user');
        $account = $accountType === 'nasabah' ? Nasabah::find($sessionUserId) : User::find($sessionUserId);
        if (! $account) {
            $msg = 'Pengguna tidak ditemukan.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            return redirect()->route('password.request')->with('error', $msg);
        }

        $account->password = $request->password;
        $account->save();

        // Perbarui status tiket di database menjadi completed dan hanguskan OTP (one-time use)
        if ($ticket) {
            $ticket->update([
                'status' => 'completed',
                'keterangan' => 'Password berhasil direset mandiri oleh user menggunakan OTP',
                'handled_at' => now(),
            ]);
        }

        // Bersihkan seluruh session reset
        session()->forget([
            'pwd_reset_step',
            'pwd_reset_account_type',
            'pwd_reset_user_id',
            'pwd_reset_ticket_id',
            'pwd_reset_username',
            'pwd_reset_nama',
            'pwd_reset_metode',
            'pwd_reset_masked_contact',
            'pwd_reset_raw_contact',
            'pwd_reset_otp',
            'pwd_reset_otp_verified',
            'pwd_reset_wa_link',
            'pwd_reset_expires_at',
            'pwd_reset_attempts',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kata sandi akun "'.$sessionUsername.'" berhasil diperbarui!',
                'redirect' => route('login', ['reset' => 'success']),
            ]);
        }

        return redirect()->route('login')->with('success', 'Kata sandi akun "'.$sessionUsername.'" berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
    }

    /**
     * Ajukan Tiket Permohonan Reset Password ke Admin
     */
    public function submitResetTicket(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'nama_pemohon' => 'required|string',
            'no_wa' => 'required|string',
            'alasan' => 'required|string|max:500',
        ], [
            'username.required' => 'Username wajib diisi.',
            'nama_pemohon.required' => 'Nama lengkap pemohon wajib diisi.',
            'no_wa.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'alasan.required' => 'Berikan keterangan alasan permohonan reset kata sandi.',
        ]);

        // Cek permohonan berulang yang masih pending
        $existingPending = PasswordResetRequest::where('username', $request->username)
            ->where('status', 'pending')
            ->where('metode', 'ticket')
            ->first();

        if ($existingPending) {
            return back()->with('warning', 'Anda sudah memiliki permohonan reset password yang masih berstatus PENDING (No. Tiket: #RST-'.str_pad((string) $existingPending->id, 4, '0', STR_PAD_LEFT).'). Harap menunggu proses verifikasi Administrator.');
        }

        $user = User::where('username', $request->username)->first();

        $ticket = PasswordResetRequest::create([
            'user_id' => $user ? $user->id : null,
            'username' => $request->username,
            'nama_pemohon' => $request->nama_pemohon,
            'metode' => 'ticket',
            'kontak' => $request->no_wa,
            'status' => 'pending',
            'keterangan' => $request->alasan,
            'ip_address' => $request->ip(),
        ]);

        // Ambil nomor admin
        $adminUser = User::where('role', 'admin')->first();
        $adminPhone = $adminUser->no_hp ?? '081234567890';
        $cleanAdminPhone = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($cleanAdminPhone, '0')) {
            $cleanAdminPhone = '62'.substr($cleanAdminPhone, 1);
        }

        $waText = urlencode("Halo Admin LKM Mitra Siswa Abadi, saya {$request->nama_pemohon} (Username: {$request->username}) mengajukan permohonan reset password di sistem (No Tiket: #RST-{$ticket->id}). No WA saya: {$request->no_wa}. Alasan: {$request->alasan}. Mohon bantuannya untuk memproses reset akun saya. Terima kasih.");
        $waAdminLink = "https://wa.me/{$cleanAdminPhone}?text={$waText}";

        return back()->with('ticket_success', true)
            ->with('ticket_id', 'RST-'.str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT))
            ->with('wa_admin_link', $waAdminLink);
    }

    /**
     * Batalkan proses reset dan kembali ke langkah awal
     */
    public function resetForgotPasswordSession(): RedirectResponse
    {
        session()->forget([
            'pwd_reset_step',
            'pwd_reset_account_type',
            'pwd_reset_user_id',
            'pwd_reset_ticket_id',
            'pwd_reset_username',
            'pwd_reset_nama',
            'pwd_reset_metode',
            'pwd_reset_masked_contact',
            'pwd_reset_raw_contact',
            'pwd_reset_otp',
            'pwd_reset_otp_verified',
            'pwd_reset_wa_link',
            'pwd_reset_expires_at',
            'pwd_reset_attempts',
        ]);

        return redirect()->route('password.request');
    }

    /**
     * Helper redirect berdasarkan role
     */
    private function redirectBasedOnRole(mixed $user): RedirectResponse
    {
        $role = strtolower($user->role ?? '');
        $userRoles = array_map('trim', explode(',', $role));

        if (in_array('admin', $userRoles, true)) {
            return redirect()->intended('/admin/dashboard');
        } elseif (in_array('teller', $userRoles, true)) {
            return redirect()->intended('/teller/dashboard');
        } elseif (in_array('supervisor', $userRoles, true) || in_array('accounting', $userRoles, true)) {
            return redirect()->intended('/supervisor/dashboard');
        } elseif (in_array('customer_service', $userRoles, true) || in_array('cs', $userRoles, true)) {
            return redirect()->intended('/admin/data-nasabah');
        } elseif (in_array('nasabah', $userRoles, true)) {
            return redirect()->intended('/nasabah/dashboard');
        }

        return redirect()->intended('/admin/dashboard');
    }

    /**
     * Sensor nomor kontak untuk perlindungan privasi
     */
    private function maskContact(?string $contact, string $type = 'whatsapp'): string
    {
        if (empty($contact)) {
            return 'Belum terdaftar';
        }

        if ($type === 'email') {
            $parts = explode('@', $contact);
            if (count($parts) === 2) {
                $name = $parts[0];
                $domain = $parts[1];
                $len = strlen($name);
                if ($len <= 2) {
                    $masked = substr($name, 0, 1).'*';
                } else {
                    $masked = substr($name, 0, 2).str_repeat('*', max(2, $len - 3)).substr($name, -1);
                }

                return $masked.'@'.$domain;
            }

            return $contact;
        }

        $clean = preg_replace('/[^0-9]/', '', $contact);
        $len = strlen($clean);
        if ($len >= 8) {
            return substr($clean, 0, 4).'****'.substr($clean, -3);
        }

        return $contact;
    }
}
