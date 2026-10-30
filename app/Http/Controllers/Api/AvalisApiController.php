<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AvalisSyncHelper;
use App\Http\Controllers\Controller;
use App\Models\AbsensiAvalis;
use App\Models\Nasabah;
use App\Models\VerifikasiKunjunganSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvalisApiController extends Controller
{
    /**
     * Daftar Presensi & Kehadiran Petugas Avalis
     */
    public function listAbsensi(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal', now()->toDateString());
        $shift = $request->query('shift');
        $status = $request->query('status');

        $query = AbsensiAvalis::with(['user:id,nama_petugas,foto', 'avalis.user:id,nama_petugas,foto'])
            ->whereDate('tanggal', $tanggal);

        if ($shift) {
            $query->where('shift_jaga', 'like', "%{$shift}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        $items = $query->orderBy('nama', 'asc')->get();

        $summary = [
            'tanggal' => $tanggal,
            'total_petugas' => $items->count(),
            'hadir' => $items->where('status', 'Hadir')->count(),
            'izin' => $items->where('status', 'Izin')->count(),
            'tidak_masuk' => $items->where('status', 'Tidak Masuk')->count(),
        ];

        $data = $items->map(fn ($item) => [
            'id' => $item->id,
            'nama' => $item->nama,
            'foto_url' => $item->foto_url,
            'kelas' => $item->kelas,
            'no_wa' => $item->no_wa,
            'shift_jaga' => $item->shift_jaga,
            'status' => $item->status,
            'keterangan' => $item->keterangan,
            'created_by' => $item->created_by,
        ]);

        return response()->json([
            'success' => true,
            'summary' => $summary,
            'data' => $data,
        ], 200);
    }

    /**
     * Jadwal Shift Jaga Petugas Avalis Hari Ini (Shift Pagi & Siang)
     */
    public function jadwalHariIni(Request $request): JsonResponse
    {
        AvalisSyncHelper::autoSync(true);

        $today = now()->toDateString();
        $absensi = AbsensiAvalis::with(['user:id,nama_petugas,foto', 'avalis.user:id,nama_petugas,foto'])
            ->whereDate('tanggal', $today)
            ->get();

        $shiftPagi = $absensi->filter(fn ($a) => str_contains($a->shift_jaga, 'Pagi'))->values()->map(fn ($a) => [
            'id' => $a->id,
            'nama' => $a->nama,
            'foto_url' => $a->foto_url,
            'kelas' => $a->kelas,
            'no_wa' => $a->no_wa,
            'status' => $a->status,
            'keterangan' => $a->keterangan,
        ]);

        $shiftSiang = $absensi->filter(fn ($a) => str_contains($a->shift_jaga, 'Siang'))->values()->map(fn ($a) => [
            'id' => $a->id,
            'nama' => $a->nama,
            'foto_url' => $a->foto_url,
            'kelas' => $a->kelas,
            'no_wa' => $a->no_wa,
            'status' => $a->status,
            'keterangan' => $a->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'tanggal' => $today,
            'data' => [
                'shift_pagi' => [
                    'label' => 'Shift Pagi (07.30 - 11.30 WIB)',
                    'petugas' => $shiftPagi,
                ],
                'shift_siang' => [
                    'label' => 'Shift Siang (11.30 - 15.30 WIB)',
                    'petugas' => $shiftSiang,
                ],
            ],
        ], 200);
    }

    /**
     * Presensi / Check-in Mandiri Petugas Avalis Loket
     */
    public function checkIn(Request $request): JsonResponse
    {
        $request->validate([
            'nama' => 'nullable|string|max:150',
            'shift_jaga' => 'nullable|string',
            'status' => 'nullable|in:Hadir,Izin,Tidak Masuk',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $petugas = $request->user();
        $nama = $request->nama ?: ($petugas ? $petugas->nama_petugas : null);

        if (! $nama) {
            return response()->json([
                'success' => false,
                'message' => 'Nama petugas wajib dicantumkan!',
            ], 422);
        }

        $today = now()->toDateString();
        $record = AbsensiAvalis::where('nama', $nama)
            ->whereDate('tanggal', $today)
            ->first();

        $shift = $request->shift_jaga ?: ($record ? $record->shift_jaga : 'Shift Pagi (07.30 - 11.30)');
        $status = $request->status ?: 'Hadir';

        if ($record) {
            $record->update([
                'status' => $status,
                'shift_jaga' => $shift,
                'keterangan' => $request->keterangan ?: $record->keterangan,
            ]);
        } else {
            $record = AbsensiAvalis::create([
                'tanggal' => $today,
                'nama' => $nama,
                'kelas' => $petugas?->kelas ?: 'Petugas Avalis',
                'no_wa' => $petugas?->no_hp ?: '-',
                'shift_jaga' => $shift,
                'status' => $status,
                'keterangan' => $request->keterangan ?: 'Presensi via API Mobile Avalis',
                'created_by' => $petugas ? $petugas->nama_petugas : 'Petugas Avalis',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Presensi petugas {$nama} berhasil dicatat sebagai {$status}!",
            'data' => [
                'id' => $record->id,
                'nama' => $record->nama,
                'shift_jaga' => $record->shift_jaga,
                'status' => $record->status,
                'tanggal' => $today,
            ],
        ], 200);
    }

    /**
     * Monitoring Verifikasi Kunjungan Siswa ke Loket Pelayanan
     */
    public function kunjunganSiswa(Request $request): JsonResponse
    {
        $tanggal = $request->query('tanggal', now()->toDateString());
        $kelas = $request->query('kelas');

        $query = Nasabah::where('status', 'aktif');
        if ($kelas) {
            $query->where('kelas', $kelas);
        }
        $semuaSiswa = $query->orderBy('kelas')->orderBy('nama_siswa')->get();

        $verifikasiList = VerifikasiKunjunganSiswa::whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('nasabah_id');

        $terverifikasiCount = $verifikasiList->where('status_kunjungan', 'TERVERIFIKASI HADIR')->count();
        $tidakHadirCount = $verifikasiList->where('status_kunjungan', 'TIDAK HADIR')->count();
        $belumCount = max(0, $semuaSiswa->count() - ($terverifikasiCount + $tidakHadirCount));

        $data = $semuaSiswa->map(function ($s) use ($verifikasiList) {
            $verif = $verifikasiList->get($s->id);

            return [
                'id_nasabah' => $s->id,
                'nis' => $s->nis,
                'nama_siswa' => $s->nama_siswa,
                'kelas' => $s->kelas,
                'no_rekening' => $s->no_rekening,
                'status_kunjungan' => $verif ? $verif->status_kunjungan : 'BELUM DIVERIFIKASI',
                'waktu_verifikasi' => $verif && $verif->waktu_verifikasi ? date('H:i:s', strtotime($verif->waktu_verifikasi)) : null,
                'nama_petugas_verifikator' => $verif ? ($verif->nama_verifikator ?: $verif->nama_petugas) : null,
            ];
        });

        return response()->json([
            'success' => true,
            'summary' => [
                'tanggal' => $tanggal,
                'total_siswa' => $semuaSiswa->count(),
                'terverifikasi_hadir' => $terverifikasiCount,
                'tidak_hadir' => $tidakHadirCount,
                'belum_diverifikasi' => $belumCount,
            ],
            'data' => $data,
        ], 200);
    }

    /**
     * Verifikasi Kunjungan Siswa di Loket via Scan Barcode / NIS / Rekening
     */
    public function verifikasiKunjungan(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string', // NIS / No Rekening / ID Nasabah
            'status_kunjungan' => 'nullable|in:TERVERIFIKASI HADIR,TIDAK HADIR',
            'catatan' => 'nullable|string|max:255',
        ]);

        $identifier = $request->identifier;
        $nasabah = Nasabah::where('id', $identifier)
            ->orWhere('nis', $identifier)
            ->orWhere('no_rekening', $identifier)
            ->first();

        if (! $nasabah) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa / Nasabah tidak ditemukan!',
            ], 404);
        }

        $petugas = $request->user();
        $petugasId = $petugas ? $petugas->id : 1;
        $petugasNama = $petugas ? $petugas->nama_petugas : 'Petugas Loket Avalis';
        $today = now()->toDateString();
        $status = $request->status_kunjungan ?: 'TERVERIFIKASI HADIR';

        $verifikasi = VerifikasiKunjunganSiswa::updateOrCreate(
            [
                'nasabah_id' => $nasabah->id,
                'tanggal' => $today,
            ],
            [
                'user_id' => $petugasId,
                'nama_verifikator' => $petugasNama,
                'role_verifikator' => $petugas?->role ?: 'avalis',
                'status_kunjungan' => $status,
                'waktu_verifikasi' => now(),
                'keterangan' => $request->catatan ?: ($request->keterangan ?: 'Verifikasi kunjungan loket via scan'),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Kunjungan siswa {$nasabah->nama_siswa} ({$nasabah->kelas}) berhasil diverifikasi!",
            'data' => [
                'id_verifikasi' => $verifikasi->id,
                'nis' => $nasabah->nis,
                'nama_siswa' => $nasabah->nama_siswa,
                'kelas' => $nasabah->kelas,
                'status_kunjungan' => $verifikasi->status_kunjungan,
                'waktu_verifikasi' => $verifikasi->waktu_verifikasi->format('Y-m-d H:i:s'),
                'petugas' => $petugasNama,
            ],
        ], 200);
    }
}
