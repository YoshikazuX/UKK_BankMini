<?php

namespace App\Http\Controllers;

use App\Helpers\AvalisSyncHelper;
use App\Helpers\SettingHelper;
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
            'role' => 'required|in:admin,teller,supervisor',
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

        // Sinkronisasi otomatis ke tabel avalis jika bertugas sebagai teller
        if ($validated['role'] === 'teller') {
            Avalis::create([
                'nama' => $validated['nama_petugas'],
                'kelas' => $validated['kelas'] ?: 'XII AKL 1',
                'no_wa' => $validated['no_hp'] ?: '-',
                'user_id' => $newUser->id,
                'status' => 'aktif',
            ]);
        }

        return redirect('/admin/dashboard')->with('success', 'Pegawai baru berhasil ditambahkan dan disinkronkan ke petugas avalis!');
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
            'role' => 'required|in:admin,teller,supervisor',
            'kelas' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'password' => 'nullable|string|min:6',
        ]);

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
        if ($user->role === 'teller') {
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

        return redirect('/admin/dashboard')->with('success', 'Data pegawai diperbarui dan tersinkronisasi!');
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
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

        $request->validate([
            'no_hp' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'foto' => 'nullable|file|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
        ], [
            'foto.file' => 'File foto yang dipilih tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.mimetypes' => 'Format file ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.max' => 'Ukuran file foto maksimal adalah 2 MB.',
        ]);

        $user->no_hp = $request->no_hp;
        $user->email = $request->email;

        if ($request->hasFile('foto')) {
            $user->foto = $imageService->validateAndStore($request->file('foto'), $user->foto);
        }

        $user->save();
        AvalisSyncHelper::autoSync(true);

        return back()->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }

    // ==================================================================
    // FUNGSI: JEJAK AUDIT & FILTER ANOMALI
    // ==================================================================
    public function auditLog(Request $request): View
    {
        $query = Transaksi::with(['nasabah', 'petugas']);

        // Filter Target: NIS atau Teller
        if ($request->filled('target')) {
            $target = $request->target;
            $query->where(function ($q) use ($target) {
                $q->whereHas('nasabah', fn ($sub) => $sub->where('nis', 'like', "%{$target}%"))
                    ->orWhereHas('petugas', fn ($sub) => $sub->where('nama_petugas', 'like', "%{$target}%")->orWhere('username', 'like', "%{$target}%"));
            });
        }

        // Filter Rentang Tanggal
        if ($request->filled('tgl_mulai') && $request->filled('tgl_akhir')) {
            $query->whereBetween('created_at', [$request->tgl_mulai.' 00:00:00', $request->tgl_akhir.' 23:59:59']);
        }

        // Filter Anomali: Di luar jam sekolah (sebelum 06:00 atau setelah 15:00)
        if ($request->has('luar_jam')) {
            $query->whereRaw('(TIME(created_at) < "06:00:00" OR TIME(created_at) > "15:00:00")');
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        return view('admin.audit', compact('logs'));
    }

    // ==================================================================
    // FUNGSI: EKSPOR DATA MENTAH KE CSV
    // ==================================================================
    public function exportAuditCsv(Request $request): StreamedResponse
    {
        $query = Transaksi::with(['nasabah', 'petugas']);

        if ($request->filled('target')) {
            $target = $request->target;
            $query->where(function ($q) use ($target) {
                $q->whereHas('nasabah', fn ($sub) => $sub->where('nis', 'like', "%{$target}%"))
                    ->orWhereHas('petugas', fn ($sub) => $sub->where('nama_petugas', 'like', "%{$target}%"));
            });
        }

        if ($request->filled('tgl_mulai') && $request->filled('tgl_akhir')) {
            $query->whereBetween('created_at', [$request->tgl_mulai.' 00:00:00', $request->tgl_akhir.' 23:59:59']);
        }

        if ($request->has('luar_jam')) {
            $query->whereRaw('(TIME(created_at) < "06:00:00" OR TIME(created_at) > "15:00:00")');
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=Audit_Trail_EBank_'.now()->format('Ymd_His').'.csv',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID Transaksi', 'Tanggal & Waktu', 'Petugas (Teller)', 'NIS Nasabah', 'Nama Nasabah', 'Jenis Transaksi', 'Nominal (Rp)', 'Status', 'Alasan Batal (Jika Void)']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
                    $log->petugas->nama_petugas ?? 'Sistem',
                    $log->nasabah->nis ?? '-',
                    $log->nasabah->nama_siswa ?? '-',
                    strtoupper($log->jenis_transaksi),
                    $log->nominal,
                    strtoupper($log->status),
                    $log->alasan_batal ?? '-',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function approveResetRequest(Request $request, int|string $id): RedirectResponse
    {
        $item = PasswordResetRequest::findOrFail($id);
        $user = User::where('username', $item->username)->first();

        if (! $user) {
            return back()->with('error', 'User akun "'.$item->username.'" tidak ditemukan.');
        }

        $newPassword = $request->input('new_password', 'bankmini'.date('Y'));
        $user->update([
            'password' => $newPassword,
        ]);

        $adminName = Auth::user()->nama_petugas ?? 'Administrator';

        $item->update([
            'status' => 'completed',
            'keterangan' => 'Disetujui oleh '.$adminName.'. Password di-reset menjadi: '.$newPassword,
        ]);

        return back()->with('success', 'Permohonan disetujui! Kata sandi akun '.$item->username.' berhasil direset menjadi: '.$newPassword);
    }

    public function rejectResetRequest(Request $request, int|string $id): RedirectResponse
    {
        $item = PasswordResetRequest::findOrFail($id);
        $adminName = Auth::user()->nama_petugas ?? 'Administrator';
        $alasan = $request->input('alasan', 'Data tidak sesuai atau permohonan ditolak.');

        $item->update([
            'status' => 'rejected',
            'keterangan' => 'Ditolak oleh '.$adminName.' (Alasan: '.$alasan.')',
        ]);

        return back()->with('success', 'Permohonan reset password berhasil ditolak.');
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

        return back()->with('success', 'Konfigurasi Sistem Global Perbankan Mini berhasil disimpan!');
    }
}
