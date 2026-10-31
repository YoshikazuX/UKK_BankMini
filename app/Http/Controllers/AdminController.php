<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\AvalisSyncHelper;
use App\Helpers\SettingHelper;
use App\Models\AuditLog;
use App\Models\Avalis;
use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\PasswordResetRequest;
use App\Models\PengajuanKas;
use App\Models\PengaturanSistem;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\ProfileImageService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        $hariIni = Carbon::today();

        $totalNasabah = Nasabah::count();
        $totalPegawai = User::whereIn('role', ['teller', 'supervisor'])->count();
        $pegawai = User::all();

        $totalDebit = Jurnal::whereDate('tanggal', $hariIni)->sum('debit');
        $totalKredit = Jurnal::whereDate('tanggal', $hariIni)->sum('kredit');

        // Mutasi Transaksi Tabungan Nasabah
        $mutasiNabung = Transaksi::whereDate('created_at', $hariIni)
            ->where('jenis_transaksi', 'setor')
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $mutasiTarik = Transaksi::whereDate('created_at', $hariIni)
            ->whereIn('jenis_transaksi', ['tarik', 'admin'])
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        // Jurnal yang bersumber dari Transaksi Nasabah
        $jurnalDebitNasabah = Jurnal::whereDate('tanggal', $hariIni)->whereNotNull('transaksi_id')->sum('debit');
        $jurnalKreditNasabah = Jurnal::whereDate('tanggal', $hariIni)->whereNotNull('transaksi_id')->sum('kredit');

        // Mutasi Kas Operasional Internal (Droping Kas / Pengajuan Kas yang Disetujui)
        $totalDropingKas = PengajuanKas::whereDate('tanggal', $hariIni)->where('status', 'disetujui')->sum('nominal');
        $jurnalDropingKas = Jurnal::whereDate('tanggal', $hariIni)
            ->whereNull('transaksi_id')
            ->where('keterangan', 'LIKE', '%DROPING KAS%')
            ->sum('debit');

        // Keseimbangan tercapai jika:
        // 1. Catatan debit nasabah di jurnal klop dengan mutasi setoran nasabah
        // 2. Catatan kredit nasabah di jurnal klop dengan mutasi penarikan/admin nasabah
        // 3. Catatan droping kas di jurnal klop dengan nominal pengajuan kas yang telah disetujui supervisor
        $isBalanced = ($jurnalDebitNasabah == $mutasiNabung)
            && ($jurnalKreditNasabah == $mutasiTarik)
            && ($jurnalDropingKas == $totalDropingKas);

        $totalVoid = Transaksi::whereDate('updated_at', $hariIni)->where('status', 'dibatalkan')->count();
        $totalGagalSistem = 0;

        $resetRequests = PasswordResetRequest::where('status', 'pending')
            ->orderBy('id', 'desc')
            ->get();

        // Ringkasan Kehadiran Petugas Avalis Hari Ini
        $absensiSummary = AvalisSyncHelper::getDashboardSummary($hariIni->toDateString());

        return view('admin.dashboard', compact(
            'totalNasabah',
            'totalPegawai',
            'pegawai',
            'totalDebit',
            'totalKredit',
            'isBalanced',
            'totalVoid',
            'totalGagalSistem',
            'resetRequests',
            'totalDropingKas',
            'absensiSummary'
        ));
    }

    public function create(): View
    {
        return view('admin.tambah');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_petugas' => 'required|string|max:150',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,teller,supervisor,customer_service,avalis,accounting',
            'kelas' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
        ]);

        $newUser = User::create([
            'nama_petugas' => $validated['nama_petugas'],
            'username' => $validated['username'],
            'password' => $validated['password'], // Otomatis di-hash oleh model cast 'password' => 'hashed'
            'role' => $validated['role'],
            'kelas' => $validated['kelas'] ?? null,
            'no_hp' => $validated['no_hp'] ?? null,
            'email' => $validated['email'] ?? null,
        ]);

        // Sinkronisasi otomatis ke tabel avalis jika bertugas sebagai teller atau avalis
        if (in_array($validated['role'], ['teller', 'avalis'], true)) {
            Avalis::create([
                'nama' => $validated['nama_petugas'],
                'kelas' => $validated['kelas'] ?: 'XII AKL 1',
                'no_wa' => $validated['no_hp'] ?: '-',
                'user_id' => $newUser->id,
                'status' => 'aktif',
            ]);
        }

        AuditHelper::log(
            jenisAktivitas: 'TAMBAH_PEGAWAI',
            modul: 'Pegawai',
            objekId: $newUser->username,
            oldValues: null,
            newValues: [
                'nama_petugas' => $newUser->nama_petugas,
                'username' => $newUser->username,
                'role' => $newUser->role,
                'kelas' => $newUser->kelas,
                'email' => $newUser->email,
                'no_hp' => $newUser->no_hp,
            ]
        );

        return redirect('/admin/dashboard')->with('success', 'Pegawai baru berhasil ditambahkan dengan role '.strtoupper($newUser->role).'!');
    }

    public function edit(int|string $id): View
    {
        $pegawai = User::findOrFail($id);

        return view('admin.edit-pegawai', compact('pegawai'));
    }

    public function update(Request $request, int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nama_petugas' => 'required|string|max:150',
            'role' => 'required|in:admin,teller,supervisor,customer_service,avalis,accounting',
            'kelas' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'password' => 'nullable|string|min:6',
        ]);

        $oldValues = [
            'nama_petugas' => $user->nama_petugas,
            'role' => $user->role,
            'kelas' => $user->kelas,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
        ];

        $user->nama_petugas = $validated['nama_petugas'];
        $user->role = $validated['role'];
        $user->kelas = $validated['kelas'] ?? null;
        $user->no_hp = $validated['no_hp'] ?? null;
        $user->email = $validated['email'] ?? null;

        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        // Sinkronisasi pembaruan ke tabel avalis
        if (in_array($user->role, ['teller', 'avalis'], true)) {
            $avalisExist = Avalis::where('user_id', $id)->first();
            if ($avalisExist) {
                $avalisExist->update([
                    'nama' => $user->nama_petugas,
                    'kelas' => $user->kelas ?: $avalisExist->kelas,
                    'no_wa' => $user->no_hp ?: $avalisExist->no_wa,
                ]);
            } else {
                Avalis::create([
                    'nama' => $user->nama_petugas,
                    'kelas' => $user->kelas ?: 'XII AKL 1',
                    'no_wa' => $user->no_hp ?: '-',
                    'user_id' => $id,
                    'status' => 'aktif',
                ]);
            }
        }

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_PEGAWAI',
            modul: 'Pegawai',
            objekId: $user->username,
            oldValues: $oldValues,
            newValues: [
                'nama_petugas' => $user->nama_petugas,
                'role' => $user->role,
                'kelas' => $user->kelas,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
            ]
        );

        return redirect('/admin/dashboard')->with('success', 'Data pegawai diperbarui dan tersinkronisasi!');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        AuditHelper::log(
            jenisAktivitas: 'HAPUS_PEGAWAI',
            modul: 'Pegawai',
            objekId: $user->username,
            oldValues: [
                'id' => $user->id,
                'nama_petugas' => $user->nama_petugas,
                'username' => $user->username,
                'role' => $user->role,
            ],
            newValues: null
        );

        Avalis::where('user_id', $id)->update(['user_id' => null]);
        $user->delete();

        return redirect('/admin/dashboard')->with('success', 'Pegawai berhasil dihapus.');
    }

    public function profil(): View
    {
        $user = Auth::user();

        return view('admin.profil', compact('user'));
    }

    public function updateProfil(Request $request, ProfileImageService $imageService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nama_petugas' => 'nullable|string|max:150',
            'no_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-()]+$/'],
            'email' => 'nullable|email|max:150|unique:users,email,'.$user->id,
            'foto' => 'nullable|file|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
        ], [
            'nama_petugas.max' => 'Nama lengkap maksimal 150 karakter.',
            'no_hp.regex' => 'Format nomor telepon tidak valid. Gunakan angka, contoh: 081234567890.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'foto.file' => 'File foto yang dipilih tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.mimetypes' => 'Format file ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.max' => 'Ukuran file foto maksimal adalah 2 MB.',
        ]);

        $oldValues = [
            'nama_petugas' => $user->nama_petugas,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
        ];

        if ($request->filled('nama_petugas')) {
            $user->nama_petugas = $validated['nama_petugas'];
        }
        $user->no_hp = $validated['no_hp'] ?? null;
        $user->email = $validated['email'] ?? null;

        if ($request->hasFile('foto')) {
            $user->foto = $imageService->validateAndStore($request->file('foto'), $user->foto);
        }

        $user->save();
        AvalisSyncHelper::autoSync(true);

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_PROFIL',
            modul: 'Profil',
            objekId: $user->username,
            oldValues: $oldValues,
            newValues: [
                'nama_petugas' => $user->nama_petugas,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
            ]
        );

        return back()->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }

    // ==================================================================
    // FUNGSI: JEJAK AUDIT & FILTER TERPADU (FEATURE 3)
    // ==================================================================
    public function auditLog(Request $request): View
    {
        $query = AuditLog::query();

        // 1. Filter Rentang Tanggal
        if ($request->filled('tgl_mulai') && $request->filled('tgl_akhir')) {
            $query->whereBetween('created_at', [$request->tgl_mulai.' 00:00:00', $request->tgl_akhir.' 23:59:59']);
        } elseif ($request->filled('tgl_mulai')) {
            $query->where('created_at', '>=', $request->tgl_mulai.' 00:00:00');
        } elseif ($request->filled('tgl_akhir')) {
            $query->where('created_at', '<=', $request->tgl_akhir.' 23:59:59');
        }

        // 2. Filter Pengguna (ID)
        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        // 3. Filter Role Pengguna
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role_user', $request->role);
        }

        // 4. Filter Jenis Aktivitas
        if ($request->filled('jenis_aktivitas') && $request->jenis_aktivitas !== 'all') {
            $query->where('jenis_aktivitas', $request->jenis_aktivitas);
        }

        // 5. Pencarian Teks Bebas
        if ($request->filled('cari')) {
            $cari = trim($request->cari);
            $query->where(function ($sub) use ($cari) {
                $sub->where('nama_user', 'like', "%{$cari}%")
                    ->orWhere('objek_id', 'like', "%{$cari}%")
                    ->orWhere('modul', 'like', "%{$cari}%")
                    ->orWhere('jenis_aktivitas', 'like', "%{$cari}%");
            });
        }

        $totalLog = (clone $query)->count();
        $logs = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Data untuk Filter Dropdown
        $daftarUser = User::select('id', 'nama_petugas', 'role')->orderBy('nama_petugas')->get();
        $daftarRole = AuditLog::whereNotNull('role_user')->distinct()->pluck('role_user');
        $daftarAktivitas = AuditLog::whereNotNull('jenis_aktivitas')->distinct()->pluck('jenis_aktivitas');

        return view('admin.audit', compact(
            'logs',
            'daftarUser',
            'daftarRole',
            'daftarAktivitas',
            'totalLog'
        ));
    }

    // ==================================================================
    // FUNGSI: EKSPOR DATA AUDIT RESMI KE CSV (FEATURE 3)
    // ==================================================================
    public function exportAuditCsv(Request $request): StreamedResponse
    {
        $query = AuditLog::query();

        if ($request->filled('tgl_mulai') && $request->filled('tgl_akhir')) {
            $query->whereBetween('created_at', [$request->tgl_mulai.' 00:00:00', $request->tgl_akhir.' 23:59:59']);
        } elseif ($request->filled('tgl_mulai')) {
            $query->where('created_at', '>=', $request->tgl_mulai.' 00:00:00');
        } elseif ($request->filled('tgl_akhir')) {
            $query->where('created_at', '<=', $request->tgl_akhir.' 23:59:59');
        }

        if ($request->filled('user_id') && $request->user_id !== 'all') {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role_user', $request->role);
        }

        if ($request->filled('jenis_aktivitas') && $request->jenis_aktivitas !== 'all') {
            $query->where('jenis_aktivitas', $request->jenis_aktivitas);
        }

        if ($request->filled('cari')) {
            $cari = trim($request->cari);
            $query->where(function ($sub) use ($cari) {
                $sub->where('nama_user', 'like', "%{$cari}%")
                    ->orWhere('objek_id', 'like', "%{$cari}%")
                    ->orWhere('modul', 'like', "%{$cari}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=Audit_Trail_Bank_Mini_'.now()->format('Ymd_His').'.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, [
                'ID Log',
                'Tanggal & Waktu',
                'ID Petugas',
                'Nama Petugas',
                'Role Petugas',
                'Jenis Aktivitas',
                'Modul',
                'Identitas Objek',
                'Nilai Sebelum Perubahan',
                'Nilai Sesudah Perubahan',
                'Alamat IP',
            ], ';');

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
                    $log->user_id ?? '-',
                    $log->nama_user,
                    $log->role_user ?? '-',
                    $log->jenis_aktivitas,
                    $log->modul,
                    $log->objek_id ?? '-',
                    $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '-',
                    $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '-',
                    $log->ip_address ?? '-',
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Halaman Manajemen Permohonan Reset Password (Feature 5)
     */
    public function resetPasswordRequests(Request $request): View
    {
        $query = PasswordResetRequest::with(['user', 'handler']);

        // 1. Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // 2. Filter Tanggal
        if ($request->filled('tgl_mulai') && $request->filled('tgl_akhir')) {
            $query->whereBetween('created_at', [$request->tgl_mulai.' 00:00:00', $request->tgl_akhir.' 23:59:59']);
        } elseif ($request->filled('tgl_mulai')) {
            $query->where('created_at', '>=', $request->tgl_mulai.' 00:00:00');
        } elseif ($request->filled('tgl_akhir')) {
            $query->where('created_at', '<=', $request->tgl_akhir.' 23:59:59');
        }

        // 3. Filter Metode (WhatsApp, Email, Ticket)
        if ($request->filled('metode') && $request->metode !== 'all') {
            $query->where('metode', $request->metode);
        }

        // 4. Pencarian Username / Nama Pemohon
        if ($request->filled('cari')) {
            $cari = trim($request->cari);
            $query->where(function ($sub) use ($cari) {
                $sub->where('username', 'like', "%{$cari}%")
                    ->orWhere('nama_pemohon', 'like', "%{$cari}%")
                    ->orWhere('kontak', 'like', "%{$cari}%");
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $totalSemua = PasswordResetRequest::count();
        $totalPending = PasswordResetRequest::where('status', 'pending')->count();
        $totalVerified = PasswordResetRequest::where('status', 'verified')->count();
        $totalCompleted = PasswordResetRequest::where('status', 'completed')->count();
        $totalRejected = PasswordResetRequest::where('status', 'rejected')->count();

        return view('admin.reset-password-requests', compact(
            'requests',
            'totalSemua',
            'totalPending',
            'totalVerified',
            'totalCompleted',
            'totalRejected'
        ));
    }

    public function approveResetRequest(Request $request, int|string $id): RedirectResponse
    {
        $item = PasswordResetRequest::findOrFail($id);
        $user = User::where('username', $item->username)->first();

        if (! $user) {
            return back()->with('error', 'User akun "'.$item->username.'" tidak ditemukan.');
        }

        $admin = Auth::user();
        $adminName = $admin->nama_petugas ?? 'Administrator';

        // Generate password sementara aman (8 karakter acak)
        $tempPassword = Str::random(8);
        $user->password = $tempPassword; // Casts will bcrypt it
        $user->save();

        $item->update([
            'status' => 'completed',
            'handled_by' => $admin->id,
            'handled_at' => now(),
            'keterangan' => 'Disetujui oleh '.$adminName.'. Password di-reset aman.',
        ]);

        AuditHelper::log(
            jenisAktivitas: 'RESET_PASSWORD_APPROVE',
            modul: 'Reset Password',
            objekId: 'RST-'.$item->id.' ('.$item->username.')',
            oldValues: ['status' => 'pending'],
            newValues: ['status' => 'completed', 'handled_by' => $adminName]
        );

        return back()->with('success', 'Permohonan reset password akun '.$item->username.' berhasil disetujui! Kata sandi sementara pemohon: '.$tempPassword.' (Segera informasikan kepada pemohon).');
    }

    public function rejectResetRequest(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
        ], [
            'alasan.required' => 'Wajib menyertakan alasan penolakan permohonan.',
        ]);

        $item = PasswordResetRequest::findOrFail($id);
        $admin = Auth::user();
        $adminName = $admin->nama_petugas ?? 'Administrator';
        $alasan = $request->alasan;

        $item->update([
            'status' => 'rejected',
            'handled_by' => $admin->id,
            'handled_at' => now(),
            'keterangan' => 'Ditolak oleh '.$adminName.' (Alasan: '.$alasan.')',
        ]);

        AuditHelper::log(
            jenisAktivitas: 'RESET_PASSWORD_REJECT',
            modul: 'Reset Password',
            objekId: 'RST-'.$item->id.' ('.$item->username.')',
            oldValues: ['status' => $item->getOriginal('status') ?? 'pending'],
            newValues: ['status' => 'rejected', 'alasan' => $alasan, 'handled_by' => $adminName]
        );

        return back()->with('success', 'Permohonan reset password berhasil ditolak dengan alasan tercatat.');
    }

    public function konfigurasi(): View
    {
        $setting = SettingHelper::getSettings();

        return view('admin.konfigurasi', compact('setting'));
    }

    public function updateKonfigurasi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'saldo_mengendap_min' => 'required|numeric|min:0',
            'tahun_ajaran_aktif' => 'required|string|max:20',
            'nama_bank' => 'required|string|max:100',
            'format_no_rekening' => 'nullable|string|max:50',
            'biaya_administrasi_buku' => 'nullable|numeric|min:0',
        ]);

        $setting = SettingHelper::getSettings();
        $oldValues = [
            'saldo_mengendap_min' => $setting->saldo_mengendap_min ?? 10000,
            'tahun_ajaran_aktif' => $setting->tahun_ajaran_aktif ?? '2026/2027',
            'nama_bank' => $setting->nama_bank ?? 'Bank Mini Sekolah',
        ];

        PengaturanSistem::updateOrCreate(
            ['id' => 1],
            [
                'saldo_mengendap_min' => $validated['saldo_mengendap_min'],
                'scanner_kamera_aktif' => $request->boolean('scanner_kamera_aktif'),
                'tahun_ajaran_aktif' => $validated['tahun_ajaran_aktif'],
                'format_no_rekening' => $validated['format_no_rekening'] ?? 'TA-JURUSAN-URUT',
                'nama_bank' => $validated['nama_bank'],
                'biaya_administrasi_buku' => $validated['biaya_administrasi_buku'] ?? 5000,
            ]
        );

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_KONFIGURASI',
            modul: 'Sistem',
            objekId: 'GLOBAL_CONFIG',
            oldValues: $oldValues,
            newValues: $validated
        );

        return back()->with('success', 'Konfigurasi Sistem Global Perbankan Mini berhasil disimpan!');
    }
}
