<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\TerbilangHelper;

class DailyReportController extends Controller
{
    /**
     * Tampilan Menu Daily Report untuk Teller & Supervisor/Accounting
     */
    public function index(Request $request)
    {
        $role = strtolower(Auth::user()->role ?? 'teller');
        $today = Carbon::today()->toDateString();
        $user_id = Auth::id();

        if (str_contains($role, 'supervisor') || str_contains($role, 'accounting') || str_contains($role, 'admin')) {
            // Dashboard Supervisor / Accounting: Daftar Review Daily Report dari Seluruh Teller
            $query = DB::table('daily_reports')
                ->join('users as teller', 'daily_reports.user_id', '=', 'teller.id')
                ->leftJoin('users as spv', 'daily_reports.approved_by', '=', 'spv.id')
                ->select(
                    'daily_reports.*',
                    'teller.nama_petugas as nama_teller',
                    'spv.nama_petugas as nama_approver'
                );

            if ($request->filled('tanggal')) {
                $query->whereDate('daily_reports.report_date', $request->tanggal);
            }
            if ($request->filled('status')) {
                $query->where('daily_reports.status', $request->status);
            }

            $reports = $query->orderBy('daily_reports.report_date', 'desc')->orderBy('daily_reports.id', 'desc')->get();

            return view('supervisor.daily-report', compact('reports', 'role'));
        }

        // Tampilan Teller: Buat Laporan Harian Hari Ini & Riwayat Laporan Sendiri
        // 1. Hitung total transaksi hari ini yang dibuat oleh teller yang login
        $trxHariIni = DB::table('transaksi')
            ->where('petugas_id', $user_id)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalDeposit = $trxHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalWithdrawal = $trxHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        // Saldo Awal Brankas / Laci Kas
        $brankasToday = DB::table('brankas')->where('tanggal', $today)->first();
        $openingBalance = $brankasToday ? $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit - $totalWithdrawal;

        // Cek apakah teller sudah submit daily report hari ini
        $myReportToday = DB::table('daily_reports')
            ->where('user_id', $user_id)
            ->whereDate('report_date', $today)
            ->first();

        // Riwayat Daily Report Teller
        $myReports = DB::table('daily_reports')
            ->leftJoin('users as spv', 'daily_reports.approved_by', '=', 'spv.id')
            ->select('daily_reports.*', 'spv.nama_petugas as nama_approver')
            ->where('daily_reports.user_id', $user_id)
            ->orderBy('report_date', 'desc')
            ->get();

        return view('teller.daily-report', compact(
            'openingBalance',
            'totalDeposit',
            'totalWithdrawal',
            'systemBalance',
            'myReportToday',
            'myReports',
            'trxHariIni'
        ));
    }

    /**
     * Teller Submit Daily Report (Tutup Kas Harian)
     */
    public function store(Request $request)
    {
        $request->validate([
            'physical_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $today = Carbon::today()->toDateString();
        $user_id = Auth::id();

        // Cek transaksi teller hari ini
        $trxHariIni = DB::table('transaksi')
            ->where('petugas_id', $user_id)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalDeposit = $trxHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalWithdrawal = $trxHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        $brankasToday = DB::table('brankas')->where('tanggal', $today)->first();
        $openingBalance = $brankasToday ? $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit - $totalWithdrawal;

        $physicalBalance = $request->physical_balance;
        $difference = $physicalBalance - $systemBalance;

        DB::beginTransaction();
        try {
            $existing = DB::table('daily_reports')
                ->where('user_id', $user_id)
                ->whereDate('report_date', $today)
                ->first();

            if ($existing) {
                // Update jika status masih pending/hold
                if ($existing->status === 'approved') {
                    return back()->with('error', 'Laporan hari ini sudah disetujui (Approved) oleh Supervisor dan tidak dapat diubah.');
                }

                DB::table('daily_reports')->where('id', $existing->id)->update([
                    'opening_balance' => $openingBalance,
                    'total_deposit' => $totalDeposit,
                    'total_withdrawal' => $totalWithdrawal,
                    'system_balance' => $systemBalance,
                    'physical_balance' => $physicalBalance,
                    'difference' => $difference,
                    'notes' => $request->notes,
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
                $reportId = $existing->id;
            } else {
                $reportId = DB::table('daily_reports')->insertGetId([
                    'user_id' => $user_id,
                    'report_date' => $today,
                    'opening_balance' => $openingBalance,
                    'total_deposit' => $totalDeposit,
                    'total_withdrawal' => $totalWithdrawal,
                    'system_balance' => $systemBalance,
                    'physical_balance' => $physicalBalance,
                    'difference' => $difference,
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Hubungkan seluruh transaksi teller hari ini ke daily_report_id
            DB::table('transaksi')
                ->where('petugas_id', $user_id)
                ->whereDate('created_at', $today)
                ->update(['daily_report_id' => $reportId]);

            DB::commit();

            $pesan = ($difference == 0)
                ? 'Daily Report Tutup Kas berhasil dikirim! Kas fisik SEIMBANG (Selisih Rp 0).'
                : 'Daily Report Tutup Kas berhasil dikirim! Terdeteksi selisih kas fisik sebesar Rp ' . number_format(abs($difference), 0, ',', '.') . ' (' . ($difference > 0 ? 'Surplus' : 'Defisit') . '). Menunggu pemeriksaan Supervisor.';

            return back()->with('success', $pesan);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal menyimpan Daily Report: ' . $e->getMessage());
        }
    }

    /**
     * Supervisor / Accounting Memeriksa & Mengesahkan Laporan (Review Satu per Satu)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,hold,rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        $report = DB::table('daily_reports')->where('id', $id)->first();
        if (!$report) {
            return back()->with('error', 'Data Daily Report tidak ditemukan!');
        }

        DB::beginTransaction();
        try {
            DB::table('daily_reports')->where('id', $id)->update([
                'status' => $request->status,
                'approved_by' => Auth::id(),
                'notes' => $request->notes ?? $report->notes,
                'updated_at' => now(),
            ]);

            // Jika Disetujui (Approved), memicu perintah pembukuan pemindahan tanggung jawab kas ke Brankas Utama
            if ($request->status === 'approved') {
                $teller = DB::table('users')->where('id', $report->user_id)->first();
                $tellerName = $teller ? $teller->nama_petugas : 'Teller';
                $spvName = Auth::user()->nama_petugas ?? 'Supervisor';

                // 1. Otorisasi seluruh transaksi loket terkait menjadi status 'sukses'
                DB::table('transaksi')
                    ->where('daily_report_id', $id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'sukses',
                        'updated_at' => now(),
                    ]);

                // 2. Perintah Pembukuan Pemindahan Kas Laci Teller ke Brankas Utama Bank Mini
                $nominalTransfer = $report->physical_balance > 0 ? $report->physical_balance : $report->system_balance;
                DB::table('jurnal')->insert([
                    'transaksi_id' => null,
                    'keterangan' => "OTORISASI PEMINDAHAN KAS [Daily Report #{$report->id}]: Pemindahan Fisik Kas Loket Teller ({$tellerName}) ke Brankas Utama Bank Mini (Divalidasi oleh {$spvName})",
                    'debit' => $nominalTransfer,
                    'kredit' => $nominalTransfer,
                    'tanggal' => now()->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            $statusText = match($request->status) {
                'approved' => 'DISETUJUI (Approved) & Pembukuan Pemindahan Kas ke Brankas Utama Berhasil Dicatat',
                'hold' => 'DITAHAN / PENDING (Hold - Laporan ditangguhkan untuk investigasi selisih kas)',
                'rejected' => 'DITOLAK (Perlu revisi perhitungan kas fisik teller)',
            };

            return back()->with('success', "Daily Report #{$id} berhasil diproses: {$statusText}.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memproses otorisasi Daily Report: ' . $e->getMessage());
        }
    }

    /**
     * Cetak Lembar Daily Report Resmi (Siap Cetak / Print)
     */
    public function cetak($id)
    {
        $report = DB::table('daily_reports')
            ->join('users as teller', 'daily_reports.user_id', '=', 'teller.id')
            ->leftJoin('users as spv', 'daily_reports.approved_by', '=', 'spv.id')
            ->select(
                'daily_reports.*',
                'teller.nama_petugas as nama_teller',
                'spv.nama_petugas as nama_approver'
            )
            ->where('daily_reports.id', $id)
            ->first();

        if (!$report) {
            return back()->with('error', 'Laporan tidak ditemukan!');
        }

        // Ambil rincian transaksi terkait daily report ini
        $transaksi = DB::table('transaksi')
            ->join('nasabah', 'transaksi.nasabah_id', '=', 'nasabah.id')
            ->select('transaksi.*', 'nasabah.nama_siswa', 'nasabah.no_rekening', 'nasabah.nis')
            ->where('transaksi.daily_report_id', $id)
            ->orderBy('transaksi.created_at', 'asc')
            ->get();

        return view('supervisor.cetak-daily-report', compact('report', 'transaksi'));
    }
}
