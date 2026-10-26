<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman login utama
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        if (request('reset') === 'success' && !session()->has('success')) {
            session()->flash('success', 'Kata sandi berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.');
        }

        $adminUser = DB::table('users')->where('role', 'admin')->first();
        $adminPhone = $adminUser->no_hp ?? '081234567890';

        return view('auth.login', compact('adminPhone'));
    }

    /**
     * Proses autentikasi login pengguna
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->with('error', 'Username atau Password salah! Periksa kembali kredensial Anda.')->withInput($request->only('username'));
    }

    /**
     * Logout pengguna dari sistem
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem secara aman.');
    }

    /**
     * Tampilkan antarmuka Lupa Password
     */
    public function showForgotPassword()
    {
        $adminUser = DB::table('users')->where('role', 'admin')->first();
        $adminPhone = $adminUser->no_hp ?? '081234567890';

        return view('auth.forgot-password', compact('adminPhone'));
    }

    /**
     * Minta Kode OTP Verifikasi (WhatsApp / Email)
     */
    public function requestOtp(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'metode' => 'required|in:whatsapp,email',
        ], [
            'username.required' => 'Silakan masukkan username akun Anda.',
            'metode.required' => 'Pilih saluran verifikasi pemulihan kata sandi.',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->with('error', 'Username "' . $request->username . '" tidak terdaftar dalam sistem. Hubungi administrator.');
        }

        $metode = $request->metode;
        $rawContact = ($metode === 'whatsapp') ? $user->no_hp : $user->email;

        // Jika nomor HP atau Email belum disetel di akun
        if (empty($rawContact)) {
            // Jika user mengisi kontak darurat di input opsional atau verifikasi langsung
            if ($request->filled('manual_contact')) {
                $rawContact = $request->manual_contact;
            } else {
                return back()->with('warning', 'Akun "' . $user->username . '" (' . $user->nama_petugas . ') belum memiliki nomor WhatsApp atau Email yang terdaftar di profil. Anda dapat mengajukan tiket permohonan reset atau hubungi Admin via WhatsApp melalui tab di samping.')
                             ->with('suggest_ticket', true)
                             ->with('suggest_username', $user->username)
                             ->with('suggest_nama', $user->nama_petugas);
            }
        }

        // Generate 6 digit OTP acak
        $otp = (string) random_int(100000, 999999);
        $expiresAt = now()->addMinutes(15);

        // Simpan ke database password_reset_requests
        DB::table('password_reset_requests')->insert([
            'user_id' => $user->id,
            'username' => $user->username,
            'nama_pemohon' => $user->nama_petugas,
            'metode' => $metode,
            'kontak' => $rawContact,
            'otp_code' => $otp,
            'otp_expires_at' => $expiresAt,
            'status' => 'pending',
            'keterangan' => 'Kode OTP pemulihan kata sandi dibuat',
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $maskedContact = $this->maskContact($rawContact, $metode);

        // Buat pesan WhatsApp siap kirim jika metode WA
        $cleanWaNumber = preg_replace('/[^0-9]/', '', $rawContact);
        if (str_starts_with($cleanWaNumber, '0')) {
            $cleanWaNumber = '62' . substr($cleanWaNumber, 1);
        }
        $waMessage = urlencode("Halo {$user->nama_petugas}, kode OTP verifikasi reset password LKM Mitra Siswa Abadi Anda adalah: {$otp}. Kode berlaku selama 15 menit. Jaga kerahasiaan kode ini.");
        $waLink = "https://wa.me/{$cleanWaNumber}?text={$waMessage}";

        // Simpan state di session untuk verifikasi tahap 2
        session([
            'pwd_reset_step' => 'verify',
            'pwd_reset_user_id' => $user->id,
            'pwd_reset_username' => $user->username,
            'pwd_reset_nama' => $user->nama_petugas,
            'pwd_reset_metode' => $metode,
            'pwd_reset_masked_contact' => $maskedContact,
            'pwd_reset_raw_contact' => $rawContact,
            'pwd_reset_otp' => $otp, // Disimpan untuk verifikasi dan demonstrasi visual
            'pwd_reset_wa_link' => $waLink,
            'pwd_reset_expires_at' => $expiresAt->timestamp,
        ]);

        return back()->with('success', 'Kode verifikasi keamanan berhasil dibuat!');
    }

    /**
     * Verifikasi OTP dan Update Password Baru
     */
    public function verifyAndResetPassword(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|numeric',
            'password' => 'required|min:6|confirmed',
        ], [
            'otp_code.required' => 'Masukkan 6 digit kode OTP verifikasi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $sessionOtp = session('pwd_reset_otp');
        $sessionUserId = session('pwd_reset_user_id');
        $sessionUsername = session('pwd_reset_username');
        $expiresAt = session('pwd_reset_expires_at');

        if (!$sessionUserId || !$sessionOtp) {
            return redirect()->route('password.request')->with('error', 'Sesi pemulihan kata sandi telah berakhir. Silakan ulangi proses.');
        }

        if (now()->timestamp > $expiresAt) {
            return back()->with('error', 'Kode OTP telah kadaluarsa (melebihi 15 menit). Silakan minta kode baru.');
        }

        // Cek kecocokan OTP
        if (trim($request->otp_code) !== trim($sessionOtp)) {
            return back()->with('error', 'Kode verifikasi OTP yang Anda masukkan salah! Periksa kembali kode Anda.');
        }

        // Update password pengguna
        $user = User::find($sessionUserId);
        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Pengguna tidak ditemukan.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Perbarui status tiket di database
        DB::table('password_reset_requests')
            ->where('user_id', $user->id)
            ->where('otp_code', $sessionOtp)
            ->update([
                'status' => 'completed',
                'keterangan' => 'Password berhasil direset mandiri oleh user menggunakan OTP',
                'updated_at' => now(),
            ]);

        // Bersihkan session reset
        session()->forget([
            'pwd_reset_step',
            'pwd_reset_user_id',
            'pwd_reset_username',
            'pwd_reset_nama',
            'pwd_reset_metode',
            'pwd_reset_masked_contact',
            'pwd_reset_raw_contact',
            'pwd_reset_otp',
            'pwd_reset_wa_link',
            'pwd_reset_expires_at',
        ]);

        return redirect()->route('login')->with('success', 'Kata sandi akun "' . $sessionUsername . '" berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.');
    }

    /**
     * Ajukan Tiket Permohonan Reset Password ke Admin
     */
    public function submitResetTicket(Request $request)
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

        $user = User::where('username', $request->username)->first();

        $ticketId = DB::table('password_reset_requests')->insertGetId([
            'user_id' => $user ? $user->id : null,
            'username' => $request->username,
            'nama_pemohon' => $request->nama_pemohon,
            'metode' => 'ticket',
            'kontak' => $request->no_wa,
            'status' => 'pending',
            'keterangan' => $request->alasan,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ambil nomor admin
        $adminUser = DB::table('users')->where('role', 'admin')->first();
        $adminPhone = $adminUser->no_hp ?? '081234567890';
        $cleanAdminPhone = preg_replace('/[^0-9]/', '', $adminPhone);
        if (str_starts_with($cleanAdminPhone, '0')) {
            $cleanAdminPhone = '62' . substr($cleanAdminPhone, 1);
        }

        $waText = urlencode("Halo Admin LKM Mitra Siswa Abadi, saya {$request->nama_pemohon} (Username: {$request->username}) mengajukan permohonan reset password di sistem (No Tiket: #RST-{$ticketId}). No WA saya: {$request->no_wa}. Alasan: {$request->alasan}. Mohon bantuannya untuk memproses reset akun saya. Terima kasih.");
        $waAdminLink = "https://wa.me/{$cleanAdminPhone}?text={$waText}";

        return back()->with('ticket_success', true)
                     ->with('ticket_id', 'RST-' . str_pad($ticketId, 4, '0', STR_PAD_LEFT))
                     ->with('wa_admin_link', $waAdminLink);
    }

    /**
     * Batalkan proses reset dan kembali ke langkah awal
     */
    public function resetForgotPasswordSession()
    {
        session()->forget([
            'pwd_reset_step',
            'pwd_reset_user_id',
            'pwd_reset_username',
            'pwd_reset_nama',
            'pwd_reset_metode',
            'pwd_reset_masked_contact',
            'pwd_reset_raw_contact',
            'pwd_reset_otp',
            'pwd_reset_wa_link',
            'pwd_reset_expires_at',
        ]);

        return redirect()->route('password.request');
    }

    /**
     * Helper redirect berdasarkan role
     */
    private function redirectBasedOnRole($user)
    {
        $role = strtolower($user->role ?? '');
        $userRoles = array_map('trim', explode(',', $role));

        if (in_array('admin', $userRoles)) {
            return redirect()->intended('/admin/dashboard');
        } elseif (in_array('teller', $userRoles)) {
            return redirect()->intended('/teller/dashboard');
        } elseif (in_array('supervisor', $userRoles) || in_array('accounting', $userRoles)) {
            return redirect()->intended('/supervisor/dashboard');
        } elseif (in_array('customer_service', $userRoles) || in_array('cs', $userRoles)) {
            return redirect()->intended('/admin/data-nasabah');
        } elseif (in_array('nasabah', $userRoles)) {
            return redirect()->intended('/nasabah/dashboard');
        }

        return redirect()->intended('/admin/dashboard');
    }

    /**
     * Sensor nomor kontak untuk perlindungan privasi
     */
    private function maskContact($contact, $type = 'whatsapp')
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
                    $masked = substr($name, 0, 1) . '*';
                } else {
                    $masked = substr($name, 0, 2) . str_repeat('*', max(2, $len - 3)) . substr($name, -1);
                }
                return $masked . '@' . $domain;
            }
            return $contact;
        }

        $clean = preg_replace('/[^0-9]/', '', $contact);
        $len = strlen($clean);
        if ($len >= 8) {
            return substr($clean, 0, 4) . '****' . substr($clean, -3);
        }

        return $contact;
    }
}
