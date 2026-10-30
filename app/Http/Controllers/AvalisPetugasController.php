<?php

namespace App\Http\Controllers;

use App\Helpers\AvalisSyncHelper;
use App\Models\AbsensiAvalis;
use App\Models\Avalis;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AvalisPetugasController extends Controller
{
    /**
     * Tampilkan Manajemen Master Data Petugas Avalis / Teller
     */
    public function index(Request $request): View
    {
        // Otomatis sinkronisasi data master avalis dari tabel users
        AvalisSyncHelper::autoSync(false);

        $query = Avalis::with('user:id,username,nama_petugas,foto');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'LIKE', "%{$q}%")
                    ->orWhere('kelas', 'LIKE', "%{$q}%")
                    ->orWhere('no_wa', 'LIKE', "%{$q}%");
            });
        }

        $avalisList = $query->orderBy('nama', 'asc')->get();

        // Ambil daftar users yang bertindak sebagai teller
        $usersTeller = User::where('role', 'teller')->get();

        $role = strtolower(Auth::user()->role ?? 'teller');
        $isAdmin = ($role === 'admin');

        return view('avalis.petugas', compact('avalisList', 'usersTeller', 'role', 'isAdmin'));
    }

    /**
     * Tambah 1 Petugas Avalis Baru
     */
    public function store(Request $request): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berwenang menambah petugas avalis.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'kelas' => 'required|string|max:50',
            'no_wa' => 'required|string|max:25',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $avalis = Avalis::create([
            ...$validated,
            'status' => 'aktif',
        ]);

        // Hubungkan otomatis ke lembar Absensi Avalis hari ini jika diminta
        if ($request->boolean('masuk_absensi_hari_ini')) {
            AbsensiAvalis::create([
                'tanggal' => now()->toDateString(),
                'nama' => $validated['nama'],
                'kelas' => $validated['kelas'],
                'no_wa' => $validated['no_wa'],
                'shift_jaga' => $request->shift_jaga ?? 'Shift Pagi (07.30 - 11.30)',
                'status' => 'Hadir',
                'keterangan' => 'Petugas Avalis Terdaftar (Auto-Sync)',
                'created_by' => Auth::user()->nama_petugas ?? 'Admin',
            ]);
        }

        return back()->with('success', "Petugas Avalis '{$validated['nama']}' berhasil ditambahkan!");
    }

    /**
     * Tambah Banyak Identitas Teller Sekaligus (Bulk Add)
     * Format baris: Nama, Kelas, No WA
     */
    public function storeBulk(Request $request): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berwenang melakukan bulk import data petugas.');
        }

        $request->validate([
            'data_bulk' => 'required|string',
        ]);

        $lines = explode("\n", str_replace("\r", '', $request->data_bulk));
        $sukses = 0;
        $gagal = 0;
        $today = now()->toDateString();
        $adminName = Auth::user()->nama_petugas ?? 'Admin';
        $syncAbsensi = $request->boolean('sync_ke_absensi');

        DB::transaction(function () use ($lines, $syncAbsensi, $today, $adminName, &$sukses, &$gagal) {
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $parts = preg_split('/[,;]/', $line);
                if (count($parts) >= 2) {
                    $nama = trim($parts[0]);
                    $kelas = trim($parts[1]);
                    $noWa = isset($parts[2]) ? trim($parts[2]) : '-';

                    if (! empty($nama) && ! empty($kelas)) {
                        Avalis::create([
                            'nama' => $nama,
                            'kelas' => $kelas,
                            'no_wa' => $noWa,
                            'status' => 'aktif',
                        ]);

                        if ($syncAbsensi) {
                            AbsensiAvalis::create([
                                'tanggal' => $today,
                                'nama' => $nama,
                                'kelas' => $kelas,
                                'no_wa' => $noWa,
                                'shift_jaga' => 'Shift Pagi (07.30 - 11.30)',
                                'status' => 'Hadir',
                                'keterangan' => 'Piket Avalis Terjadwal (Bulk Import)',
                                'created_by' => $adminName,
                            ]);
                        }

                        $sukses++;
                    } else {
                        $gagal++;
                    }
                } else {
                    $gagal++;
                }
            }
        });

        return back()->with('success', "Bulk Import selesai! {$sukses} petugas berhasil ditambahkan ({$gagal} baris diabaikan karena format tidak lengkap).");
    }

    /**
     * Masukkan Avalis Terpilih ke Jadwal Absensi Hari Ini
     */
    public function syncKeAbsensi(Request $request, int|string $id): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berwenang menjadwalkan absensi.');
        }

        $avalis = Avalis::findOrFail($id);
        $today = now()->toDateString();

        $shift = $request->input('shift_jaga', 'Shift Pagi (07.30 - 11.30)');
        $keterangan = $request->input('keterangan', 'Petugas Loket Terjadwal (Manual Add)');

        // Periksa apakah sudah ada di absensi hari ini
        $ada = AbsensiAvalis::where('nama', $avalis->nama)
            ->whereDate('tanggal', $today)
            ->first();

        if ($ada) {
            $ada->update([
                'shift_jaga' => $shift,
                'status' => 'Hadir',
                'keterangan' => $keterangan,
            ]);

            return back()->with('success', "Penugasan '{$avalis->nama}' pada absensi hari ini berhasil diperbarui!");
        }

        AbsensiAvalis::create([
            'tanggal' => $today,
            'nama' => $avalis->nama,
            'kelas' => $avalis->kelas,
            'no_wa' => $avalis->no_wa,
            'shift_jaga' => $shift,
            'status' => 'Hadir',
            'keterangan' => $keterangan,
            'created_by' => Auth::user()->nama_petugas ?? 'Admin',
        ]);

        return back()->with('success', "Petugas '{$avalis->nama}' berhasil dijadwalkan masuk di absensi hari ini!");
    }

    /**
     * Sinkronisasi data Avalis dengan data Pegawai asli di tabel Users
     */
    public function syncFromUsers(Request $request): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berwenang menjalankan sinkronisasi data master.');
        }

        $res = AvalisSyncHelper::autoSync(true);

        return back()->with('success', "Sinkronisasi berhasil! {$res['synced_avalis']} master data petugas diperbarui dari akun sistem.");
    }

    /**
     * Hapus Data Petugas Avalis
     */
    public function destroy(int|string $id): RedirectResponse
    {
        if (strtolower(Auth::user()->role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berwenang menghapus master petugas.');
        }

        $avalis = Avalis::findOrFail($id);
        $avalis->delete();

        return back()->with('success', 'Data petugas Avalis berhasil dihapus.');
    }
}
