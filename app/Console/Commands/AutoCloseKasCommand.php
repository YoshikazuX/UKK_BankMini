<?php

namespace App\Console\Commands;

use App\Helpers\AuditHelper;
use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoCloseKasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kas:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tutup kas harian otomatis akhir hari untuk teller yang belum melapor (hanya jika kas seimbang/tanpa selisih).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();
        $this->info("Memulai proses Auto-Close Kas untuk tanggal {$today}...");

        $tellers = User::where('role', 'teller')->get();
        if ($tellers->isEmpty()) {
            $this->warn('Tidak ada pengguna dengan peran Teller yang ditemukan.');

            return self::SUCCESS;
        }

        $closedCount = 0;
        $skippedCount = 0;

        foreach ($tellers as $teller) {
            $existingReport = DailyReport::where('user_id', $teller->id)
                ->whereDate('report_date', $today)
                ->first();

            if ($existingReport) {
                if ($existingReport->status === 'approved') {
                    $this->line("Teller [{$teller->nama_petugas}] sudah ditutup dan disahkan sebelumnya.");

                    continue;
                }

                // Jika teller sudah lapor tapi ada selisih, JANGAN auto close!
                if ($existingReport->difference != 0) {
                    $this->warn("Tutup Kas Ditolak: Teller [{$teller->nama_petugas}] memiliki selisih kas Rp ".number_format($existingReport->difference, 0, ',', '.').'. Wajib rekonsiliasi manual bersama Supervisor!');
                    $skippedCount++;

                    continue;
                }

                // Jika sudah lapor dan seimbang tapi masih status pending, sahkan otomatis
                $existingReport->update([
                    'status' => 'approved',
                    'notes' => trim(($existingReport->notes ? $existingReport->notes.' | ' : '').'Disahkan otomatis oleh sistem akhir hari (Auto-Close).'),
                ]);

                AuditHelper::log(
                    jenisAktivitas: 'AUTO_CLOSE_APPROVE',
                    modul: 'Daily Report',
                    objekId: "#REP-{$existingReport->id}",
                    oldValues: ['status' => 'pending'],
                    newValues: ['status' => 'approved']
                );

                $this->info("Berhasil mengesahkan Daily Report [#REP-{$existingReport->id}] milik Teller [{$teller->nama_petugas}].");
                $closedCount++;

                continue;
            }

            // Teller belum melapor sama sekali
            // Cek apakah ada pengajuan kas yang masih pending
            $hasPendingDrop = PengajuanKas::where('user_id', $teller->id)
                ->whereDate('tanggal', $today)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingDrop) {
                $this->warn("Tutup Kas Ditolak: Teller [{$teller->nama_petugas}] masih memiliki pengajuan kas yang belum diproses oleh Supervisor!");
                $skippedCount++;

                continue;
            }

            // Hitung transaksi teller hari ini (hanya yang statusnya sukses)
            $trxHariIni = Transaksi::where('petugas_id', $teller->id)
                ->whereDate('created_at', $today)
                ->where('status', 'sukses')
                ->get();

            $totalDeposit = (int) $trxHariIni->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->sum('nominal');
            $totalWithdrawal = (int) $trxHariIni->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->sum('nominal');

            $totalCashDrop = (int) PengajuanKas::where('user_id', $teller->id)
                ->whereDate('tanggal', $today)
                ->where('status', 'disetujui')
                ->sum('nominal');

            $brankasToday = Brankas::whereDate('tanggal', $today)->first();
            $openingBalance = $brankasToday ? (int) $brankasToday->saldo_awal : 0;
            $systemBalance = $openingBalance + $totalDeposit + $totalCashDrop - $totalWithdrawal;

            // Jika ada transaksi hari ini atau brankas buka
            DB::beginTransaction();
            try {
                $report = DailyReport::create([
                    'user_id' => $teller->id,
                    'report_date' => $today,
                    'opening_balance' => $openingBalance,
                    'total_deposit' => $totalDeposit,
                    'total_cash_drop' => $totalCashDrop,
                    'total_withdrawal' => $totalWithdrawal,
                    'system_balance' => $systemBalance,
                    'physical_balance' => $systemBalance, // Kas fisik dianggap seimbang
                    'difference' => 0,
                    'status' => 'approved',
                    'notes' => 'Tutup kas otomatis akhir hari oleh sistem (Auto-Close). Kas dinyatakan seimbang.',
                ]);

                Transaksi::where('petugas_id', $teller->id)
                    ->whereDate('created_at', $today)
                    ->update(['daily_report_id' => $report->id]);

                AuditHelper::log(
                    jenisAktivitas: 'AUTO_CLOSE_CREATE',
                    modul: 'Daily Report',
                    objekId: "#REP-{$report->id}",
                    oldValues: null,
                    newValues: [
                        'system_balance' => $systemBalance,
                        'physical_balance' => $systemBalance,
                        'difference' => 0,
                        'status' => 'approved',
                    ]
                );

                DB::commit();
                $this->info("Berhasil membuat Tutup Kas Otomatis [#REP-{$report->id}] untuk Teller [{$teller->nama_petugas}] dengan saldo Rp ".number_format($systemBalance, 0, ',', '.').'.');
                $closedCount++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("Gagal melakukan auto-close untuk Teller [{$teller->nama_petugas}]: ".$e->getMessage());
                $skippedCount++;
            }
        }

        $this->info("Selesai. Total kas ditutup/disahkan: {$closedCount}, Dilewati (ada selisih/pending): {$skippedCount}.");

        return self::SUCCESS;
    }
}
