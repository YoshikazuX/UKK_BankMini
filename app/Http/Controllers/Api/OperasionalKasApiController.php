<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AuditHelper;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\ApprovalHistory;
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
                'alasan_penolakan' => $p->alasan_penolakan,
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

        $tellerName = $user ? $user->nama_petugas : 'Teller';

        // Log Audit
        AuditHelper::log(
            jenisAktivitas: 'AJUKAN_DROPING_KAS_API',
            modul: 'Operasional Kas',
            objekId: $nomorPengajuan,
            oldValues: null,
            newValues: [
                'nomor_pengajuan' => $nomorPengajuan,
                'nominal' => (int) $validated['nominal'],
            ],
            user: $user
        );

        // Notifikasi ke seluruh Supervisor
        NotificationHelper::notifySupervisors([
            'task_type' => 'pengajuan_kas',
            'task_id' => $pengajuan->id,
            'nomor_referensi' => $nomorPengajuan,
            'nama_teller' => $tellerName,
            'title' => 'Pengajuan Droping Kas Baru (API)',
            'message' => "Teller {$tellerName} mengajukan tambahan kas Rp ".number_format($pengajuan->nominal, 0, ',', '.').'.',
            'nominal' => $pengajuan->nominal,
            'status' => 'pending',
            'action_url' => url('/supervisor/pengajuan-kas/'.$pengajuan->id),
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
        $totalCashDrop = (float) PengajuanKas::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->where('status', 'disetujui')
            ->sum('nominal');

        $brankasToday = Brankas::getOrCreateToday($today);
        $openingBalance = $brankasToday ? (float) $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit + $totalCashDrop - $totalWithdrawal;

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
                'total_cash_drop' => $totalCashDrop,
                'formatted_cash_drop' => 'Rp '.number_format($totalCashDrop, 0, ',', '.'),
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
                    'catatan_spv' => $myReportToday->catatan_spv,
                    'approver' => $myReportToday->approver?->nama_petugas,
                ] : null,
            ],
        ], 200);
    }

    /**
     * Teller Submit / Kirim Daily Report Tutup Kas
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
        $totalCashDrop = (int) PengajuanKas::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->where('status', 'disetujui')
            ->sum('nominal');

        $brankasToday = Brankas::getOrCreateToday($today);
        $openingBalance = $brankasToday ? (int) $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit + $totalCashDrop - $totalWithdrawal;

        $physicalBalance = (int) $validated['physical_balance'];
        $difference = $physicalBalance - $systemBalance;

        // Validasi selisih
        if ($difference !== 0 && empty(trim($validated['notes'] ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Rekonsiliasi Kas: Terdeteksi selisih kas fisik. Teller wajib menyertakan keterangan alasan selisih pada kolom catatan!',
                'difference' => $difference,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $existing = DailyReport::where('user_id', $userId)
                ->whereDate('report_date', $today)
                ->first();

            $isRevision = false;
            if ($existing) {
                if ($existing->status === 'approved') {
                    return response()->json(['success' => false, 'message' => 'Laporan hari ini sudah disetujui (Approved) dan tidak dapat diubah.'], 422);
                }
                $isRevision = in_array($existing->status, ['hold', 'rejected']);
                $existing->update([
                    'opening_balance' => $openingBalance,
                    'total_deposit' => $totalDeposit,
                    'total_cash_drop' => $totalCashDrop,
                    'total_withdrawal' => $totalWithdrawal,
                    'system_balance' => $systemBalance,
                    'physical_balance' => $physicalBalance,
                    'difference' => $difference,
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending',
                ]);
                $report = $existing;
            } else {
                $report = DailyReport::create([
                    'user_id' => $userId,
                    'report_date' => $today,
                    'opening_balance' => $openingBalance,
                    'total_deposit' => $totalDeposit,
                    'total_cash_drop' => $totalCashDrop,
                    'total_withdrawal' => $totalWithdrawal,
                    'system_balance' => $systemBalance,
                    'physical_balance' => $physicalBalance,
                    'difference' => $difference,
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                ]);
            }

            Transaksi::where('petugas_id', $userId)
                ->whereDate('created_at', $today)
                ->update(['daily_report_id' => $report->id]);

            $tellerName = $user ? $user->nama_petugas : 'Teller Loket';

            // Notifikasi ke seluruh Supervisor
            NotificationHelper::notifySupervisors([
                'task_type' => 'daily_report',
                'task_id' => $report->id,
                'nomor_referensi' => '#REP-'.str_pad($report->id, 4, '0', STR_PAD_LEFT),
                'nama_teller' => $tellerName,
                'title' => $isRevision ? 'Daily Report Telah Diperbaiki (API)' : 'Daily Report Baru Menunggu Verifikasi (API)',
                'message' => "Teller {$tellerName} mengirimkan Daily Report Tutup Kas.",
                'nominal' => $physicalBalance,
                'status' => 'pending',
                'action_url' => url('/supervisor/daily-report/'.$report->id),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Daily Report Tutup Kas berhasil dikirim! Menunggu verifikasi Supervisor.',
                'data' => [
                    'id' => $report->id,
                    'report_date' => $today,
                    'system_balance' => (float) $systemBalance,
                    'physical_balance' => (float) $physicalBalance,
                    'difference' => (float) $difference,
                    'status' => $report->status,
                ],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan Daily Report: '.$e->getMessage()], 500);
        }
    }

    // =========================================================================
    // 3. MODUL SUPERVISOR: PUSAT PERSETUJUAN & APPROVAL
    // =========================================================================

    /**
     * Supervisor: Pusat Persetujuan Terpadu (Approval Center API)
     */
    public function approvalCenterSupervisor(Request $request): JsonResponse
    {
        $kategori = $request->query('kategori', 'semua');
        $jenis = $request->query('jenis', 'semua');
        $tellerId = $request->query('teller_id');

        $queryPengajuan = PengajuanKas::with(['user', 'approver']);
        if ($kategori === 'menunggu_persetujuan') {
            $queryPengajuan->where('status', 'pending');
        } elseif ($kategori === 'telah_disetujui') {
            $queryPengajuan->where('status', 'disetujui');
        } elseif ($kategori === 'ditolak') {
            $queryPengajuan->where('status', 'ditolak');
        } elseif ($kategori === 'menunggu_verifikasi' || $kategori === 'memerlukan_klarifikasi') {
            $queryPengajuan->whereRaw('1 = 0');
        }
        if ($tellerId) {
            $queryPengajuan->where('user_id', $tellerId);
        }

        $pengajuanList = ($jenis === 'daily_report') ? collect() : $queryPengajuan->get()->map(fn ($p) => [
            'id' => $p->id,
            'pekerjaan_type' => 'pengajuan_kas',
            'nomor_referensi' => $p->nomor_pengajuan,
            'nama_teller' => $p->user?->nama_petugas ?? '-',
            'tanggal' => $p->tanggal?->format('Y-m-d'),
            'nominal' => (float) $p->nominal,
            'status' => $p->status,
            'alasan' => $p->alasan,
            'catatan_spv' => $p->catatan_spv,
            'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
        ]);

        $queryReport = DailyReport::with(['user', 'approver']);
        if ($kategori === 'menunggu_verifikasi') {
            $queryReport->whereIn('status', ['pending', 'hold']);
        } elseif ($kategori === 'telah_disetujui') {
            $queryReport->where('status', 'approved');
        } elseif ($kategori === 'ditolak') {
            $queryReport->where('status', 'rejected');
        } elseif ($kategori === 'memerlukan_klarifikasi') {
            $queryReport->where('status', 'hold');
        } elseif ($kategori === 'menunggu_persetujuan') {
            $queryReport->whereRaw('1 = 0');
        }
        if ($tellerId) {
            $queryReport->where('user_id', $tellerId);
        }

        $reportList = ($jenis === 'pengajuan_kas') ? collect() : $queryReport->get()->map(fn ($r) => [
            'id' => $r->id,
            'pekerjaan_type' => 'daily_report',
            'nomor_referensi' => '#REP-'.str_pad($r->id, 4, '0', STR_PAD_LEFT),
            'nama_teller' => $r->user?->nama_petugas ?? '-',
            'tanggal' => $r->report_date?->format('Y-m-d'),
            'nominal' => (float) ($r->physical_balance ?: $r->system_balance),
            'difference' => (float) $r->difference,
            'status' => $r->status,
            'notes' => $r->notes,
            'catatan_spv' => $r->catatan_spv,
            'created_at' => $r->created_at?->format('Y-m-d H:i:s'),
        ]);

        $all = $pengajuanList->concat($reportList)->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_pending' => PengajuanKas::where('status', 'pending')->count() + DailyReport::whereIn('status', ['pending', 'hold'])->count(),
                'pengajuan_pending' => PengajuanKas::where('status', 'pending')->count(),
                'daily_report_pending' => DailyReport::whereIn('status', ['pending', 'hold'])->count(),
                'total_selesai' => PengajuanKas::whereIn('status', ['disetujui', 'ditolak'])->count() + DailyReport::whereIn('status', ['approved', 'rejected'])->count(),
            ],
            'count' => $all->count(),
            'data' => $all,
        ], 200);
    }

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
            'alasan_penolakan' => $p->alasan_penolakan,
            'created_at' => $p->created_at?->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'count' => $list->count(),
            'data' => $list,
        ], 200);
    }

    /**
     * Supervisor: Detail Pengajuan Kas Sebelum Otorisasi
     */
    public function detailPengajuanKasSupervisor(int|string $id): JsonResponse
    {
        $pengajuan = PengajuanKas::with(['user', 'approver', 'histories.supervisor'])->find($id);
        if (! $pengajuan) {
            return response()->json(['success' => false, 'message' => 'Pengajuan kas tidak ditemukan!'], 404);
        }

        $tgl = $pengajuan->tanggal ? $pengajuan->tanggal->toDateString() : Carbon::today()->toDateString();
        $brankas = Brankas::where('tanggal', $tgl)->first();

        $trxTeller = Transaksi::where('petugas_id', $pengajuan->user_id)
            ->whereDate('created_at', $tgl)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalSetor = (float) $trxTeller->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalTarik = (float) $trxTeller->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        return response()->json([
            'success' => true,
            'data' => [
                'pengajuan' => [
                    'id' => $pengajuan->id,
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'tanggal' => $pengajuan->tanggal?->format('Y-m-d'),
                    'nominal' => (float) $pengajuan->nominal,
                    'alasan' => $pengajuan->alasan,
                    'status' => $pengajuan->status,
                    'catatan_spv' => $pengajuan->catatan_spv,
                    'alasan_penolakan' => $pengajuan->alasan_penolakan,
                    'nama_teller' => $pengajuan->user?->nama_petugas,
                    'kelas_teller' => $pengajuan->user?->kelas,
                ],
                'posisi_kas' => [
                    'kas_awal' => $brankas ? (float) $brankas->saldo_awal : 0,
                    'kas_tersedia_loket' => $brankas ? (float) $brankas->total : 0,
                    'total_setoran_masuk' => $totalSetor,
                    'total_penarikan_keluar' => $totalTarik,
                ],
                'riwayat_keputusan' => $pengajuan->histories->map(fn ($h) => [
                    'keputusan' => $h->jenis_keputusan,
                    'supervisor' => $h->nama_supervisor,
                    'waktu' => $h->created_at?->format('Y-m-d H:i:s'),
                    'catatan' => $h->catatan,
                ]),
            ],
        ], 200);
    }

    /**
     * Supervisor: Setujui Tiket Droping Kas
     */
    public function setujuiPengajuanKas(Request $request, int|string $id): JsonResponse
    {
        $today = Carbon::today()->toDateString();
        $spvUser = $request->user();
        $spvId = $spvUser?->id ?: 1;
        $spvName = $spvUser?->nama_petugas ?? 'Supervisor';

        DB::beginTransaction();
        try {
            // Row-level lock untuk mencegah race condition double approval
            $pengajuan = PengajuanKas::where('id', $id)->lockForUpdate()->first();
            if (! $pengajuan) {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => 'Data pengajuan kas tidak ditemukan!'], 404);
            }

            if ($pengajuan->status !== 'pending') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => "Pencegahan Dobel Persetujuan: Pengajuan kas #{$pengajuan->nomor_pengajuan} sudah diproses sebelumnya dengan status [{$pengajuan->status}].",
                ], 422);
            }

            $catatan = $request->catatan_spv ?? 'Disetujui untuk pemenuhan likuiditas loket';
            $pengajuan->update([
                'status' => 'disetujui',
                'disetujui_oleh' => $spvId,
                'catatan_spv' => $catatan,
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
                'keterangan' => "PENERIMAAN DROPING KAS OPERASIONAL [{$pengajuan->nomor_pengajuan}] - {$namaTeller} (Divalidasi oleh {$spvName})",
                'debit' => $pengajuan->nominal,
                'kredit' => 0,
                'tanggal' => $today,
            ]);

            // Rekam jejak keputusan
            ApprovalHistory::record(
                pekerjaanType: 'pengajuan_kas',
                pekerjaanId: $pengajuan->id,
                nomorReferensi: $pengajuan->nomor_pengajuan,
                tellerId: $pengajuan->user_id,
                namaTeller: $namaTeller,
                jenisKeputusan: 'disetujui',
                statusSebelum: 'pending',
                statusSesudah: 'disetujui',
                nominal: $pengajuan->nominal,
                catatan: $catatan,
                supervisor: $spvUser
            );

            // Kirim notifikasi ke Teller
            NotificationHelper::notifyUser($pengajuan->user_id, [
                'task_type' => 'pengajuan_kas',
                'task_id' => $pengajuan->id,
                'nomor_referensi' => $pengajuan->nomor_pengajuan,
                'nama_teller' => $namaTeller,
                'title' => 'Pengajuan Kas Disetujui (API)',
                'message' => "Pengajuan tambahan kas #{$pengajuan->nomor_pengajuan} disetujui oleh {$spvName}.",
                'nominal' => $pengajuan->nominal,
                'status' => 'disetujui',
                'action_url' => url('/teller/pengajuan-kas'),
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
        $alasan = trim($request->alasan_penolakan ?? $request->catatan_spv ?? '');
        if (empty($alasan) || strlen($alasan) < 5) {
            return response()->json(['success' => false, 'message' => 'Alasan penolakan wajib diisi minimal 5 karakter!'], 422);
        }

        $spvUser = $request->user();
        $spvId = $spvUser?->id ?: 1;
        $spvName = $spvUser?->nama_petugas ?? 'Supervisor';

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanKas::where('id', $id)->lockForUpdate()->first();
            if (! $pengajuan) {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => 'Data pengajuan kas tidak ditemukan!'], 404);
            }

            if ($pengajuan->status !== 'pending') {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => "Pengajuan kas ini sudah diproses sebelumnya ({$pengajuan->status})."], 422);
            }

            $pengajuan->update([
                'status' => 'ditolak',
                'disetujui_oleh' => $spvId,
                'catatan_spv' => $alasan,
                'alasan_penolakan' => $alasan,
            ]);

            $namaTeller = $pengajuan->user?->nama_petugas ?? 'Teller';

            // Catat ke approval_histories
            ApprovalHistory::record(
                pekerjaanType: 'pengajuan_kas',
                pekerjaanId: $pengajuan->id,
                nomorReferensi: $pengajuan->nomor_pengajuan,
                tellerId: $pengajuan->user_id,
                namaTeller: $namaTeller,
                jenisKeputusan: 'ditolak',
                statusSebelum: 'pending',
                statusSesudah: 'ditolak',
                nominal: $pengajuan->nominal,
                catatan: $alasan,
                alasanPenolakan: $alasan,
                supervisor: $spvUser
            );

            NotificationHelper::notifyUser($pengajuan->user_id, [
                'task_type' => 'pengajuan_kas',
                'task_id' => $pengajuan->id,
                'nomor_referensi' => $pengajuan->nomor_pengajuan,
                'nama_teller' => $namaTeller,
                'title' => 'Pengajuan Kas Ditolak (API)',
                'message' => "Pengajuan kas #{$pengajuan->nomor_pengajuan} DITOLAK oleh {$spvName}. Alasan: {$alasan}",
                'nominal' => $pengajuan->nominal,
                'status' => 'ditolak',
                'action_url' => url('/teller/pengajuan-kas'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Pengajuan kas #{$pengajuan->nomor_pengajuan} telah ditolak.",
                'data' => [
                    'id' => $pengajuan->id,
                    'nomor_pengajuan' => $pengajuan->nomor_pengajuan,
                    'status' => 'ditolak',
                    'alasan_penolakan' => $alasan,
                ],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal menolak pengajuan kas: '.$e->getMessage()], 500);
        }
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
            'nomor_referensi' => '#REP-'.str_pad($r->id, 4, '0', STR_PAD_LEFT),
            'report_date' => $r->report_date ? $r->report_date->format('Y-m-d') : null,
            'nama_teller' => $r->user?->nama_petugas ?? '-',
            'kelas_teller' => $r->user?->kelas ?? '-',
            'opening_balance' => (float) $r->opening_balance,
            'total_deposit' => (float) $r->total_deposit,
            'total_withdrawal' => (float) $r->total_withdrawal,
            'total_cash_drop' => (float) $r->total_cash_drop,
            'system_balance' => (float) $r->system_balance,
            'physical_balance' => (float) $r->physical_balance,
            'difference' => (float) $r->difference,
            'status' => $r->status,
            'notes' => $r->notes,
            'catatan_spv' => $r->catatan_spv,
            'verified_by' => $r->approver?->nama_petugas ?? '-',
        ]);

        return response()->json([
            'success' => true,
            'count' => $list->count(),
            'data' => $list,
        ], 200);
    }

    /**
     * Supervisor: Detail Daily Report dan Transaksi Pendukung
     */
    public function detailDailyReportSupervisor(int|string $id): JsonResponse
    {
        $report = DailyReport::with(['user', 'approver', 'histories.supervisor'])->find($id);
        if (! $report) {
            return response()->json(['success' => false, 'message' => 'Daily Report tidak ditemukan!'], 404);
        }

        $transaksi = Transaksi::with(['nasabah', 'petugas'])
            ->where(function ($q) use ($report) {
                $q->where('daily_report_id', $report->id)
                    ->orWhere(function ($sub) use ($report) {
                        $sub->where('petugas_id', $report->user_id)
                            ->whereDate('created_at', $report->report_date);
                    });
            })
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'created_at' => $t->created_at?->format('Y-m-d H:i:s'),
                'nama_nasabah' => $t->nama_siswa ?? $t->nasabah?->nama_siswa,
                'nis' => $t->nis ?? $t->nasabah?->nis,
                'jenis_transaksi' => $t->jenis_transaksi,
                'nominal' => (float) $t->nominal,
                'status' => $t->status,
                'ref_transaksi_id' => $t->ref_transaksi_id,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'report' => [
                    'id' => $report->id,
                    'nomor_referensi' => '#REP-'.str_pad($report->id, 4, '0', STR_PAD_LEFT),
                    'report_date' => $report->report_date?->format('Y-m-d'),
                    'nama_teller' => $report->user?->nama_petugas,
                    'opening_balance' => (float) $report->opening_balance,
                    'total_deposit' => (float) $report->total_deposit,
                    'total_withdrawal' => (float) $report->total_withdrawal,
                    'total_cash_drop' => (float) $report->total_cash_drop,
                    'system_balance' => (float) $report->system_balance,
                    'physical_balance' => (float) $report->physical_balance,
                    'difference' => (float) $report->difference,
                    'status' => $report->status,
                    'notes' => $report->notes,
                    'catatan_spv' => $report->catatan_spv,
                    'alasan_penolakan' => $report->alasan_penolakan,
                ],
                'transaksi_pendukung' => $transaksi,
                'riwayat_keputusan' => $report->histories->map(fn ($h) => [
                    'keputusan' => $h->jenis_keputusan,
                    'supervisor' => $h->nama_supervisor,
                    'waktu' => $h->created_at?->format('Y-m-d H:i:s'),
                    'catatan' => $h->catatan,
                ]),
            ],
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
            'alasan_penolakan' => 'nullable|string|max:500',
        ]);

        $spvUser = $request->user();
        $spvId = $spvUser?->id ?: 1;
        $spvName = $spvUser?->nama_petugas ?? 'Supervisor';

        $targetStatus = $request->status === 'verified' ? 'approved' : $request->status;
        $notes = trim($request->notes ?? $request->alasan_penolakan ?? '');

        if (in_array($targetStatus, ['hold', 'rejected']) && empty($notes)) {
            return response()->json(['success' => false, 'message' => 'Catatan alasan klarifikasi / penolakan wajib disertakan!'], 422);
        }

        DB::beginTransaction();
        try {
            $report = DailyReport::where('id', $id)->lockForUpdate()->first();
            if (! $report) {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => 'Laporan harian tidak ditemukan!'], 404);
            }

            if ($report->status === 'approved' && $targetStatus === 'approved') {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => 'Daily Report ini sudah disahkan sebelumnya.'], 422);
            }

            $statusSebelum = $report->status;
            $report->update([
                'status' => $targetStatus,
                'approved_by' => $spvId,
                'catatan_spv' => $notes ?: $report->catatan_spv,
                'alasan_penolakan' => $targetStatus === 'rejected' ? $notes : $report->alasan_penolakan,
            ]);

            if ($targetStatus === 'approved') {
                Transaksi::where('daily_report_id', $id)
                    ->where('status', 'pending')
                    ->update(['status' => 'sukses']);

                $nominalTransfer = $report->physical_balance > 0 ? $report->physical_balance : $report->system_balance;
                $teller = User::find($report->user_id);
                $tellerName = $teller ? $teller->nama_petugas : 'Teller';

                Jurnal::create([
                    'transaksi_id' => null,
                    'keterangan' => "OTORISASI PEMINDAHAN KAS [Daily Report #{$report->id}]: Pemindahan Fisik Kas Loket ({$tellerName}) ke Brankas Utama",
                    'debit' => $nominalTransfer,
                    'kredit' => $nominalTransfer,
                    'tanggal' => now()->toDateString(),
                ]);
            }

            $jenisKeputusan = match ($targetStatus) {
                'approved' => 'disetujui',
                'hold' => 'klarifikasi_hold',
                'rejected' => 'ditolak',
            };

            ApprovalHistory::record(
                pekerjaanType: 'daily_report',
                pekerjaanId: $report->id,
                nomorReferensi: '#REP-'.str_pad($report->id, 4, '0', STR_PAD_LEFT),
                tellerId: $report->user_id,
                namaTeller: $report->user?->nama_petugas ?? 'Teller',
                jenisKeputusan: $jenisKeputusan,
                statusSebelum: $statusSebelum,
                statusSesudah: $targetStatus,
                nominal: $report->physical_balance ?: $report->system_balance,
                catatan: $notes,
                alasanPenolakan: $targetStatus === 'rejected' ? $notes : null,
                supervisor: $spvUser
            );

            NotificationHelper::notifyUser($report->user_id, [
                'task_type' => 'daily_report',
                'task_id' => $report->id,
                'nomor_referensi' => '#REP-'.str_pad($report->id, 4, '0', STR_PAD_LEFT),
                'nama_teller' => $report->user?->nama_petugas ?? 'Teller',
                'title' => 'Daily Report Diperiksa (API)',
                'message' => "Daily Report #REP-{$report->id} diproses dengan status: {$targetStatus}.",
                'nominal' => $report->physical_balance ?: $report->system_balance,
                'status' => $targetStatus,
                'action_url' => url('/teller/daily-report'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Status laporan kas harian berhasil diperbarui menjadi: {$targetStatus}!",
                'data' => [
                    'id' => $report->id,
                    'status' => $report->status,
                ],
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Gagal memperbarui status: '.$e->getMessage()], 500);
        }
    }

    /**
     * Supervisor: Riwayat Persetujuan Terpadu
     */
    public function riwayatApprovalSupervisor(Request $request): JsonResponse
    {
        $query = ApprovalHistory::with(['supervisor:id,nama_petugas', 'teller:id,nama_petugas']);

        if ($request->filled('pekerjaan_type')) {
            $query->where('pekerjaan_type', $request->pekerjaan_type);
        }
        if ($request->filled('jenis_keputusan')) {
            $query->where('jenis_keputusan', $request->jenis_keputusan);
        }
        if ($request->filled('teller_id')) {
            $query->where('teller_id', $request->teller_id);
        }

        $list = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $list,
        ], 200);
    }

    /**
     * Supervisor: Monitoring Produktivitas Teller
     */
    public function monitoringTellerSupervisor(Request $request): JsonResponse
    {
        $dariTanggal = $request->query('dari_tanggal', Carbon::today()->toDateString());
        $sampaiTanggal = $request->query('sampai_tanggal', Carbon::today()->toDateString());

        $tellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();
        $stats = [];

        foreach ($tellers as $t) {
            $trxList = Transaksi::where('petugas_id', $t->id)
                ->whereBetween('created_at', [$dariTanggal.' 00:00:00', $sampaiTanggal.' 23:59:59'])
                ->get();

            $stats[] = [
                'teller_id' => $t->id,
                'nama_teller' => $t->nama_petugas,
                'total_transaksi' => $trxList->where('status', '!=', 'dibatalkan')->count(),
                'transaksi_sukses' => $trxList->where('status', 'sukses')->count(),
                'transaksi_batal' => $trxList->where('status', 'dibatalkan')->count(),
                'total_setoran' => (float) $trxList->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->where('status', '!=', 'dibatalkan')->sum('nominal'),
                'total_penarikan' => (float) $trxList->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->where('status', '!=', 'dibatalkan')->sum('nominal'),
            ];
        }

        return response()->json([
            'success' => true,
            'periode' => ['dari' => $dariTanggal, 'sampai' => $sampaiTanggal],
            'data' => $stats,
        ], 200);
    }

    /**
     * Supervisor: Rekonsiliasi Kas Keseluruhan
     */
    public function rekonsiliasiSupervisor(Request $request): JsonResponse
    {
        $query = DailyReport::with('user:id,nama_petugas');
        if ($request->filled('tanggal')) {
            $query->whereDate('report_date', $request->tanggal);
        }

        $list = $query->orderBy('report_date', 'desc')->get()->map(fn ($r) => [
            'id' => $r->id,
            'report_date' => $r->report_date?->format('Y-m-d'),
            'nama_teller' => $r->user?->nama_petugas,
            'system_balance' => (float) $r->system_balance,
            'physical_balance' => (float) $r->physical_balance,
            'difference' => (float) $r->difference,
            'kondisi' => $r->difference == 0 ? 'sesuai' : ($r->difference > 0 ? 'surplus' : 'defisit'),
            'status' => $r->status,
        ]);

        return response()->json([
            'success' => true,
            'summary' => [
                'total' => $list->count(),
                'seimbang' => $list->where('difference', 0)->count(),
                'surplus' => $list->where('difference', '>', 0)->count(),
                'defisit' => $list->where('difference', '<', 0)->count(),
            ],
            'data' => $list,
        ], 200);
    }

    /**
     * Supervisor: Notifikasi Pekerjaan
     */
    public function notifikasiSupervisor(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notifs = $user->notifications()->take(20)->get()->map(fn ($n) => [
            'id' => $n->id,
            'data' => $n->data,
            'read_at' => $n->read_at?->format('Y-m-d H:i:s'),
            'created_at' => $n->created_at?->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $notifs,
        ], 200);
    }

    /**
     * Supervisor: Tandai Semua Notifikasi Terbaca
     */
    public function bacaSemuaNotifikasiSupervisor(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Seluruh notifikasi telah ditandai sebagai terbaca.',
        ], 200);
    }
}
