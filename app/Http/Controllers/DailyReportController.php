<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\NotificationHelper;
use App\Models\ApprovalHistory;
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
            if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
                $query->whereBetween('report_date', [$request->dari_tanggal, $request->sampai_tanggal]);
            } elseif ($request->filled('dari_tanggal')) {
                $query->whereDate('report_date', '>=', $request->dari_tanggal);
            } elseif ($request->filled('sampai_tanggal')) {
                $query->whereDate('report_date', '<=', $request->sampai_tanggal);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('teller_id')) {
                $query->where('user_id', $request->teller_id);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('nama_petugas', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            }

            $reports = $query->orderBy('report_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

            // Transformasikan nama teller dan nama approver untuk kompatibilitas view
            $reports->each(function ($r) {
                $r->nama_teller = $r->user->nama_petugas ?? '-';
                $r->nama_approver = $r->approver->nama_petugas ?? '-';
            });

            $tellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();

            return view('supervisor.daily-report', compact('reports', 'role', 'tellers'));
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

            $isRevision = false;

            if ($existing) {
                // Update jika status masih pending/hold/rejected
                if ($existing->status === 'approved') {
                    return back()->with('error', 'Laporan hari ini sudah disetujui (Approved) oleh Supervisor dan tidak dapat diubah.');
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

            // Audit log
            AuditHelper::log(
                jenisAktivitas: $isRevision ? 'REVISI_DAILY_REPORT' : 'KIRIM_DAILY_REPORT',
                modul: 'Daily Report',
                objekId: "#REP-{$reportId}",
                oldValues: $existing ? ['status' => $existing->status, 'physical_balance' => $existing->physical_balance] : null,
                newValues: [
                    'system_balance' => $systemBalance,
                    'physical_balance' => $physicalBalance,
                    'difference' => $difference,
                ]
            );

            // Notifikasi ke seluruh Supervisor
            $namaTeller = Auth::user()->nama_petugas ?? 'Teller Loket';
            $notifTitle = $isRevision ? 'Daily Report Telah Diperbaiki' : 'Daily Report Baru Menunggu Verifikasi';
            $notifMsg = $isRevision
                ? "Teller {$namaTeller} telah memperbaiki dan mengirim ulang Daily Report (#REP-".str_pad($reportId, 4, '0', STR_PAD_LEFT).'). Siap diverifikasi.'
                : "Teller {$namaTeller} mengirimkan Daily Report Tutup Kas (#REP-".str_pad($reportId, 4, '0', STR_PAD_LEFT).') dengan Kas Fisik Rp '.number_format($physicalBalance, 0, ',', '.').'.';

            NotificationHelper::notifySupervisors([
                'task_type' => 'daily_report',
                'task_id' => $reportId,
                'nomor_referensi' => '#REP-'.str_pad($reportId, 4, '0', STR_PAD_LEFT),
                'nama_teller' => $namaTeller,
                'title' => $notifTitle,
                'message' => $notifMsg,
                'nominal' => $physicalBalance,
                'status' => 'pending',
                'action_url' => url('/supervisor/daily-report/'.$reportId),
            ]);

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
     * Detail Daily Report & Rekonsiliasi Kas untuk Pemeriksaan Mendalam Supervisor
     */
    public function show(Request $request, int|string $id): View
    {
        $report = DailyReport::with(['user', 'approver', 'histories.supervisor'])->findOrFail($id);

        // Ambil rincian transaksi terkait daily report ini
        $trxQuery = Transaksi::with(['nasabah', 'petugas'])
            ->where(function ($q) use ($report) {
                $q->where('daily_report_id', $report->id)
                    ->orWhere(function ($sub) use ($report) {
                        $sub->where('petugas_id', $report->user_id)
                            ->whereDate('created_at', $report->report_date);
                    });
            });

        // Filter dalam transaksi pendukung
        if ($request->filled('trx_jenis')) {
            $trxQuery->where('jenis_transaksi', $request->trx_jenis);
        }
        if ($request->filled('trx_status')) {
            $trxQuery->where('status', $request->trx_status);
        }

        $transaksi = $trxQuery->orderBy('created_at', 'asc')->get();

        // Statistik transaksi pendukung
        $transaksiSuksesCount = $transaksi->where('status', '!=', 'dibatalkan')->count();
        $transaksiBatalCount = $transaksi->where('status', 'dibatalkan')->count();
        $totalSetoranTrx = $transaksi->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->where('status', '!=', 'dibatalkan')->sum('nominal');
        $totalPenarikanTrx = $transaksi->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->where('status', '!=', 'dibatalkan')->sum('nominal');

        // Droping kas terkait teller ini pada tanggal tersebut
        $dropingKasList = PengajuanKas::where('user_id', $report->user_id)
            ->whereDate('tanggal', $report->report_date)
            ->get();

        // Kondisi rekonsiliasi kas
        $selisih = $report->difference;
        $statusRekonsiliasi = $selisih == 0 ? 'sesuai' : ($selisih > 0 ? 'surplus' : 'defisit');

        // Tandai notifikasi pekerjaan sebagai terbaca
        NotificationHelper::markTaskAsRead(Auth::user(), 'daily_report', $report->id);

        return view('supervisor.detail-daily-report', compact(
            'report',
            'transaksi',
            'transaksiSuksesCount',
            'transaksiBatalCount',
            'totalSetoranTrx',
            'totalPenarikanTrx',
            'dropingKasList',
            'statusRekonsiliasi'
        ));
    }

    /**
     * Supervisor / Accounting Memeriksa & Mengesahkan Laporan (Review Satu per Satu)
     * Dilengkapi Proteksi Row Locking & Idempotensi Mencegah Double Decision
     */
    public function updateStatus(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:approved,verified,hold,rejected',
            'notes' => 'nullable|string|max:500',
            'alasan_penolakan' => 'nullable|string|max:500',
        ]);

        $targetStatus = $request->status === 'verified' ? 'approved' : $request->status;
        $notes = trim($request->notes ?? $request->alasan_penolakan ?? '');

        // Validasi wajib isi alasan jika status ditolak atau diminta klarifikasi (hold)
        if (in_array($targetStatus, ['hold', 'rejected']) && empty($notes)) {
            $tindakan = $targetStatus === 'hold' ? 'permintaan klarifikasi / perbaikan (Hold)' : 'penolakan';
            $pesanError = "Catatan penjelasan wajib diisi untuk tindakan {$tindakan} Daily Report agar Teller mengetahui perbaikan yang diperlukan!";

            return back()->withInput()
                ->withErrors(['notes' => $pesanError])
                ->with('error', $pesanError);
        }

        DB::beginTransaction();
        try {
            // Row-level lock untuk konsistensi status
            $report = DailyReport::where('id', $id)->lockForUpdate()->first();
            if (! $report) {
                DB::rollBack();

                return back()->with('error', 'Data Daily Report tidak ditemukan!');
            }

            // Cegah duplikasi persetujuan jika sudah approved
            if ($report->status === 'approved' && $targetStatus === 'approved') {
                DB::rollBack();

                return back()->with('error', "Laporan Daily Report #REP-{$id} sudah disahkan (Approved) sebelumnya dan tidak dapat disetujui ulang.");
            }

            $statusSebelum = $report->status;
            $teller = User::find($report->user_id);
            $tellerName = $teller ? $teller->nama_petugas : 'Teller';
            $spvName = Auth::user()->nama_petugas ?? 'Supervisor';

            $updateData = [
                'status' => $targetStatus,
                'approved_by' => Auth::id(),
                'catatan_spv' => $notes ?: $report->catatan_spv,
            ];
            if ($targetStatus === 'rejected') {
                $updateData['alasan_penolakan'] = $notes;
            }
            $report->update($updateData);

            // Jika Disetujui (Approved), memicu pembukuan pemindahan kas loket ke Brankas Utama
            if ($targetStatus === 'approved') {
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

            // Catat jejak keputusan ke approval_histories
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
                namaTeller: $tellerName,
                jenisKeputusan: $jenisKeputusan,
                statusSebelum: $statusSebelum,
                statusSesudah: $targetStatus,
                nominal: $report->physical_balance ?: $report->system_balance,
                catatan: $notes,
                alasanPenolakan: $targetStatus === 'rejected' ? $notes : null
            );

            // Audit log
            AuditHelper::log(
                jenisAktivitas: strtoupper($jenisKeputusan).'_DAILY_REPORT',
                modul: 'Pusat Persetujuan',
                objekId: '#REP-'.str_pad($report->id, 4, '0', STR_PAD_LEFT),
                oldValues: ['status' => $statusSebelum],
                newValues: [
                    'status' => $targetStatus,
                    'approved_by' => Auth::id(),
                    'catatan_spv' => $notes,
                ]
            );

            // Kirim notifikasi hasil verifikasi ke Teller
            $pesanNotif = match ($targetStatus) {
                'approved' => "Daily Report #REP-{$report->id} tanggal {$report->report_date->format('d/m/Y')} telah DISETUJUI & diverifikasi oleh {$spvName}.",
                'hold' => "Daily Report #REP-{$report->id} ditangguhkan (Hold) oleh {$spvName}: {$notes}. Mohon periksa kembali fisik kas.",
                'rejected' => "Daily Report #REP-{$report->id} DITOLAK oleh {$spvName}. Alasan: {$notes}.",
            };

            NotificationHelper::notifyUser($report->user_id, [
                'task_type' => 'daily_report',
                'task_id' => $report->id,
                'nomor_referensi' => '#REP-'.str_pad($report->id, 4, '0', STR_PAD_LEFT),
                'nama_teller' => $tellerName,
                'title' => 'Status Daily Report Diperbarui',
                'message' => $pesanNotif,
                'nominal' => $report->physical_balance ?: $report->system_balance,
                'status' => $targetStatus,
                'action_url' => url('/teller/daily-report'),
            ]);

            // Tandai notifikasi pekerjaan sebagai terbaca
            NotificationHelper::markTaskAsRead(Auth::user(), 'daily_report', $report->id);

            DB::commit();

            $statusText = match ($targetStatus) {
                'approved' => 'DISETUJUI (Approved) & Pembukuan Pemindahan Kas ke Brankas Utama Berhasil Dicatat',
                'hold' => 'DITAHAN / PENDING (Hold - Laporan ditangguhkan untuk klarifikasi/investigasi selisih kas)',
                'rejected' => 'DITOLAK (Perlu revisi perhitungan kas fisik teller)',
            };

            return redirect()->route('supervisor.daily-report.detail', $report->id)
                ->with('success', 'Daily Report #REP-'.str_pad($report->id, 4, '0', STR_PAD_LEFT)." berhasil diproses: {$statusText}.");

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
            ->where(function ($q) use ($report) {
                $q->where('daily_report_id', $report->id)
                    ->orWhere(function ($sub) use ($report) {
                        $sub->where('petugas_id', $report->user_id)
                            ->whereDate('created_at', $report->report_date);
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return view('supervisor.cetak-daily-report', compact('report', 'transaksi'));
    }
}
