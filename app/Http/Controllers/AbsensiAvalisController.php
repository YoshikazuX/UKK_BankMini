<?php

namespace App\Http\Controllers;

use App\Helpers\AvalisSyncHelper;
use App\Models\AbsensiAvalis;
use App\Models\Avalis;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\VerifikasiKunjunganSiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsensiAvalisController extends Controller
{
    /**
     * Tampilkan Halaman Absensi Petugas Avalis
     */
    public function index(Request $request): View
    {
        // Otomatis sinkronisasi data absensi dengan data petugas asli di tabel users / avalis
        AvalisSyncHelper::autoSync(true);

        $query = AbsensiAvalis::query();

        // Filter Pencarian (Nama / Kelas / No WA / Keterangan)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('kelas', 'like', "%{$q}%")
                    ->orWhere('no_wa', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%");
            });
        }

        // Filter Tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Filter Shift
        if ($request->filled('shift')) {
            $query->where('shift_jaga', 'like', "%{$request->shift}%");
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $absensi = $query->with(['user:id,nama_petugas,foto', 'avalis.user:id,nama_petugas,foto'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Ringkasan Statistik Berdasarkan Database
        $totalAvalis = AbsensiAvalis::count();
        $totalHadir = AbsensiAvalis::where('status', 'Hadir')->count();
        $totalIzin = AbsensiAvalis::where('status', 'Izin')->count();
        $totalTidakMasuk = AbsensiAvalis::where('status', 'Tidak Masuk')->count();
        $totalShiftPagi = AbsensiAvalis::where('shift_jaga', 'like', '%Pagi%')->count();
        $totalShiftSiang = AbsensiAvalis::where('shift_jaga', 'like', '%Siang%')->count();

        // Daftar Petugas Avalis Terdaftar (untuk opsi auto-select di modal tambah khusus Admin)
        $petugasList = Avalis::where('status', 'aktif')->orderBy('nama', 'asc')->get();
        if ($petugasList->isEmpty()) {
            $petugasList = User::where('role', 'teller')
                ->select('nama_petugas as nama', 'kelas', 'no_hp as no_wa')
                ->get();
        }

        $user = Auth::user();
        $role = strtolower($user->role ?? 'teller');
        $isAdmin = ($role === 'admin');

        return view('avalis.index', compact(
            'absensi',
            'totalAvalis',
            'totalHadir',
            'totalIzin',
            'totalTidakMasuk',
            'totalShiftPagi',
            'totalShiftSiang',
            'petugasList',
            'role',
            'isAdmin'
        ));
    }

    /**
     * Sinkronisasi data absensi langsung dari tabel petugas (users / avalis)
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function syncFromUsers(Request $request): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator Utama yang memiliki wewenang melakukan sinkronisasi petugas.');
        }

        AvalisSyncHelper::autoSync(true);

        return back()->with('success', 'Sinkronisasi berhasil! Data absensi petugas avalis telah disesuaikan dengan data petugas asli di sistem.');
    }

    /**
     * Simpan Absensi Avalis Baru
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function store(Request $request): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator Utama yang memiliki wewenang menambahkan absensi petugas.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'no_wa' => 'required|string|max:25',
            'shift_jaga' => 'required|in:Shift Pagi (07.30 - 11.30),Shift Siang (11.30 - 15.30)',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Izin,Tidak Masuk',
            'keterangan' => 'nullable|string|max:255',
        ]);

        AbsensiAvalis::create([
            ...$validated,
            'created_by' => Auth::user()->nama_petugas ?? 'Administrator Utama',
        ]);

        return back()->with('success', 'Data absensi petugas Avalis berhasil disimpan oleh Administrator.');
    }

    /**
     * Update Absensi Avalis
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function update(Request $request, int|string $id): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator Utama yang memiliki wewenang mengedit data absensi petugas.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'no_wa' => 'required|string|max:25',
            'shift_jaga' => 'required|in:Shift Pagi (07.30 - 11.30),Shift Siang (11.30 - 15.30)',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Izin,Tidak Masuk',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $absensi = AbsensiAvalis::findOrFail($id);
        $absensi->update($validated);

        return back()->with('success', 'Data absensi petugas Avalis berhasil diperbarui.');
    }

    /**
     * Ubah Shift Kerja Petugas (Quick Action Administrator)
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function ubahShift(Request $request, int|string $id): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator Utama yang berwenang menentukan atau mengubah penugasan shift.');
        }

        $validated = $request->validate([
            'shift_jaga' => 'required|in:Shift Pagi (07.30 - 11.30),Shift Siang (11.30 - 15.30)',
        ]);

        $absensi = AbsensiAvalis::findOrFail($id);
        $absensi->update($validated);

        return back()->with('success', 'Penugasan shift petugas berhasil diubah oleh Administrator.');
    }

    /**
     * Ubah Status Kehadiran Petugas (Quick Action Administrator)
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function ubahStatus(Request $request, int|string $id): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator Utama yang berwenang menentukan status kehadiran petugas.');
        }

        $validated = $request->validate([
            'status' => 'required|in:Hadir,Izin,Tidak Masuk',
        ]);

        $absensi = AbsensiAvalis::findOrFail($id);
        $absensi->update($validated);

        return back()->with('success', "Status kehadiran petugas berhasil diubah menjadi '{$validated['status']}'.");
    }

    /**
     * Hapus Absensi Avalis
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function destroy(int|string $id): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator Utama yang memiliki wewenang menghapus data absensi.');
        }

        $absensi = AbsensiAvalis::findOrFail($id);
        $absensi->delete();

        return back()->with('success', 'Data absensi petugas Avalis berhasil dihapus.');
    }

    /**
     * Ekspor Data Absensi Avalis ke File CSV / Excel
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Fitur ekspor laporan absensi hanya dapat diakses oleh Administrator Utama.');
        }

        $query = AbsensiAvalis::query();

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('shift')) {
            $query->where('shift_jaga', 'like', "%{$request->shift}%");
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        $filename = 'Absensi_Petugas_Avalis_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['No', 'Tanggal', 'Nama Petugas', 'Kelas', 'No WhatsApp', 'Shift Jaga', 'Status Kehadiran', 'Keterangan / Tugas', 'Petugas Pencatat'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns, ';');

            $no = 1;
            foreach ($data as $row) {
                fputcsv($file, [
                    $no++,
                    $row->tanggal,
                    $row->nama,
                    $row->kelas,
                    "'".$row->no_wa,
                    $row->shift_jaga,
                    $row->status,
                    $row->keterangan ?? '-',
                    $row->created_by ?? '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Halaman Monitoring & Verifikasi Kunjungan Siswa ke Loket Pelayanan
     * (Dapat diakses oleh Admin, Supervisor, dan Teller)
     */
    public function kunjunganSiswa(Request $request): View
    {
        $tanggal = $request->input('tanggal', now()->toDateString());

        // 1. Jadwal Petugas Loket dari Absensi Avalis (Ditentukan Administrator)
        $jadwalPetugas = AbsensiAvalis::with(['user:id,nama_petugas,foto', 'avalis.user:id,nama_petugas,foto'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('shift_jaga', 'asc')
            ->orderBy('nama', 'asc')
            ->get();

        $jadwalShiftPagi = $jadwalPetugas->filter(fn ($p) => str_contains($p->shift_jaga, 'Pagi'))->values();
        $jadwalShiftSiang = $jadwalPetugas->filter(fn ($p) => str_contains($p->shift_jaga, 'Siang'))->values();

        // Opsi petugas avalis aktif untuk verifikasi
        $petugasAvalisPilihan = $jadwalPetugas->where('status', 'Hadir')->pluck('nama')->toArray();
        if (empty($petugasAvalisPilihan)) {
            $petugasAvalisPilihan = $jadwalPetugas->pluck('nama')->toArray();
        }

        // 2. Data Verifikasi Kunjungan Siswa pada tanggal ini
        $verifikasiList = VerifikasiKunjunganSiswa::whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('nasabah_id');

        // 3. Ringkasan Statistik Kunjungan Siswa Berdasarkan Tanggal Terpilih (Eksklusif Siswa)
        $totalSiswa = Nasabah::where('status', 'aktif')->count();
        $totalTerverifikasiHadir = $verifikasiList->where('status_kunjungan', 'TERVERIFIKASI HADIR')->count();
        $totalTidakHadir = $verifikasiList->where('status_kunjungan', 'TIDAK HADIR')->count();
        $totalBelumDiverifikasi = max(0, $totalSiswa - ($totalTerverifikasiHadir + $totalTidakHadir));

        // 4. Data Transaksi Pelayanan Siswa pada tanggal ini
        $transaksiHariIni = Transaksi::with('petugas:id,nama_petugas')
            ->whereDate('created_at', $tanggal)
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('nasabah_id');

        // 5. Query Nasabah Siswa
        $queryNasabah = Nasabah::where('status', 'aktif');
        if ($request->filled('q')) {
            $q = $request->q;
            $queryNasabah->where(function ($sub) use ($q) {
                $sub->where('nama_siswa', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
                    ->orWhere('kelas', 'like', "%{$q}%");
            });
        }
        if ($request->filled('kelas')) {
            $queryNasabah->where('kelas', $request->kelas);
        }

        $allNasabah = $queryNasabah->orderBy('kelas', 'asc')->orderBy('nama_siswa', 'asc')->get();

        // 6. Pemetaan status kunjungan, waktu verifikasi, transaksi, dan petugas verifikator
        $daftarKunjungan = $allNasabah->map(function ($s) use ($verifikasiList, $transaksiHariIni) {
            $verif = $verifikasiList->get($s->id);
            $trxList = $transaksiHariIni->get($s->id);
            $latestTrx = $trxList ? $trxList->first() : null;

            $statusKunjungan = $verif ? $verif->status_kunjungan : 'BELUM DIVERIFIKASI';

            return (object) [
                'id' => $s->id,
                'nis' => $s->nis,
                'nama_siswa' => $s->nama_siswa,
                'kelas' => $s->kelas,
                'no_rekening' => $s->no_rekening,
                'status_kunjungan' => $statusKunjungan,
                'waktu_verifikasi' => $verif && $verif->waktu_verifikasi ? date('d/m/Y H:i', strtotime($verif->waktu_verifikasi)).' WIB' : '-',
                'raw_waktu_verifikasi' => $verif ? $verif->waktu_verifikasi : null,
                'nama_verifikator' => $verif && $verif->nama_verifikator ? $verif->nama_verifikator.' ('.ucfirst($verif->role_verifikator).')' : '-',
                'raw_verifikator' => $verif ? $verif->nama_verifikator : null,
                'role_verifikator' => $verif ? $verif->role_verifikator : null,
                'loket' => $verif ? $verif->loket : null,
                'shift' => $verif ? $verif->shift : null,
                'petugas_avalis_nama' => $verif ? $verif->petugas_avalis_nama : null,
                'keterangan' => $verif ? $verif->keterangan : null,
                'verifikasi_id' => $verif ? $verif->id : null,
                'latest_trx' => $latestTrx,
                'trx_count' => $trxList ? $trxList->count() : 0,
            ];
        });

        // Filter berdasarkan status kunjungan
        if ($request->filled('status_kunjungan')) {
            $daftarKunjungan = $daftarKunjungan->filter(function ($item) use ($request) {
                return $item->status_kunjungan === $request->status_kunjungan;
            })->values();
        }

        // Daftar kelas untuk dropdown filter
        $kelasList = Nasabah::distinct()->pluck('kelas')->filter()->values();

        $user = Auth::user();
        $role = strtolower($user->role ?? 'teller');
        $isAdmin = ($role === 'admin');
        $isTeller = ($role === 'teller');
        $isSupervisor = ($role === 'supervisor');
        $canVerify = in_array($role, ['admin', 'teller'], true);

        return view('avalis.kunjungan-siswa', compact(
            'tanggal',
            'jadwalShiftPagi',
            'jadwalShiftSiang',
            'petugasAvalisPilihan',
            'totalSiswa',
            'totalTerverifikasiHadir',
            'totalBelumDiverifikasi',
            'totalTidakHadir',
            'daftarKunjungan',
            'kelasList',
            'role',
            'isAdmin',
            'isTeller',
            'isSupervisor',
            'canVerify'
        ));
    }

    /**
     * Simpan Verifikasi Kehadiran Fisik Siswa di Loket
     * HANYA BISA DIAKSES OLEH TELLER DAN ADMINISTRATOR
     */
    public function simpanVerifikasiKunjungan(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');

        // Hak akses: Teller dan Administrator
        if (! in_array($role, ['admin', 'teller'], true)) {
            abort(403, 'Akses Ditolak: Hanya Teller atau Administrator yang berwenang melakukan verifikasi kunjungan siswa.');
        }

        $validated = $request->validate([
            'nasabah_id' => 'required|exists:nasabah,id',
            'tanggal' => 'required|date',
            'status_kunjungan' => 'required|in:TERVERIFIKASI HADIR,TIDAK HADIR',
            'loket' => 'nullable|string|max:50',
            'shift' => 'nullable|string|max:100',
            'petugas_avalis_nama' => 'nullable|string|max:150',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $verifikasi = VerifikasiKunjunganSiswa::updateOrCreate(
            [
                'nasabah_id' => $validated['nasabah_id'],
                'tanggal' => $validated['tanggal'],
            ],
            [
                'status_kunjungan' => $validated['status_kunjungan'],
                'waktu_verifikasi' => now(),
                'user_id' => $user->id,
                'nama_verifikator' => $user->nama_petugas ?? 'Petugas Loket',
                'role_verifikator' => $user->role ?? 'teller',
                'loket' => $validated['loket'] ?: 'Loket 1',
                'shift' => $validated['shift'] ?: 'Shift Pagi (07.30 - 11.30)',
                'petugas_avalis_nama' => $validated['petugas_avalis_nama'] ?: null,
                'keterangan' => $validated['keterangan'] ?: null,
            ]
        );

        $namaSiswa = Nasabah::where('id', $validated['nasabah_id'])->value('nama_siswa') ?? 'Siswa';

        return back()->with('success', "Kehadiran fisik {$namaSiswa} berhasil diverifikasi sebagai '{$validated['status_kunjungan']}'.");
    }

    /**
     * Reset Status Verifikasi Kunjungan Siswa
     * HANYA BISA DIAKSES OLEH ADMINISTRATOR
     */
    public function resetVerifikasiKunjungan(Request $request, int|string $id): RedirectResponse
    {
        $user = Auth::user();
        if (strtolower($user->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berwenang mereset verifikasi kunjungan siswa.');
        }

        $item = VerifikasiKunjunganSiswa::findOrFail($id);
        $item->delete();

        return back()->with('success', 'Status verifikasi kunjungan siswa berhasil direset menjadi Belum Diverifikasi.');
    }
}
