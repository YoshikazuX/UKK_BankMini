<?php

namespace App\Services;

use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\PasswordResetRequest;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardWidgetService
{
    /**
     * Ringkasan Total Nasabah (Konsisten untuk Admin, Teller, dan Supervisor).
     *
     * @return array{total: int, aktif: int, nonaktif: int}
     */
    public function getNasabahSummary(): array
    {
        $total = Nasabah::count();
        $aktif = Nasabah::where('status', 'aktif')->count();
        $nonaktif = Nasabah::where('status', '!=', 'aktif')->count();

        return [
            'total' => $total,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
        ];
    }

    /**
     * Ringkasan Total Pegawai Aktif & Breakdown Per Role (Konsisten lintas role).
     *
     * @return array{
     *     total_aktif: int,
     *     total_semua: int,
     *     nonaktif: int,
     *     roles: array<string, int>,
     *     role_labels: array<string, string>
     * }
     */
    public function getPegawaiSummary(): array
    {
        $allUsers = User::all();
        $activeUsers = $allUsers->filter(fn (User $user) => ($user->status ?? 'aktif') === 'aktif');

        $roles = [
            'teller' => $activeUsers->where('role', 'teller')->count(),
            'supervisor' => $activeUsers->where('role', 'supervisor')->count(),
            'admin' => $activeUsers->where('role', 'admin')->count(),
            'customer_service' => $activeUsers->where('role', 'customer_service')->count(),
            'accounting' => $activeUsers->where('role', 'accounting')->count(),
            'avalis' => $activeUsers->where('role', 'avalis')->count(),
        ];

        $roleLabels = [
            'teller' => 'Teller',
            'supervisor' => 'Supervisor',
            'admin' => 'Admin',
            'customer_service' => 'Customer Service',
            'accounting' => 'Accounting',
            'avalis' => 'Avalis',
        ];

        return [
            'total_aktif' => $activeUsers->count(),
            'total_semua' => $allUsers->count(),
            'nonaktif' => $allUsers->count() - $activeUsers->count(),
            'roles' => $roles,
            'role_labels' => $roleLabels,
        ];
    }

    /**
     * Ringkasan Permohonan Reset Password (Khusus Admin).
     *
     * @return array{
     *     pending: int,
     *     processing: int,
     *     completed: int,
     *     rejected: int,
     *     total: int,
     *     latest: array<string, mixed>|null
     * }
     */
    public function getPasswordResetSummary(): array
    {
        $pending = PasswordResetRequest::where('status', 'pending')->count();
        $processing = PasswordResetRequest::where('status', 'processing')->count();
        $completed = PasswordResetRequest::where('status', 'completed')->count();
        $rejected = PasswordResetRequest::where('status', 'rejected')->count();

        $latestModel = PasswordResetRequest::orderBy('id', 'desc')->first();
        $latest = null;

        if ($latestModel) {
            $latest = [
                'id' => $latestModel->id,
                'username' => $latestModel->username,
                'nama_pemohon' => $latestModel->nama_pemohon ?? '-',
                'status' => $latestModel->status,
                'metode' => $latestModel->metode,
                'created_at' => $latestModel->created_at,
                'time_ago' => $latestModel->created_at ? $latestModel->created_at->diffForHumans() : '-',
            ];
        }

        return [
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'rejected' => $rejected,
            'total' => $pending + $processing + $completed + $rejected,
            'latest' => $latest,
        ];
    }

    /**
     * Peringatan Sistem / Health Check (Admin).
     *
     * @return array{
     *     status: 'normal'|'warning'|'critical',
     *     status_label: string,
     *     db_connected: bool,
     *     db_latency_ms: float,
     *     storage_writable: bool,
     *     storage_free_space: string,
     *     is_balanced: bool,
     *     total_void: int,
     *     total_error_logs: int,
     *     messages: list<string>
     * }
     */
    public function getSystemHealthSummary(): array
    {
        $messages = [];
        $status = 'normal';

        // 1. Cek Koneksi Database & Latensi
        $dbConnected = false;
        $dbLatency = 0.0;
        try {
            $startTime = microtime(true);
            DB::connection()->getPdo();
            $dbLatency = round((microtime(true) - $startTime) * 1000, 2);
            $dbConnected = true;
        } catch (Throwable $e) {
            $status = 'critical';
            $messages[] = 'Koneksi database bermasalah: '.$e->getMessage();
        }

        // 2. Cek Filesystem & Storage
        $storagePath = storage_path('app');
        $storageWritable = is_writable($storagePath);
        $freeBytes = @disk_free_space($storagePath);
        $storageFreeFormatted = $freeBytes !== false ? $this->formatBytes($freeBytes) : 'Tersedia';

        if (! $storageWritable) {
            $status = 'critical';
            $messages[] = 'Direktori storage tidak dapat ditulis (permissions issue).';
        }

        // 3. Cek Keseimbangan Finansial Jurnal vs Mutasi Hari Ini
        $hariIni = Carbon::today();
        $mutasiNabung = Transaksi::whereDate('created_at', $hariIni)
            ->where('jenis_transaksi', 'setor')
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $mutasiTarik = Transaksi::whereDate('created_at', $hariIni)
            ->whereIn('jenis_transaksi', ['tarik', 'admin'])
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $jurnalDebitNasabah = Jurnal::whereDate('tanggal', $hariIni)->whereNotNull('transaksi_id')->sum('debit');
        $jurnalKreditNasabah = Jurnal::whereDate('tanggal', $hariIni)->whereNotNull('transaksi_id')->sum('kredit');

        $totalDropingKas = PengajuanKas::whereDate('tanggal', $hariIni)->where('status', 'disetujui')->sum('nominal');
        $jurnalDropingKas = Jurnal::whereDate('tanggal', $hariIni)
            ->whereNull('transaksi_id')
            ->where('keterangan', 'LIKE', '%DROPING KAS%')
            ->sum('debit');

        $isBalanced = ($jurnalDebitNasabah == $mutasiNabung)
            && ($jurnalKreditNasabah == $mutasiTarik)
            && ($jurnalDropingKas == $totalDropingKas);

        if (! $isBalanced) {
            if ($status !== 'critical') {
                $status = 'warning';
            }
            $messages[] = 'Ketidakseimbangan finansial terdeteksi antara Jurnal dan Mutasi Nasabah/Kas.';
        }

        // 4. Log Error & Transaksi Void Hari Ini
        $totalVoid = Transaksi::whereDate('updated_at', $hariIni)->where('status', 'dibatalkan')->count();
        if ($totalVoid > 5 && $status === 'normal') {
            $status = 'warning';
            $messages[] = "Terdapat {$totalVoid} transaksi void hari ini yang memerlukan peninjauan.";
        }

        $totalErrorLogs = 0; // Dapat diperluas dari log reader jika diperlukan

        $statusLabel = match ($status) {
            'critical' => 'Kritis / Gangguan',
            'warning' => 'Perhatian Diperlukan',
            default => 'Normal & Sehat',
        };

        return [
            'status' => $status,
            'status_label' => $statusLabel,
            'db_connected' => $dbConnected,
            'db_latency_ms' => $dbLatency,
            'storage_writable' => $storageWritable,
            'storage_free_space' => $storageFreeFormatted,
            'is_balanced' => $isBalanced,
            'total_void' => $totalVoid,
            'total_error_logs' => $totalErrorLogs,
            'messages' => $messages,
        ];
    }

    /**
     * Aktivitas Terbaru untuk Teller yang Sedang Login.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getTellerRecentActivities(int $userId, int $limit = 5): Collection
    {
        $activities = collect();

        // 1. Transaksi oleh teller ini
        $transaksis = Transaksi::with('nasabah')
            ->where('petugas_id', $userId)
            ->latest('created_at')
            ->take($limit)
            ->get();

        foreach ($transaksis as $trx) {
            $namaNasabah = $trx->nasabah->nama_siswa ?? 'Nasabah';
            $isVoid = $trx->status === 'dibatalkan';

            $activities->push([
                'type' => 'transaksi',
                'title' => ($trx->jenis_transaksi === 'setor' ? 'Setoran' : 'Penarikan').' Rp '.number_format($trx->nominal, 0, ',', '.'),
                'subtitle' => $namaNasabah.' ('.($trx->nasabah->no_rekening ?? '-').')',
                'status_badge' => $isVoid ? 'Dibatalkan (Void)' : 'Berhasil',
                'status_color' => $isVoid ? 'rose' : 'emerald',
                'timestamp' => $trx->created_at,
                'time_ago' => $trx->created_at ? $trx->created_at->diffForHumans() : '-',
                'url' => url('/teller/riwayat'),
                'icon' => $trx->jenis_transaksi === 'setor' ? 'arrow-down-circle' : 'arrow-up-circle',
            ]);
        }

        // 2. Pengajuan Kas oleh teller ini
        $pengajuans = PengajuanKas::where('user_id', $userId)
            ->latest('created_at')
            ->take($limit)
            ->get();

        foreach ($pengajuans as $pk) {
            $color = match ($pk->status) {
                'disetujui' => 'emerald',
                'ditolak' => 'rose',
                default => 'amber',
            };

            $statusText = match ($pk->status) {
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                default => 'Menunggu Approval',
            };

            $activities->push([
                'type' => 'pengajuan_kas',
                'title' => 'Pengajuan Kas Rp '.number_format($pk->nominal, 0, ',', '.'),
                'subtitle' => 'No: '.$pk->nomor_pengajuan.' - '.($pk->alasan ?? 'Droping kas operasional'),
                'status_badge' => $statusText,
                'status_color' => $color,
                'timestamp' => $pk->created_at,
                'time_ago' => $pk->created_at ? $pk->created_at->diffForHumans() : '-',
                'url' => url('/teller/pengajuan-kas'),
                'icon' => 'cash',
            ]);
        }

        // 3. Daily Report oleh teller ini
        $dailyReports = DailyReport::where('user_id', $userId)
            ->latest('report_date')
            ->take($limit)
            ->get();

        foreach ($dailyReports as $dr) {
            $color = match ($dr->status) {
                'verified' => 'emerald',
                'rejected' => 'rose',
                'hold' => 'amber',
                default => 'blue',
            };

            $statusText = match ($dr->status) {
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak',
                'hold' => 'Perlu Revisi (Hold)',
                default => 'Menunggu Verifikasi',
            };

            $activities->push([
                'type' => 'daily_report',
                'title' => 'Daily Report Tutup Kas',
                'subtitle' => 'Tgl: '.Carbon::parse($dr->report_date)->format('d/m/Y').' | Fisik: Rp '.number_format($dr->physical_balance, 0, ',', '.'),
                'status_badge' => $statusText,
                'status_color' => $color,
                'timestamp' => $dr->updated_at ?? Carbon::parse($dr->report_date),
                'time_ago' => $dr->updated_at ? $dr->updated_at->diffForHumans() : '-',
                'url' => url('/teller/daily-report'),
                'icon' => 'document-report',
            ]);
        }

        // Sort descending by timestamp and limit
        return $activities->sortByDesc('timestamp')->take($limit)->values();
    }

    /**
     * Peringatan Sistem Operasional untuk Teller.
     *
     * @return array{
     *     status: 'normal'|'info'|'warning'|'critical',
     *     status_badge: string,
     *     kas_alert: array{level: string, message: string, detail: string, nominal: int},
     *     closing_alert: array{level: string, message: string, detail: string, url: string},
     *     pending_kas_alert: array{level: string, message: string, count: int, url: string},
     *     is_ready_for_service: bool
     * }
     */
    public function getTellerOperationalAlerts(User $teller): array
    {
        $today = Carbon::today()->toDateString();
        $brankasHariIni = Brankas::getOrCreateToday($today);
        $kasTersedia = $brankasHariIni ? (int) $brankasHariIni->total : 0;

        // 1. Kas Laci Loket Alert
        if ($kasTersedia <= 0) {
            $kasAlert = [
                'level' => 'critical',
                'message' => 'Kas Loket Habis (Rp 0)',
                'detail' => 'Segera ajukan droping kas ke Supervisor untuk dapat melayani penarikan.',
                'nominal' => $kasTersedia,
            ];
        } elseif ($kasTersedia < 100000) {
            $kasAlert = [
                'level' => 'warning',
                'message' => 'Kas Loket Menipis',
                'detail' => 'Saldo kas fisik tersisa Rp '.number_format($kasTersedia, 0, ',', '.').'. Pertimbangkan mengajukan kas tambahan.',
                'nominal' => $kasTersedia,
            ];
        } else {
            $kasAlert = [
                'level' => 'normal',
                'message' => 'Likuiditas Kas Aman',
                'detail' => 'Tersedia Rp '.number_format($kasTersedia, 0, ',', '.').' di loket kasir.',
                'nominal' => $kasTersedia,
            ];
        }

        // 2. Status Closing Daily Report Hari Ini
        $reportHariIni = DailyReport::where('user_id', $teller->id)
            ->whereDate('report_date', $today)
            ->first();

        $currentHour = (int) Carbon::now()->format('H');
        $isSore = $currentHour >= 14;

        if ($reportHariIni) {
            if ($reportHariIni->status === 'hold') {
                $closingAlert = [
                    'level' => 'warning',
                    'message' => 'Laporan Butuh Revisi (Hold)',
                    'detail' => 'Supervisor meminta perbaikan pada laporan: '.($reportHariIni->supervisor_notes ?? 'Cek fisik kas.'),
                    'url' => url('/teller/daily-report'),
                ];
            } elseif ($reportHariIni->status === 'rejected') {
                $closingAlert = [
                    'level' => 'critical',
                    'message' => 'Laporan Tutup Kas Ditolak',
                    'detail' => 'Laporan ditolak oleh Supervisor: '.($reportHariIni->supervisor_notes ?? 'Silakan buat ulang.'),
                    'url' => url('/teller/daily-report'),
                ];
            } elseif ($reportHariIni->status === 'verified') {
                $closingAlert = [
                    'level' => 'normal',
                    'message' => 'Tutup Kas Terverifikasi',
                    'detail' => 'Laporan tutup kas hari ini telah disahkan oleh Supervisor.',
                    'url' => url('/teller/daily-report'),
                ];
            } else {
                $closingAlert = [
                    'level' => 'info',
                    'message' => 'Tutup Kas Menunggu Verifikasi',
                    'detail' => 'Laporan telah dikirimkan ke Supervisor dan sedang dalam antrean review.',
                    'url' => url('/teller/daily-report'),
                ];
            }
        } else {
            if ($isSore) {
                $closingAlert = [
                    'level' => 'warning',
                    'message' => 'Belum Tutup Kas Hari Ini',
                    'detail' => 'Waktu operasional hampir selesai. Segera hitung fisik kas dan submit daily report.',
                    'url' => url('/teller/daily-report'),
                ];
            } else {
                $closingAlert = [
                    'level' => 'normal',
                    'message' => 'Loket Sedang Buka',
                    'detail' => 'Lakukan tutup kas di akhir jam operasional.',
                    'url' => url('/teller/daily-report'),
                ];
            }
        }

        // 3. Status Pengajuan Kas Loket Pending
        $pendingKasCount = PengajuanKas::where('user_id', $teller->id)
            ->where('status', 'pending')
            ->count();

        $pendingKasAlert = [
            'level' => $pendingKasCount > 0 ? 'info' : 'normal',
            'message' => $pendingKasCount > 0 ? "{$pendingKasCount} Pengajuan Kas Pending" : 'Tidak Ada Pengajuan Pending',
            'count' => $pendingKasCount,
            'url' => url('/teller/pengajuan-kas'),
        ];

        // Overall status
        $status = 'normal';
        if ($kasAlert['level'] === 'critical' || $closingAlert['level'] === 'critical') {
            $status = 'critical';
        } elseif ($kasAlert['level'] === 'warning' || $closingAlert['level'] === 'warning') {
            $status = 'warning';
        } elseif ($closingAlert['level'] === 'info' || $pendingKasAlert['level'] === 'info') {
            $status = 'info';
        }

        $statusBadge = match ($status) {
            'critical' => 'Perhatian Kritis',
            'warning' => 'Perlu Tindakan',
            'info' => 'Ada Antrean',
            default => 'Operasional Lancar',
        };

        return [
            'status' => $status,
            'status_badge' => $statusBadge,
            'kas_alert' => $kasAlert,
            'closing_alert' => $closingAlert,
            'pending_kas_alert' => $pendingKasAlert,
            'is_ready_for_service' => $kasTersedia > 0,
        ];
    }

    /**
     * Aktivitas Terbaru Lintas Seluruh Teller untuk Supervisor (Monitoring & Checker).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getSupervisorRecentActivities(int $limit = 6): Collection
    {
        $activities = collect();

        // 1. Transaksi seluruh teller
        $transaksis = Transaksi::with(['nasabah', 'petugas'])
            ->latest('created_at')
            ->take($limit)
            ->get();

        foreach ($transaksis as $trx) {
            $petugasNama = $trx->petugas->nama_petugas ?? 'Teller';
            $nasabahNama = $trx->nasabah->nama_siswa ?? 'Nasabah';
            $isVoid = $trx->status === 'dibatalkan';

            $activities->push([
                'type' => 'transaksi',
                'title' => ($trx->jenis_transaksi === 'setor' ? 'Setoran' : 'Penarikan').' Rp '.number_format($trx->nominal, 0, ',', '.'),
                'subtitle' => "Teller: {$petugasNama} &bull; Nasabah: {$nasabahNama}",
                'is_actionable' => false,
                'status_badge' => $isVoid ? 'Dibatalkan (Void)' : 'Selesai',
                'status_color' => $isVoid ? 'rose' : 'slate',
                'timestamp' => $trx->created_at,
                'time_ago' => $trx->created_at ? $trx->created_at->diffForHumans() : '-',
                'url' => route('supervisor.monitoring-teller'),
                'petugas' => $petugasNama,
            ]);
        }

        // 2. Pengajuan droping kas
        $pengajuans = PengajuanKas::with('user')
            ->latest('created_at')
            ->take($limit)
            ->get();

        foreach ($pengajuans as $pk) {
            $petugasNama = $pk->user->nama_petugas ?? 'Teller';
            $isPending = $pk->status === 'pending';

            $statusBadge = match ($pk->status) {
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                default => 'Butuh Persetujuan',
            };

            $color = match ($pk->status) {
                'disetujui' => 'emerald',
                'ditolak' => 'rose',
                default => 'amber',
            };

            $activities->push([
                'type' => 'pengajuan_kas',
                'title' => 'Pengajuan Kas Rp '.number_format($pk->nominal, 0, ',', '.'),
                'subtitle' => "Teller: {$petugasNama} &bull; {$pk->nomor_pengajuan}",
                'is_actionable' => $isPending,
                'status_badge' => $statusBadge,
                'status_color' => $color,
                'timestamp' => $pk->created_at,
                'time_ago' => $pk->created_at ? $pk->created_at->diffForHumans() : '-',
                'url' => $isPending ? route('supervisor.pengajuan-kas.detail', $pk->id) : route('supervisor.pengajuan-kas'),
                'petugas' => $petugasNama,
            ]);
        }

        // 3. Daily report teller
        $dailyReports = DailyReport::with('user')
            ->latest('report_date')
            ->take($limit)
            ->get();

        foreach ($dailyReports as $dr) {
            $petugasNama = $dr->user->nama_petugas ?? 'Teller';
            $isNeedAction = in_array($dr->status, ['pending', 'hold']);

            $statusBadge = match ($dr->status) {
                'verified' => 'Terverifikasi',
                'rejected' => 'Ditolak',
                'hold' => 'Revisi (Hold)',
                default => 'Butuh Verifikasi',
            };

            $color = match ($dr->status) {
                'verified' => 'emerald',
                'rejected' => 'rose',
                'hold' => 'amber',
                default => 'blue',
            };

            $activities->push([
                'type' => 'daily_report',
                'title' => 'Tutup Kas Daily Report',
                'subtitle' => "Teller: {$petugasNama} &bull; Selisih: Rp ".number_format($dr->difference, 0, ',', '.'),
                'is_actionable' => $isNeedAction,
                'status_badge' => $statusBadge,
                'status_color' => $color,
                'timestamp' => $dr->updated_at ?? Carbon::parse($dr->report_date),
                'time_ago' => $dr->updated_at ? $dr->updated_at->diffForHumans() : '-',
                'url' => route('supervisor.daily-report.detail', $dr->id),
                'petugas' => $petugasNama,
            ]);
        }

        return $activities->sortByDesc('timestamp')->take($limit)->values();
    }

    /**
     * Peringatan Sistem Operasional untuk Supervisor (Checker Priority).
     *
     * @return array{
     *     priority: 'normal'|'pending'|'warning'|'critical',
     *     priority_label: string,
     *     pending_droping_count: int,
     *     pending_daily_report_count: int,
     *     discrepancy_count: int,
     *     unclosed_teller_count: int,
     *     alerts: list<array{type: string, title: string, count: int, level: string, url: string}>
     * }
     */
    public function getSupervisorOperationalAlerts(): array
    {
        $today = Carbon::today()->toDateString();
        $alerts = [];

        // 1. Pengajuan kas pending
        $pendingDropingCount = PengajuanKas::where('status', 'pending')->count();
        if ($pendingDropingCount > 0) {
            $alerts[] = [
                'type' => 'pengajuan_kas',
                'title' => 'Pengajuan Droping Kas Menunggu Otorisasi',
                'count' => $pendingDropingCount,
                'level' => 'pending',
                'url' => route('supervisor.pengajuan-kas'),
            ];
        }

        // 2. Daily report butuh verifikasi (pending & hold)
        $pendingDailyReportCount = DailyReport::whereIn('status', ['pending', 'hold'])->count();
        if ($pendingDailyReportCount > 0) {
            $alerts[] = [
                'type' => 'daily_report',
                'title' => 'Daily Report Menunggu Verifikasi / Revisi',
                'count' => $pendingDailyReportCount,
                'level' => 'pending',
                'url' => route('supervisor.daily-report'),
            ];
        }

        // 3. Selisih kas fisik vs sistem (hari ini atau belum verified)
        $discrepancyCount = DailyReport::whereDate('report_date', $today)
            ->where('difference', '!=', 0)
            ->count();

        if ($discrepancyCount > 0) {
            $alerts[] = [
                'type' => 'discrepancy',
                'title' => 'Laporan Rekonsiliasi dengan Selisih Kas (Defisit/Surplus)',
                'count' => $discrepancyCount,
                'level' => 'critical',
                'url' => route('supervisor.rekonsiliasi'),
            ];
        }

        // 4. Teller aktif yang belum melakukan closing hari ini (hanya relevan jika sore hari)
        $currentHour = (int) Carbon::now()->format('H');
        $isAfternoon = $currentHour >= 14;

        $unclosedTellerCount = 0;
        if ($isAfternoon) {
            $activeTellerIds = User::where('role', 'teller')
                ->where('status', 'aktif')
                ->pluck('id');

            $closedTellerIds = DailyReport::whereDate('report_date', $today)
                ->whereIn('user_id', $activeTellerIds)
                ->pluck('user_id');

            $unclosedTellerCount = $activeTellerIds->diff($closedTellerIds)->count();

            if ($unclosedTellerCount > 0) {
                $alerts[] = [
                    'type' => 'unclosed_teller',
                    'title' => 'Teller Aktif Belum Melakukan Tutup Kas Sore Ini',
                    'count' => $unclosedTellerCount,
                    'level' => 'warning',
                    'url' => route('supervisor.monitoring-teller'),
                ];
            }
        }

        // Priority calculation
        $priority = 'normal';
        if ($discrepancyCount > 0) {
            $priority = 'critical';
        } elseif ($pendingDropingCount > 0 || $pendingDailyReportCount > 0) {
            $priority = 'pending';
        } elseif ($unclosedTellerCount > 0) {
            $priority = 'warning';
        }

        $priorityLabel = match ($priority) {
            'critical' => 'Kritis (Ada Selisih)',
            'pending' => 'Menunggu Otorisasi',
            'warning' => 'Perlu Perhatian',
            default => 'Operasional Bersih',
        };

        return [
            'priority' => $priority,
            'priority_label' => $priorityLabel,
            'pending_droping_count' => $pendingDropingCount,
            'pending_daily_report_count' => $pendingDailyReportCount,
            'discrepancy_count' => $discrepancyCount,
            'unclosed_teller_count' => $unclosedTellerCount,
            'alerts' => $alerts,
        ];
    }

    /**
     * Helper format byte size.
     */
    protected function formatBytes(float|int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision).' '.$units[$pow];
    }
}
