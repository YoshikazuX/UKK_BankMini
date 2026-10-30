<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Jurnal;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperasionalKasApiController extends Controller
{
    // =========================================================================
    // 1. MODUL PENGAJUAN DROPING KAS (TELLER)
    // =========================================================================

    /**
     * Daftar Riwayat Pengajuan Kas Teller Saat Ini
     */
    public function listPengajuanKasTeller(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user ? $user->id : 1;
        $today = Carbon::today()->toDateString();

        $brankasHariIni = Brankas::where('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? (float) $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? (float) $brankasHariIni->saldo_awal : 0;

        $list = PengajuanKas::with('approver')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'nomor_pengajuan' => $p->nomor_pengajuan,
                'tanggal' => $p->tanggal ? $p->tanggal->format('Y-m-d') : null,
                'nominal' => (float) $p->nominal,
                'formatted_nominal' => 'Rp '.number_format($p->nominal, 0, ',', '.'),
                'alasan' => $p->alasan,
                'status' => $p->status,
                'nama_spv' => $p->approver?->nama_petugas ?? '-',
                'catatan_spv' => $p->catatan_spv,
                'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
            ]);

        return response()->json([
            'success' => true,
            'summary' => [
                'kas_awal_hari_ini' => $kasAwal,
                'kas_tersedia_laci' => $kasTersedia,
                'total_pengajuan' => $list->count(),
            ],
            'data' => $list,
        ], 200);
    }

    /**
     * Teller Mengirim Pengajuan Kas Tambahan (Droping)
     */
    public function storePengajuanKasTeller(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:10000',
            'alasan' => 'required|string|max:500',
        ]);

        $user = $request->user();
        $userId = $user ? $user->id : 1;
        $today = Carbon::today()->toDateString();

        $countToday = PengajuanKas::whereDate('tanggal', $today)->count() + 1;
        $nomorPengajuan = 'PK-'.date('Ymd').'-'.str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);

        $pengajuan = PengajuanKas::create([
            'nomor_pengajuan' => $nomorPengajuan,
            'user_id' => $userId,
            'tanggal' => $today,
            'nominal' => (int) $validated['nominal'],
            'alasan' => $validated['alasan'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pengajuan kas #{$nomorPengajuan} berhasil dikirim! Menunggu persetujuan Supervisor.",
            'data' => [
                'id' => $pengajuan->id,
                'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                'nominal' => (float) $pengajuan->nominal,
                'formatted_nominal' => 'Rp '.number_format($pengajuan->nominal, 0, ',', '.'),
                'status' => $pengajuan->status,
                'tanggal' => $today,
            ],
        ], 201);
    }

    // =========================================================================
    // 2. MODUL DAILY REPORT TUTUP KAS (TELLER)
    // =========================================================================

    /**
     * Cek Posisi Saldo Tutup Kas & Laporan Hari Ini bagi Teller
     */
    public function statusDailyReportTeller(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user ? $user->id : 1;
        $today = Carbon::today()->toDateString();

        $trxHariIni = Transaksi::where('petugas_id', $userId)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalDeposit = (float) $trxHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalWithdrawal = (float) $trxHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        $brankasToday = Brankas::where('tanggal', $today)->first();
        $openingBalance = $brankasToday ? (float) $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit - $totalWithdrawal;

        $myReportToday = DailyReport::with('approver')
            ->where('user_id', $userId)
            ->whereDate('report_date', $today)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tanggal' => $today,
                'opening_balance' => $openingBalance,
                'formatted_opening' => 'Rp '.number_format($openingBalance, 0, ',', '.'),
                'total_deposit' => $totalDeposit,
                'formatted_deposit' => 'Rp '.number_format($totalDeposit, 0, ',', '.'),
                'total_withdrawal' => $totalWithdrawal,
                'formatted_withdrawal' => 'Rp '.number_format($totalWithdrawal, 0, ',', '.'),
                'system_balance' => $systemBalance,
                'formatted_system' => 'Rp '.number_format($systemBalance, 0, ',', '.'),
                'is_submitted' => $myReportToday !== null,
                'report' => $myReportToday ? [
                    'id' => $myReportToday->id,
                    'physical_balance' => (float) $myReportToday->physical_balance,
                    'difference' => (float) $myReportToday->difference,
                    'status' => $myReportToday->status,
                    'notes' => $myReportToday->notes,
                    'approver' => $myReportToday->approver?->nama_petugas,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Teller Submit Laporan Kas Tutup Harian
     */
    public function storeDailyReportTeller(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'physical_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $userId = $user ? $user->id : 1;
        $today = Carbon::today()->toDateString();

        $trxHariIni = Transaksi::where('petugas_id', $userId)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalDeposit = (int) $trxHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalWithdrawal = (int) $trxHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        $brankasToday = Brankas::where('tanggal', $today)->first();
        $openingBalance = $brankasToday ? (int) $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit - $totalWithdrawal;
        $physicalBalance = (int) $validated['physical_balance'];
        $difference = $physicalBalance - $systemBalance;

        $report = DailyReport::updateOrCreate(
            [
                'user_id' => $userId,
                'report_date' => $today,
            ],
            [
                'opening_balance' => $openingBalance,
                'total_deposit' => $totalDeposit,
                'total_withdrawal' => $totalWithdrawal,
                'system_balance' => $systemBalance,
                'physical_balance' => $physicalBalance,
                'difference' => $difference,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan kas harian berhasil diserahkan! Menunggu verifikasi Supervisor.',
            'data' => [
                'id' => $report->id,
                'report_date' => $today,
                'system_balance' => (float) $systemBalance,
                'physical_balance' => (float) $physicalBalance,
                'difference' => (float) $difference,
                'status' => $report->status,
            ],
        ], 200);
    }

    // =========================================================================
    // 3. MODUL SUPERVISOR: APPROVAL KAS & DAILY REPORT
    // =========================================================================

    /**
     * Supervisor: Daftar Seluruh Pengajuan Kas Teller
     */
    public function listPengajuanKasSupervisor(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $tanggal = $request->query('tanggal');

        $query = PengajuanKas::with(['user:id,nama_petugas,kelas', 'approver:id,nama_petugas']);

        if ($status) {
            $query->where('status', $status);
        }
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $list = $query->orderBy('created_at', 'desc')->get()->map(fn ($p) => [
            'id' => $p->id,
            'nomor_pengajuan' => $p->nomor_pengajuan,
            'tanggal' => $p->tanggal ? $p->tanggal->format('Y-m-d') : null,
            'nominal' => (float) $p->nominal,
            'formatted_nominal' => 'Rp '.number_format($p->nominal, 0, ',', '.'),
            'alasan' => $p->alasan,
            'status' => $p->status,
            'nama_teller' => $p->user?->nama_petugas ?? '-',
            'kelas_teller' => $p->user?->kelas ?? '-',
            'disetujui_oleh' => $p->approver?->nama_petugas ?? '-',
            'catatan_spv' => $p->catatan_spv,
            'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'count' => $list->count(),
            'data' => $list,
        ], 200);
    }

    /**
     * Supervisor: Setujui Tiket Droping Kas
     */
    public function setujuiPengajuanKas(Request $request, int|string $id): JsonResponse
    {
        $pengajuan = PengajuanKas::find($id);
        if (! $pengajuan) {
            return response()->json(['success' => false, 'message' => 'Data pengajuan kas tidak ditemukan!'], 404);
        }

        if ($pengajuan->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan kas ini sudah pernah diproses.'], 422);
        }

        $today = Carbon::today()->toDateString();
        $spvId = $request->user()?->id ?: 1;

        DB::beginTransaction();
        try {
            $pengajuan->update([
                'status' => 'disetujui',
                'disetujui_oleh' => $spvId,
                'catatan_spv' => $request->catatan_spv ?? 'Disetujui untuk penambahan modal kerja kas loket',
            ]);

            // Tambah kas fisik di brankas
            $brankasHariIni = Brankas::where('tanggal', $today)->lockForUpdate()->first();
            if ($brankasHariIni) {
                $masuk = $brankasHariIni->masuk + $pengajuan->nominal;
                $total = $brankasHariIni->saldo_awal + $masuk - $brankasHariIni->keluar;
                $brankasHariIni->update([
                    'masuk' => $masuk,
                    'total' => $total,
                ]);
            } else {
                $kemarin = Brankas::orderBy('tanggal', 'desc')->first();
                $saldoAwal = $kemarin ? $kemarin->total : 0;
                $total = $saldoAwal + $pengajuan->nominal;
                Brankas::create([
                    'tanggal' => $today,
                    'saldo_awal' => $saldoAwal,
                    'masuk' => $pengajuan->nominal,
                    'keluar' => 0,
                    'total' => $total,
                ]);
            }

            // Catat mutasi jurnal debit kas droping
            $teller = User::find($pengajuan->user_id);
            $namaTeller = $teller ? $teller->nama_petugas : 'Teller Loket';

            Jurnal::create([
                'transaksi_id' => null,
                'keterangan' => "PENERIMAAN DROPING KAS OPERASIONAL [{$pengajuan->nomor_pengajuan}] - {$namaTeller}",
                'debit' => $pengajuan->nominal,
                'kredit' => 0,
                'tanggal' => $today,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengajuan kas #{$pengajuan->nomor_pengajuan} berhasil disetujui! Kas operasional otomatis bertambah.",
                'data' => [
                    'id' => $pengajuan->id,
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'status' => 'disetujui',
                ],
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal menyetujui pengajuan kas: '.$e->getMessage()], 500);
        }
    }

    /**
     * Supervisor: Tolak Tiket Droping Kas
     */
    public function tolakPengajuanKas(Request $request, int|string $id): JsonResponse
    {
        $pengajuan = PengajuanKas::find($id);
        if (! $pengajuan) {
            return response()->json(['success' => false, 'message' => 'Data pengajuan kas tidak ditemukan!'], 404);
        }

        $spvId = $request->user()?->id ?: 1;

        $pengajuan->update([
            'status' => 'ditolak',
            'disetujui_oleh' => $spvId,
            'catatan_spv' => $request->catatan_spv ?? 'Ditolak: Kas loket dinilai masih mencukupi.',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Pengajuan kas #{$pengajuan->nomor_pengajuan} telah ditolak.",
            'data' => [
                'id' => $pengajuan->id,
                'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                'status' => 'ditolak',
            ],
        ], 200);
    }

    /**
     * Supervisor: Daftar Seluruh Daily Report Teller
     */
    public function listDailyReportSupervisor(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $tanggal = $request->query('tanggal');

        $query = DailyReport::with(['user:id,nama_petugas,kelas', 'approver:id,nama_petugas']);

        if ($status) {
            $query->where('status', $status);
        }
        if ($tanggal) {
            $query->whereDate('report_date', $tanggal);
        }

        $list = $query->orderBy('report_date', 'desc')->orderBy('id', 'desc')->get()->map(fn ($r) => [
            'id' => $r->id,
            'report_date' => $r->report_date ? $r->report_date->format('Y-m-d') : null,
            'nama_teller' => $r->user?->nama_petugas ?? '-',
            'kelas_teller' => $r->user?->kelas ?? '-',
            'opening_balance' => (float) $r->opening_balance,
            'total_deposit' => (float) $r->total_deposit,
            'total_withdrawal' => (float) $r->total_withdrawal,
            'system_balance' => (float) $r->system_balance,
            'physical_balance' => (float) $r->physical_balance,
            'difference' => (float) $r->difference,
            'status' => $r->status,
            'notes' => $r->notes,
            'verified_by' => $r->approver?->nama_petugas ?? '-',
        ]);

        return response()->json([
            'success' => true,
            'count' => $list->count(),
            'data' => $list,
        ], 200);
    }

    /**
     * Supervisor: Verifikasi Status Daily Report (approved / hold / rejected)
     */
    public function updateStatusDailyReport(Request $request, int|string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,hold,rejected,verified',
            'notes' => 'nullable|string|max:500',
        ]);

        $report = DailyReport::find($id);
        if (! $report) {
            return response()->json(['success' => false, 'message' => 'Laporan harian tidak ditemukan!'], 404);
        }

        $spvId = $request->user()?->id ?: 1;
        $status = $request->status === 'verified' ? 'approved' : $request->status;

        $report->update([
            'status' => $status,
            'approved_by' => $spvId,
            'notes' => $request->notes ?: $report->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Status laporan kas harian berhasil diperbarui menjadi: {$status}!",
            'data' => [
                'id' => $report->id,
                'status' => $report->status,
            ],
        ], 200);
    }
}
