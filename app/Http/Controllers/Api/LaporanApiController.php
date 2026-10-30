<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiAvalis;
use App\Models\Brankas;
use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\Transaksi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanApiController extends Controller
{
    /**
     * Ringkasan Eksekutif Operasional Bank Mini Hari Ini
     */
    public function ringkasanHarian(Request $request): JsonResponse
    {
        $today = now()->toDateString();

        $totalNasabah = Nasabah::where('status', 'aktif')->count();
        $totalDanaNasabah = (float) Nasabah::where('status', 'aktif')->sum('saldo');

        $brankas = Brankas::where('tanggal', $today)->first();
        $kasFisik = $brankas ? (float) $brankas->total : (float) (Brankas::orderBy('tanggal', 'desc')->value('total') ?? 0);

        $trxHariIni = Transaksi::whereDate('created_at', $today)->where('status', 'sukses')->get();
        $totalSetorHariIni = (float) $trxHariIni->where('jenis_transaksi', 'setor')->sum('nominal');
        $totalTarikHariIni = (float) $trxHariIni->where('jenis_transaksi', 'tarik')->sum('nominal');

        $totalPetugasJaga = AbsensiAvalis::whereDate('tanggal', $today)->count();
        $petugasHadir = AbsensiAvalis::whereDate('tanggal', $today)->where('status', 'Hadir')->count();

        return response()->json([
            'success' => true,
            'tanggal' => $today,
            'data' => [
                'nasabah' => [
                    'total_nasabah_aktif' => $totalNasabah,
                    'total_dana_tabungan' => $totalDanaNasabah,
                    'formatted_dana_tabungan' => 'Rp '.number_format($totalDanaNasabah, 0, ',', '.'),
                ],
                'kas_operasional' => [
                    'kas_fisik_tersedia' => $kasFisik,
                    'formatted_kas_fisik' => 'Rp '.number_format($kasFisik, 0, ',', '.'),
                    'kas_masuk_hari_ini' => $totalSetorHariIni,
                    'formatted_kas_masuk' => 'Rp '.number_format($totalSetorHariIni, 0, ',', '.'),
                    'kas_keluar_hari_ini' => $totalTarikHariIni,
                    'formatted_kas_keluar' => 'Rp '.number_format($totalTarikHariIni, 0, ',', '.'),
                ],
                'transaksi_hari_ini' => [
                    'frekuensi_transaksi' => $trxHariIni->count(),
                    'frekuensi_setor' => $trxHariIni->where('jenis_transaksi', 'setor')->count(),
                    'frekuensi_tarik' => $trxHariIni->where('jenis_transaksi', 'tarik')->count(),
                ],
                'petugas_avalis' => [
                    'total_jadwal' => $totalPetugasJaga,
                    'hadir' => $petugasHadir,
                ],
            ],
        ], 200);
    }

    /**
     * Agregasi Simpanan Tabungan Per Kelas / Rombel
     */
    public function saldoPerKelas(Request $request): JsonResponse
    {
        $data = Nasabah::where('status', 'aktif')
            ->select('kelas', DB::raw('COUNT(*) as total_siswa'), DB::raw('SUM(saldo) as total_saldo'))
            ->groupBy('kelas')
            ->orderBy('kelas', 'asc')
            ->get()
            ->map(fn ($row) => [
                'kelas' => $row->kelas,
                'total_siswa' => (int) $row->total_siswa,
                'total_saldo' => (float) $row->total_saldo,
                'formatted_saldo' => 'Rp '.number_format($row->total_saldo, 0, ',', '.'),
                'rata_rata_saldo' => (float) ($row->total_siswa > 0 ? round($row->total_saldo / $row->total_siswa) : 0),
            ]);

        return response()->json([
            'success' => true,
            'total_kelas' => $data->count(),
            'data' => $data,
        ], 200);
    }

    /**
     * Data Mutasi Jurnal Akuntansi Perbankan Mini
     */
    public function mutasiJurnal(Request $request): JsonResponse
    {
        $dariTanggal = $request->query('dari_tanggal', now()->startOfMonth()->toDateString());
        $sampaiTanggal = $request->query('sampai_tanggal', now()->toDateString());

        $jurnal = Jurnal::whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        $totalDebit = (float) $jurnal->sum('debit');
        $totalKredit = (float) $jurnal->sum('kredit');

        return response()->json([
            'success' => true,
            'periode' => [
                'dari' => $dariTanggal,
                'sampai' => $sampaiTanggal,
            ],
            'summary' => [
                'total_entri' => $jurnal->count(),
                'total_debit' => $totalDebit,
                'formatted_debit' => 'Rp '.number_format($totalDebit, 0, ',', '.'),
                'total_kredit' => $totalKredit,
                'formatted_kredit' => 'Rp '.number_format($totalKredit, 0, ',', '.'),
            ],
            'data' => $jurnal->map(fn ($j) => [
                'id' => $j->id,
                'tanggal' => $j->tanggal,
                'keterangan' => $j->keterangan,
                'debit' => (float) $j->debit,
                'kredit' => (float) $j->kredit,
            ]),
        ], 200);
    }
}
