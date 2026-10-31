<?php

namespace App\Http\Controllers;

use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Jurnal;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    /**
     * Tampilan Menu Daily Report untuk Teller & Supervisor/Accounting
     */
    public function index(Request $request): View
    {
        $role = strtolower(Auth::user()->role ?? 'teller');
        $today = Carbon::today()->toDateString();
        $user_id = Auth::id();

        if (str_contains($role, 'supervisor') || str_contains($role, 'accounting') || str_contains($role, 'admin')) {
            // Dashboard Supervisor / Accounting: Daftar Review Daily Report dari Seluruh Teller
            $query = DailyReport::with(['user', 'approver']);

            if ($request->filled('tanggal')) {
                $query->whereDate('report_date', $request->tanggal);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $reports = $query->orderBy('report_date', 'desc')->orderBy('id', 'desc')->get();

            // Transformasikan nama teller dan nama approver untuk kompatibilitas view
            $reports->each(function ($r) {
                $r->nama_teller = $r->user->nama_petugas ?? '-';
                $r->nama_approver = $r->approver->nama_petugas ?? '-';
            });

            return view('supervisor.daily-report', compact('reports', 'role'));
        }

        // Tampilan Teller: Buat Laporan Harian Hari Ini & Riwayat Laporan Sendiri
        $trxHariIni = Transaksi::where('petugas_id', $user_id)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalDeposit = (int) $trxHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalWithdrawal = (int) $trxHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        // Droping kas yang telah disetujui untuk teller ini hari ini
        $totalCashDrop = (int) PengajuanKas::where('user_id', $user_id)
            ->whereDate('tanggal', $today)
            ->where('status', 'disetujui')
            ->sum('nominal');

        // Saldo Awal Brankas / Laci Kas
        $brankasToday = Brankas::getOrCreateToday($today);
        $openingBalance = $brankasToday ? (int) $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit + $totalCashDrop - $totalWithdrawal;

        // Cek apakah teller sudah submit daily report hari ini
        $myReportToday = DailyReport::where('user_id', $user_id)
            ->whereDate('report_date', $today)
            ->first();

        // Riwayat Daily Report Teller
        $myReports = DailyReport::with('approver')
            ->where('user_id', $user_id)
            ->orderBy('report_date', 'desc')
            ->get();

        $myReports->each(function ($r) {
            $r->nama_approver = $r->approver->nama_petugas ?? '-';
        });

        return view('teller.daily-report', compact(
            'openingBalance',
            'totalDeposit',
            'totalWithdrawal',
            'totalCashDrop',
            'systemBalance',
            'myReportToday',
            'myReports',
            'trxHariIni'
        ));
    }

    /**
     * Teller Submit Daily Report (Tutup Kas Harian)
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'physical_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $today = Carbon::today()->toDateString();
        $user_id = Auth::id();

        // Cek transaksi teller hari ini
        $trxHariIni = Transaksi::where('petugas_id', $user_id)
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalDeposit = (int) $trxHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
        $totalWithdrawal = (int) $trxHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

        // Droping kas yang telah disetujui untuk teller ini hari ini
        $totalCashDrop = (int) PengajuanKas::where('user_id', $user_id)
            ->whereDate('tanggal', $today)
            ->where('status', 'disetujui')
            ->sum('nominal');

        $brankasToday = Brankas::getOrCreateToday($today);
        $openingBalance = $brankasToday ? (int) $brankasToday->saldo_awal : 0;
        $systemBalance = $openingBalance + $totalDeposit + $totalCashDrop - $totalWithdrawal;

        $physicalBalance = (int) $request->physical_balance;
        $difference = $physicalBalance - $systemBalance;

        // Aturan SOP Rekonsiliasi: Jika ada selisih, keterangan/alasan wajib diisi
        if ($difference !== 0 && empty(trim($request->notes ?? ''))) {
            $formattedDiff = number_format(abs($difference), 0, ',', '.');
            $tipeSelisih = $difference > 0 ? 'Surplus' : 'Defisit';

            return back()->withInput()->with('error', "Rekonsiliasi Kas: Terdeteksi selisih kas fisik sebesar Rp {$formattedDiff} ({$tipeSelisih}). Teller wajib memberikan penjelasan alasan selisih pada kolom catatan sebelum laporan dapat dikirimkan!");
        }

        DB::beginTransaction();
        try {
            $existing = DailyReport::where('user_id', $user_id)
                ->whereDate('report_date', $today)
                ->first();

            if ($existing) {
                // Update jika status masih pending/hold
                if ($existing->status === 'approved') {
                    return back()->with('error', 'Laporan hari ini sudah disetujui (Approved) oleh Supervisor dan tidak dapat diubah.');
                }

                $existing->update([
                    'opening_balance' => $openingBalance,
                    'total_deposit' => $totalDeposit,
                    'total_cash_drop' => $totalCashDrop,
                    'total_withdrawal' => $totalWithdrawal,
                    'system_balance' => $systemBalance,
                    'physical_balance' => $physicalBalance,
                    'difference' => $difference,
                    'notes' => $request->notes,
                    'status' => 'pending',
                ]);
                $reportId = $existing->id;
            } else {
                $report = DailyReport::create([
                    'user_id' => $user_id,
                    'report_date' => $today,
                    'opening_balance' => $openingBalance,
                    'total_deposit' => $totalDeposit,
                    'total_cash_drop' => $totalCashDrop,
                    'total_withdrawal' => $totalWithdrawal,
                    'system_balance' => $systemBalance,
                    'physical_balance' => $physicalBalance,
                    'difference' => $difference,
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);
                $reportId = $report->id;
            }

            // Hubungkan seluruh transaksi teller hari ini ke daily_report_id
            Transaksi::where('petugas_id', $user_id)
                ->whereDate('created_at', $today)
                ->update(['daily_report_id' => $reportId]);

            DB::commit();

            $pesan = ($difference === 0)
                ? 'Daily Report Tutup Kas berhasil dikirim! Kas fisik SEIMBANG (Selisih Rp 0).'
                : 'Daily Report Tutup Kas berhasil dikirim! Terdeteksi selisih kas fisik sebesar Rp '.number_format(abs($difference), 0, ',', '.').' ('.($difference > 0 ? 'Surplus' : 'Defisit').'). Menunggu pemeriksaan Supervisor.';

            return back()->with('success', $pesan);

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan Daily Report: '.$e->getMessage());
        }
    }

    /**
     * Supervisor / Accounting Memeriksa & Mengesahkan Laporan (Review Satu per Satu)
     */
    public function updateStatus(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:approved,hold,rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        $report = DailyReport::find($id);
        if (! $report) {
            return back()->with('error', 'Data Daily Report tidak ditemukan!');
        }

        DB::beginTransaction();
        try {
            $report->update([
                'status' => $request->status,
                'approved_by' => Auth::id(),
                'notes' => $request->notes ?? $report->notes,
            ]);

            // Jika Disetujui (Approved), memicu pembukuan pemindahan kas loket ke Brankas Utama
            if ($request->status === 'approved') {
                $teller = User::find($report->user_id);
                $tellerName = $teller ? $teller->nama_petugas : 'Teller';
                $spvName = Auth::user()->nama_petugas ?? 'Supervisor';

                // 1. Otorisasi seluruh transaksi loket terkait menjadi status 'sukses'
                Transaksi::where('daily_report_id', $id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'sukses',
                    ]);

                // 2. Perintah Pembukuan Pemindahan Kas Laci Teller ke Brankas Utama Bank Mini
                $nominalTransfer = $report->physical_balance > 0 ? $report->physical_balance : $report->system_balance;
                Jurnal::create([
                    'transaksi_id' => null,
                    'keterangan' => "OTORISASI PEMINDAHAN KAS [Daily Report #{$report->id}]: Pemindahan Fisik Kas Loket Teller ({$tellerName}) ke Brankas Utama Bank Mini (Divalidasi oleh {$spvName})",
                    'debit' => $nominalTransfer,
                    'kredit' => $nominalTransfer,
                    'tanggal' => now()->toDateString(),
                ]);
            }

            DB::commit();

            $statusText = match ($request->status) {
                'approved' => 'DISETUJUI (Approved) & Pembukuan Pemindahan Kas ke Brankas Utama Berhasil Dicatat',
                'hold' => 'DITAHAN / PENDING (Hold - Laporan ditangguhkan untuk investigasi selisih kas)',
                'rejected' => 'DITOLAK (Perlu revisi perhitungan kas fisik teller)',
            };

            return back()->with('success', "Daily Report #{$id} berhasil diproses: {$statusText}.");

        } catch (Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses otorisasi Daily Report: '.$e->getMessage());
        }
    }

    /**
     * Cetak Lembar Daily Report Resmi (Siap Cetak / Print)
     */
    public function cetak(int|string $id): View|RedirectResponse
    {
        $report = DailyReport::with(['user', 'approver'])->find($id);

        if (! $report) {
            return back()->with('error', 'Laporan tidak ditemukan!');
        }

        $report->nama_teller = $report->user->nama_petugas ?? '-';
        $report->nama_approver = $report->approver->nama_petugas ?? '-';

        // Ambil rincian transaksi terkait daily report ini
        $transaksi = Transaksi::with('nasabah')
            ->where('daily_report_id', $id)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('supervisor.cetak-daily-report', compact('report', 'transaksi'));
    }
}
