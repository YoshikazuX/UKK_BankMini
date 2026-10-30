<?php

namespace App\Helpers;

use App\Models\AbsensiAvalis;
use App\Models\Avalis;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AvalisSyncHelper
{
    /**
     * Jalankan sinkronisasi otomatis antara tabel users (pegawai/petugas)
     * dengan tabel avalis (master) dan tabel absensi_avalis (harian).
     *
     * @return array{status: bool, message?: string, synced_avalis?: int, synced_absensi?: int}
     */
    public static function autoSync(bool $syncToAbsensiToday = true): array
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('avalis') || ! Schema::hasTable('absensi_avalis')) {
            return ['status' => false, 'message' => 'Tabel belum siap'];
        }

        // 1. Bersihkan placeholder dummy statis lama
        $placeholders = ['Siti Nurhaliza', 'Ahmad Fauzi Rahman', 'Rina Anggraeni', 'Budi Pratama', 'Dewi Sartika Putri'];
        AbsensiAvalis::whereIn('nama', $placeholders)->delete();

        // Normalisasi status lama jika ada
        AbsensiAvalis::where('status', 'Alpha')->update(['status' => 'Tidak Masuk']);
        AbsensiAvalis::where('status', 'Sakit')->update(['status' => 'Izin']);

        // 2. Ambil seluruh data petugas teller aktif dari tabel users
        $tellers = User::where('role', 'teller')->get();
        if ($tellers->isEmpty()) {
            $tellers = User::whereIn('role', ['admin', 'supervisor'])->get();
        }

        $today = now()->toDateString();
        $syncedAvalis = 0;
        $syncedAbsensi = 0;

        foreach ($tellers as $idx => $t) {
            $kelas = $t->kelas ?: 'XII AKL '.($idx + 1);
            $noWa = $t->no_hp ?: '0812'.rand(10000000, 99999999);

            // Perbarui data user jika belum lengkap
            if (empty($t->kelas) || empty($t->no_hp)) {
                $t->update([
                    'kelas' => $t->kelas ?: $kelas,
                    'no_hp' => $t->no_hp ?: $noWa,
                ]);
            }

            // SINKRONISASI KE MASTER AVALIS
            $avalis = Avalis::where('user_id', $t->id)->first()
                ?: Avalis::where('nama', $t->nama_petugas)->first();

            if ($avalis) {
                $avalis->update([
                    'nama' => $t->nama_petugas,
                    'kelas' => $t->kelas ?: $kelas,
                    'no_wa' => $t->no_hp ?: $noWa,
                    'user_id' => $t->id,
                    'status' => 'aktif',
                ]);
            } else {
                Avalis::create([
                    'nama' => $t->nama_petugas,
                    'kelas' => $kelas,
                    'no_wa' => $noWa,
                    'user_id' => $t->id,
                    'status' => 'aktif',
                ]);
                $syncedAvalis++;
            }

            // SINKRONISASI KE ABSENSI HARI INI
            if ($syncToAbsensiToday) {
                $shift = ($idx % 2 === 0) ? 'Shift Pagi (07.30 - 11.30)' : 'Shift Siang (11.30 - 15.30)';
                $keterangan = ($idx % 2 === 0)
                    ? 'Standby Loket Teller 1 (Setoran & Penarikan)'
                    : 'Standby Loket Teller 2 (Verifikasi Buku & Kas)';

                $absensi = AbsensiAvalis::where('nama', $t->nama_petugas)
                    ->whereDate('tanggal', $today)
                    ->first();

                if (! $absensi) {
                    AbsensiAvalis::create([
                        'tanggal' => $today,
                        'nama' => $t->nama_petugas,
                        'kelas' => $t->kelas ?: $kelas,
                        'no_wa' => $t->no_hp ?: $noWa,
                        'shift_jaga' => $shift,
                        'status' => 'Hadir',
                        'keterangan' => $keterangan,
                        'created_by' => 'Sistem Otomatis',
                    ]);
                    $syncedAbsensi++;
                } else {
                    $absensi->update([
                        'kelas' => $t->kelas ?: $kelas,
                        'no_wa' => $t->no_hp ?: $noWa,
                    ]);
                }
            }
        }

        return [
            'status' => true,
            'synced_avalis' => $syncedAvalis,
            'synced_absensi' => $syncedAbsensi,
        ];
    }

    /**
     * Ambil ringkasan kehadiran petugas avalis untuk Dashboard (Admin, Supervisor, Teller).
     */
    public static function getDashboardSummary(?string $date = null): object
    {
        $targetDate = $date ?: now()->toDateString();

        // Pastikan sinkronisasi hari ini sudah berjalan
        self::autoSync(true);

        $query = AbsensiAvalis::whereDate('tanggal', $targetDate);

        $totalPetugas = (clone $query)->count();
        $totalHadir = (clone $query)->where('status', 'Hadir')->count();
        $totalIzin = (clone $query)->where('status', 'Izin')->count();
        $totalTidakMasuk = (clone $query)->where('status', 'Tidak Masuk')->count();
        $totalShiftPagi = (clone $query)->where('shift_jaga', 'like', '%Pagi%')->count();
        $totalShiftSiang = (clone $query)->where('shift_jaga', 'like', '%Siang%')->count();

        $daftarPetugas = (clone $query)
            ->with(['user:id,nama_petugas,foto', 'avalis.user:id,nama_petugas,foto'])
            ->orderByRaw("CASE WHEN shift_jaga LIKE '%Pagi%' THEN 1 ELSE 2 END")
            ->orderBy('nama', 'asc')
            ->get();

        return (object) [
            'tanggal' => $targetDate,
            'totalPetugas' => $totalPetugas,
            'totalHadir' => $totalHadir,
            'totalIzin' => $totalIzin,
            'totalTidakMasuk' => $totalTidakMasuk,
            'totalShiftPagi' => $totalShiftPagi,
            'totalShiftSiang' => $totalShiftSiang,
            'daftarPetugas' => $daftarPetugas,
        ];
    }
}
