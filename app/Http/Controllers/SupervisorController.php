<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Helpers\AvalisSyncHelper;
use App\Models\ApprovalHistory;
use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Nasabah;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\DashboardWidgetService;
use App\Services\ProfileImageService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupervisorController extends Controller
{
    /**
     * Dashboard Utama Pengawasan Operasional Supervisor
     */
    public function index(DashboardWidgetService $widgetService): View
    {
        $today = Carbon::today()->toDateString();

        $nasabahSummary = $widgetService->getNasabahSummary();
        $pegawaiSummary = $widgetService->getPegawaiSummary();
        $supervisorActivities = $widgetService->getSupervisorRecentActivities(6);
        $supervisorAlerts = $widgetService->getSupervisorOperationalAlerts();

        $totalNasabah = $nasabahSummary['total'];
        $totalSaldo = Nasabah::sum('saldo');

        $brankasHariIni = Brankas::whereDate('tanggal', $today)->first();
        $kasTersedia = $brankasHariIni ? $brankasHariIni->total : 0;
        $kasAwal = $brankasHariIni ? $brankasHariIni->saldo_awal : 0;

        // Metrik Pekerjaan Pending
        $pendingPengajuanKasCount = PengajuanKas::where('status', 'pending')->count();
        $pendingDailyReportCount = DailyReport::whereIn('status', ['pending', 'hold'])->count();
        $pendingTransaksiCount = Transaksi::where('status', 'pending')->count();
        $totalPekerjaanPending = $pendingPengajuanKasCount + $pendingDailyReportCount + $pendingTransaksiCount;

        // Ringkasan Presensi Petugas Avalis
        $absensiSummary = AvalisSyncHelper::getDashboardSummary($today);

        // Daftar 5 Pengajuan Kas Pending Terbaru
        $pendingPengajuanKas = PengajuanKas::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Daftar 5 Daily Report Menunggu Verifikasi
        $pendingDailyReports = DailyReport::with('user')
            ->whereIn('status', ['pending', 'hold'])
            ->orderBy('report_date', 'desc')
            ->take(5)
            ->get();

        // Daftar Transaksi Pending (jika ada)
        $transaksiPending = Transaksi::with(['nasabah', 'petugas'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Riwayat 5 Keputusan Terakhir
        $keputusanTerakhir = ApprovalHistory::orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 5 Notifikasi Pekerjaan Terkini
        $user = Auth::user();
        $notifikasiTerbaru = $user ? $user->notifications()->take(5)->get() : collect();

        // Ringkasan Rekonsiliasi Kas Hari Ini
        $reportsHariIni = DailyReport::whereDate('report_date', $today)->get();
        $rekapRekonsiliasiHariIni = [
            'total_laporan' => $reportsHariIni->count(),
            'seimbang' => $reportsHariIni->where('difference', 0)->count(),
            'selisih' => $reportsHariIni->where('difference', '!=', 0)->count(),
            'total_selisih_nominal' => $reportsHariIni->sum('difference'),
        ];

        // GABUNGAN PEKERJAAN MENUNGGU TINDAKAN (PUSAT PERSETUJUAN TABBED)
        $pekerjaanMenunggu = collect();

        foreach ($pendingPengajuanKas as $pk) {
            $pekerjaanMenunggu->push([
                'id' => $pk->id,
                'kategori' => 'kas',
                'kategori_label' => 'Pengajuan Kas',
                'nomor_ref' => $pk->nomor_pengajuan,
                'teller' => $pk->user->nama_petugas ?? 'Teller',
                'teller_kelas' => $pk->user->kelas ?? '-',
                'tanggal' => $pk->created_at,
                'ringkasan' => 'Droping Kas: Rp '.number_format($pk->nominal, 0, ',', '.'),
                'detail' => $pk->alasan ?? 'Droping kas operasional',
                'status' => 'Menunggu Otorisasi',
                'status_color' => 'amber',
                'url_detail' => route('supervisor.pengajuan-kas.detail', $pk->id),
            ]);
        }

        foreach ($pendingDailyReports as $dr) {
            $pekerjaanMenunggu->push([
                'id' => $dr->id,
                'kategori' => 'report',
                'kategori_label' => 'Daily Report',
                'nomor_ref' => 'DR-'.Carbon::parse($dr->report_date)->format('Ymd').'-'.$dr->id,
                'teller' => $dr->user->nama_petugas ?? 'Teller',
                'teller_kelas' => $dr->user->kelas ?? '-',
                'tanggal' => $dr->updated_at ?? Carbon::parse($dr->report_date),
                'ringkasan' => 'Tutup Kas Fisik: Rp '.number_format($dr->physical_balance, 0, ',', '.').($dr->difference != 0 ? ' (Selisih: Rp '.number_format($dr->difference, 0, ',', '.').')' : ''),
                'detail' => $dr->notes ?? ($dr->difference != 0 ? 'Terdapat selisih kas fisik vs sistem' : 'Kas fisik klop dengan sistem'),
                'status' => $dr->status === 'hold' ? 'Perlu Revisi (Hold)' : 'Menunggu Verifikasi',
                'status_color' => $dr->status === 'hold' ? 'amber' : 'blue',
                'url_detail' => route('supervisor.daily-report.detail', $dr->id),
            ]);
        }

        $pekerjaanMenungguTindakan = $pekerjaanMenunggu->sortByDesc('tanggal')->values();

        // GABUNGAN AKTIVITAS DAN JEJAK KEPUTUSAN TERBARU
        $riwayatGabungan = collect();

        foreach ($supervisorActivities as $act) {
            $riwayatGabungan->push([
                'pelaku' => $act['petugas'] ?? 'Petugas Loket',
                'jenis' => $act['title'],
                'keterangan' => $act['subtitle'] ?? '-',
                'waktu' => $act['timestamp'],
                'time_ago' => $act['time_ago'] ?? '-',
                'status' => $act['status_badge'] ?? 'Selesai',
                'status_color' => $act['status_color'] ?? 'slate',
                'is_keputusan' => false,
            ]);
        }

        foreach ($keputusanTerakhir as $kep) {
            $riwayatGabungan->push([
                'pelaku' => $kep->user->nama_petugas ?? 'Supervisor',
                'jenis' => 'Keputusan Otorisasi Checker',
                'keterangan' => $kep->catatan ?? 'Otorisasi pengajuan operasional',
                'waktu' => $kep->created_at,
                'time_ago' => $kep->created_at ? $kep->created_at->diffForHumans() : '-',
                'status' => ucfirst($kep->status),
                'status_color' => $kep->status === 'disetujui' ? 'emerald' : 'rose',
                'is_keputusan' => true,
            ]);
        }

        $riwayatAktivitasDanKeputusan = $riwayatGabungan->sortByDesc('waktu')->take(8)->values();

        return view('supervisor.dashboard', compact(
            'totalNasabah',
            'totalSaldo',
            'kasTersedia',
            'kasAwal',
            'pendingPengajuanKasCount',
            'pendingDailyReportCount',
            'pendingTransaksiCount',
            'totalPekerjaanPending',
            'absensiSummary',
            'pendingPengajuanKas',
            'pendingDailyReports',
            'transaksiPending',
            'keputusanTerakhir',
            'notifikasiTerbaru',
            'rekapRekonsiliasiHariIni',
            'nasabahSummary',
            'pegawaiSummary',
            'supervisorActivities',
            'supervisorAlerts',
            'pekerjaanMenungguTindakan',
            'riwayatAktivitasDanKeputusan'
        ));
    }

    /**
     * FITUR 1: PUSAT PERSETUJUAN (APPROVAL CENTER)
     * Tempat terpusat untuk seluruh pekerjaan yang membutuhkan tindakan Supervisor.
     */
    public function approvalCenter(Request $request): View
    {
        $kategori = $request->query('kategori', 'semua');
        $jenisPekerjaan = $request->query('jenis', 'semua');
        $tellerId = $request->query('teller_id');
        $search = $request->query('search');
        $dariTanggal = $request->query('dari_tanggal');
        $sampaiTanggal = $request->query('sampai_tanggal');

        // 1. Ambil Data Pengajuan Kas
        $queryPengajuan = PengajuanKas::with(['user', 'approver']);

        if ($kategori === 'menunggu_persetujuan') {
            $queryPengajuan->where('status', 'pending');
        } elseif ($kategori === 'menunggu_verifikasi') {
            $queryPengajuan->whereRaw('1 = 0'); // Pengajuan kas tidak memiliki status verifikasi
        } elseif ($kategori === 'telah_disetujui') {
            $queryPengajuan->where('status', 'disetujui');
        } elseif ($kategori === 'ditolak') {
            $queryPengajuan->where('status', 'ditolak');
        } elseif ($kategori === 'memerlukan_klarifikasi') {
            $queryPengajuan->whereRaw('1 = 0');
        }

        if ($tellerId) {
            $queryPengajuan->where('user_id', $tellerId);
        }
        if ($dariTanggal && $sampaiTanggal) {
            $queryPengajuan->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal]);
        } elseif ($dariTanggal) {
            $queryPengajuan->whereDate('tanggal', '>=', $dariTanggal);
        } elseif ($sampaiTanggal) {
            $queryPengajuan->whereDate('tanggal', '<=', $sampaiTanggal);
        }
        if ($search) {
            $queryPengajuan->where(function ($q) use ($search) {
                $q->where('nomor_pengajuan', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('nama_petugas', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $pengajuanItems = ($jenisPekerjaan === 'daily_report') ? collect() : $queryPengajuan->get()->map(function ($p) {
            $statusNormalized = match ($p->status) {
                'disetujui' => 'disetujui',
                'ditolak' => 'ditolak',
                default => 'menunggu_persetujuan'
            };

            return (object) [
                'id' => $p->id,
                'pekerjaan_type' => 'pengajuan_kas',
                'pekerjaan_label' => 'Droping Kas Loket',
                'nomor_referensi' => $p->nomor_pengajuan,
                'user_id' => $p->user_id,
                'nama_teller' => $p->user->nama_petugas ?? 'Teller Loket',
                'kelas_teller' => $p->user->kelas ?? '-',
                'tanggal' => $p->tanggal ? $p->tanggal->format('Y-m-d') : null,
                'created_at' => $p->created_at,
                'nominal' => $p->nominal,
                'keterangan' => $p->alasan,
                'catatan_spv' => $p->catatan_spv,
                'alasan_penolakan' => $p->alasan_penolakan,
                'status_asli' => $p->status,
                'status_kategori' => $statusNormalized,
                'status_badge' => match ($p->status) {
                    'disetujui' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'ditolak' => 'bg-rose-100 text-rose-800 border-rose-200',
                    default => 'bg-amber-100 text-amber-800 border-amber-200'
                },
                'status_text' => match ($p->status) {
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak',
                    default => 'Menunggu Persetujuan'
                },
                'detail_url' => route('supervisor.pengajuan-kas.detail', $p->id),
                'pemeriksa' => $p->approver->nama_petugas ?? null,
            ];
        });

        // 2. Ambil Data Daily Report
        $queryReport = DailyReport::with(['user', 'approver']);

        if ($kategori === 'menunggu_persetujuan') {
            $queryReport->whereRaw('1 = 0');
        } elseif ($kategori === 'menunggu_verifikasi') {
            $queryReport->whereIn('status', ['pending', 'hold']);
        } elseif ($kategori === 'telah_disetujui') {
            $queryReport->where('status', 'approved');
        } elseif ($kategori === 'ditolak') {
            $queryReport->where('status', 'rejected');
        } elseif ($kategori === 'memerlukan_klarifikasi') {
            $queryReport->where('status', 'hold');
        }

        if ($tellerId) {
            $queryReport->where('user_id', $tellerId);
        }
        if ($dariTanggal && $sampaiTanggal) {
            $queryReport->whereBetween('report_date', [$dariTanggal, $sampaiTanggal]);
        } elseif ($dariTanggal) {
            $queryReport->whereDate('report_date', '>=', $dariTanggal);
        } elseif ($sampaiTanggal) {
            $queryReport->whereDate('report_date', '<=', $sampaiTanggal);
        }
        if ($search) {
            $queryReport->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('nama_petugas', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        $reportItems = ($jenisPekerjaan === 'pengajuan_kas') ? collect() : $queryReport->get()->map(function ($r) {
            $statusNormalized = match ($r->status) {
                'approved' => 'telah_disetujui',
                'rejected' => 'ditolak',
                'hold' => 'memerlukan_klarifikasi',
                default => 'menunggu_verifikasi'
            };

            return (object) [
                'id' => $r->id,
                'pekerjaan_type' => 'daily_report',
                'pekerjaan_label' => 'Daily Report Tutup Kas',
                'nomor_referensi' => '#REP-'.str_pad($r->id, 4, '0', STR_PAD_LEFT),
                'user_id' => $r->user_id,
                'nama_teller' => $r->user->nama_petugas ?? 'Teller Loket',
                'kelas_teller' => $r->user->kelas ?? '-',
                'tanggal' => $r->report_date ? $r->report_date->format('Y-m-d') : null,
                'created_at' => $r->created_at,
                'nominal' => $r->physical_balance ?: $r->system_balance,
                'selisih' => $r->difference,
                'keterangan' => $r->notes,
                'catatan_spv' => $r->catatan_spv,
                'alasan_penolakan' => $r->alasan_penolakan,
                'status_asli' => $r->status,
                'status_kategori' => $statusNormalized,
                'status_badge' => match ($r->status) {
                    'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'rejected' => 'bg-rose-100 text-rose-800 border-rose-200',
                    'hold' => 'bg-amber-100 text-amber-800 border-amber-200',
                    default => 'bg-blue-100 text-blue-800 border-blue-200'
                },
                'status_text' => match ($r->status) {
                    'approved' => 'Terverifikasi / Approved',
                    'rejected' => 'Ditolak',
                    'hold' => 'Perlu Klarifikasi (Hold)',
                    default => 'Menunggu Verifikasi'
                },
                'detail_url' => route('supervisor.daily-report.detail', $r->id),
                'pemeriksa' => $r->approver->nama_petugas ?? null,
            ];
        });

        // 3. Gabungkan dan Urutkan Pekerjaan Terbaru Dahulu
        $merged = $pengajuanItems->concat($reportItems)->sortByDesc('created_at')->values();

        // 4. Hitung Metrik Ringkasan Terpusat
        $totalPengajuanPending = PengajuanKas::where('status', 'pending')->count();
        $totalDailyReportPending = DailyReport::whereIn('status', ['pending', 'hold'])->count();
        $totalPekerjaanPending = $totalPengajuanPending + $totalDailyReportPending;
        $totalSelesai = PengajuanKas::whereIn('status', ['disetujui', 'ditolak'])->count() +
            DailyReport::whereIn('status', ['approved', 'rejected'])->count();

        // 5. Paginasi Koleksi
        $perPage = 15;
        $page = (int) $request->query('page', 1);
        $pagedData = $merged->slice(($page - 1) * $perPage, $perPage)->values();
        $pekerjaanList = new LengthAwarePaginator(
            $pagedData,
            $merged->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $tellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();

        return view('supervisor.approval-center', compact(
            'pekerjaanList',
            'totalPekerjaanPending',
            'totalPengajuanPending',
            'totalDailyReportPending',
            'totalSelesai',
            'tellers',
            'kategori',
            'jenisPekerjaan'
        ));
    }

    /**
     * FITUR 3: RIWAYAT PERSETUJUAN (APPROVAL HISTORY)
     * Catatan histori keputusan supervisor yang dapat ditelusuri dan tidak dapat diedit.
     */
    public function riwayatApproval(Request $request): View
    {
        $query = ApprovalHistory::with(['supervisor', 'teller']);

        if ($request->filled('pekerjaan_type')) {
            $query->where('pekerjaan_type', $request->pekerjaan_type);
        }
        if ($request->filled('jenis_keputusan')) {
            $query->where('jenis_keputusan', $request->jenis_keputusan);
        }
        if ($request->filled('teller_id')) {
            $query->where('teller_id', $request->teller_id);
        }
        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_id', $request->supervisor_id);
        }
        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $query->whereBetween('created_at', [$request->dari_tanggal.' 00:00:00', $request->sampai_tanggal.' 23:59:59']);
        } elseif ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        } elseif ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_referensi', 'like', "%{$search}%")
                    ->orWhere('nama_teller', 'like', "%{$search}%")
                    ->orWhere('nama_supervisor', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $histories = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $tellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();
        $supervisors = User::whereIn('role', ['supervisor', 'accounting'])->orderBy('nama_petugas')->get();

        // Ringkasan Histori
        $totalKeputusan = ApprovalHistory::count();
        $totalDisetujui = ApprovalHistory::where('jenis_keputusan', 'disetujui')->count();
        $totalDitolak = ApprovalHistory::where('jenis_keputusan', 'ditolak')->count();
        $totalKlarifikasi = ApprovalHistory::where('jenis_keputusan', 'klarifikasi_hold')->count();

        return view('supervisor.riwayat-approval', compact(
            'histories',
            'tellers',
            'supervisors',
            'totalKeputusan',
            'totalDisetujui',
            'totalDitolak',
            'totalKlarifikasi'
        ));
    }

    /**
     * FITUR 5: MONITORING PRODUKTIVITAS DAN AKTIVITAS TELLER
     * Memantau aktivitas operasional masing-masing Teller berdasarkan data transaksi sebenarnya.
     */
    public function monitoringTeller(Request $request): View
    {
        $dariTanggal = $request->query('dari_tanggal', Carbon::today()->toDateString());
        $sampaiTanggal = $request->query('sampai_tanggal', Carbon::today()->toDateString());
        $selectedTellerId = $request->query('teller_id');
        $selectedJenis = $request->query('jenis_transaksi');
        $selectedStatus = $request->query('status');

        $tellersQuery = User::where('role', 'teller')->orderBy('nama_petugas');
        if ($selectedTellerId) {
            $tellersQuery->where('id', $selectedTellerId);
        }
        $tellers = $tellersQuery->get();

        $tellerStats = collect();
        $chartTellerLabels = [];
        $chartTrxCounts = [];
        $chartDepositSums = [];
        $chartWithdrawalSums = [];

        foreach ($tellers as $teller) {
            $trxQuery = Transaksi::where('petugas_id', $teller->id)
                ->whereBetween('created_at', [$dariTanggal.' 00:00:00', $sampaiTanggal.' 23:59:59']);

            if ($selectedJenis) {
                $trxQuery->where('jenis_transaksi', $selectedJenis);
            }
            if ($selectedStatus) {
                $trxQuery->where('status', $selectedStatus);
            }

            $trxList = $trxQuery->get();

            // Pisahkan transaksi sukses, dibatalkan (void), dan nominal
            $trxSukses = $trxList->where('status', 'sukses');
            $trxBatal = $trxList->where('status', 'dibatalkan');
            $trxPending = $trxList->where('status', 'pending');

            $totalNominalSetoran = (int) $trxList->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->where('status', '!=', 'dibatalkan')->sum('nominal');
            $totalNominalPenarikan = (int) $trxList->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->where('status', '!=', 'dibatalkan')->sum('nominal');

            // Pengajuan kas teller pada periode tersebut
            $pengajuanKasCount = PengajuanKas::where('user_id', $teller->id)
                ->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal])
                ->count();

            // Status daily report terakhir teller pada rentang tanggal
            $latestReport = DailyReport::where('user_id', $teller->id)
                ->whereBetween('report_date', [$dariTanggal, $sampaiTanggal])
                ->orderBy('report_date', 'desc')
                ->first();

            $stat = (object) [
                'teller' => $teller,
                'total_transaksi' => $trxList->where('status', '!=', 'dibatalkan')->count(),
                'transaksi_sukses' => $trxSukses->count(),
                'transaksi_batal' => $trxBatal->count(),
                'transaksi_pending' => $trxPending->count(),
                'total_setoran' => $totalNominalSetoran,
                'total_penarikan' => $totalNominalPenarikan,
                'pengajuan_kas_count' => $pengajuanKasCount,
                'daily_report_status' => $latestReport ? $latestReport->status : 'belum_tutup_kas',
                'daily_report_selisih' => $latestReport ? $latestReport->difference : null,
                'daily_report_id' => $latestReport ? $latestReport->id : null,
            ];

            $tellerStats->push($stat);

            $chartTellerLabels[] = $teller->nama_petugas;
            $chartTrxCounts[] = $stat->total_transaksi;
            $chartDepositSums[] = $totalNominalSetoran;
            $chartWithdrawalSums[] = $totalNominalPenarikan;
        }

        // Akumulasi ringkasan keseluruhan
        $ringkasanProduktivitas = (object) [
            'total_transaksi_keseluruhan' => $tellerStats->sum('total_transaksi'),
            'total_setoran_keseluruhan' => $tellerStats->sum('total_setoran'),
            'total_penarikan_keseluruhan' => $tellerStats->sum('total_penarikan'),
            'total_sukses' => $tellerStats->sum('transaksi_sukses'),
            'total_batal' => $tellerStats->sum('transaksi_batal'),
            'total_pengajuan_kas' => $tellerStats->sum('pengajuan_kas_count'),
        ];

        // Tren Harian dalam rentang tanggal (maksimal 14 hari)
        $trendDates = [];
        $trendCounts = [];
        $start = Carbon::parse($dariTanggal);
        $end = Carbon::parse($sampaiTanggal);
        $diffDays = $start->diffInDays($end);

        if ($diffDays <= 31) {
            $current = $start->copy();
            while ($current->lte($end)) {
                $curDate = $current->toDateString();
                $trendDates[] = $current->format('d/m');
                $trendCounts[] = Transaksi::whereDate('created_at', $curDate)->where('status', '!=', 'dibatalkan')->count();
                $current->addDay();
            }
        }

        $allTellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();

        return view('supervisor.monitoring-teller', compact(
            'tellerStats',
            'ringkasanProduktivitas',
            'allTellers',
            'dariTanggal',
            'sampaiTanggal',
            'selectedTellerId',
            'selectedJenis',
            'selectedStatus',
            'chartTellerLabels',
            'chartTrxCounts',
            'chartDepositSums',
            'chartWithdrawalSums',
            'trendDates',
            'trendCounts'
        ));
    }

    /**
     * FITUR 6: REKONSILIASI LAPORAN DAN KAS
     * Membandingkan kas yang tercatat pada sistem dengan kas fisik yang dilaporkan Teller.
     */
    public function rekonsiliasi(Request $request): View
    {
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

        if ($request->filled('teller_id')) {
            $query->where('user_id', $request->teller_id);
        }

        if ($request->filled('kondisi_selisih')) {
            $kondisi = $request->kondisi_selisih;
            if ($kondisi === 'sesuai') {
                $query->where('difference', 0);
            } elseif ($kondisi === 'surplus') {
                $query->where('difference', '>', 0);
            } elseif ($kondisi === 'defisit') {
                $query->where('difference', '<', 0);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('report_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Ringkasan Metrik Rekonsiliasi Keseluruhan
        $allReportsQuery = DailyReport::query();
        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $allReportsQuery->whereBetween('report_date', [$request->dari_tanggal, $request->sampai_tanggal]);
        }
        $allMatched = $allReportsQuery->get();

        $totalLaporan = $allMatched->count();
        $totalSesuai = $allMatched->where('difference', 0)->count();
        $totalSurplus = $allMatched->where('difference', '>', 0)->count();
        $totalDefisit = $allMatched->where('difference', '<', 0)->count();
        $netSelisih = $allMatched->sum('difference');

        $tellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();

        return view('supervisor.rekonsiliasi', compact(
            'reports',
            'tellers',
            'totalLaporan',
            'totalSesuai',
            'totalSurplus',
            'totalDefisit',
            'netSelisih'
        ));
    }

    /**
     * FITUR 7: FILTER LAPORAN OPERASIONAL SUPERVISOR
     * Laporan transaksi komprehensif dengan multi-filter terintegrasi.
     */
    public function laporan(Request $request): View
    {
        $query = Transaksi::with(['nasabah', 'petugas']);

        // Default ke hari ini jika tidak ada filter tanggal
        if ($request->filled('dari_tanggal') && $request->filled('sampai_tanggal')) {
            $query->whereBetween('created_at', [$request->dari_tanggal.' 00:00:00', $request->sampai_tanggal.' 23:59:59']);
        } elseif ($request->filled('dari_tanggal')) {
            $query->whereDate('created_at', '>=', $request->dari_tanggal);
        } elseif ($request->filled('sampai_tanggal')) {
            $query->whereDate('created_at', '<=', $request->sampai_tanggal);
        } elseif ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        } else {
            $query->whereDate('created_at', Carbon::today());
        }

        $petugasId = $request->input('petugas_id') ?: $request->input('teller_id');
        if ($petugasId) {
            $query->where('petugas_id', $petugasId);
        }
        if ($request->filled('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->jenis_transaksi);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('nama_siswa', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhereHas('nasabah', function ($nq) use ($search) {
                        $nq->where('no_rekening', 'like', "%{$search}%")
                            ->orWhere('nama_siswa', 'like', "%{$search}%");
                    });
            });
        }

        // Hitung total nominal sebelum paginasi
        $totalSetoran = (clone $query)->whereIn('jenis_transaksi', ['setor', 'koreksi_tarik'])->where('status', '!=', 'dibatalkan')->sum('nominal');
        $totalPenarikan = (clone $query)->whereIn('jenis_transaksi', ['tarik', 'admin', 'koreksi_setor'])->where('status', '!=', 'dibatalkan')->sum('nominal');
        $totalTransaksiCount = (clone $query)->count();

        $transaksi = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $tellers = User::where('role', 'teller')->orderBy('nama_petugas')->get();

        return view('supervisor.laporan', compact(
            'transaksi',
            'tellers',
            'totalSetoran',
            'totalPenarikan',
            'totalTransaksiCount'
        ));
    }

    /**
     * FITUR 8: PUSAT NOTIFIKASI SUPERVISOR
     * Menampilkan riwayat notifikasi pekerjaan baru dan perbaikan laporan.
     */
    public function notifikasi(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $filterStatus = $request->query('status', 'semua');

        $query = $user->notifications();

        if ($filterStatus === 'belum_dibaca') {
            $query = $user->unreadNotifications();
        } elseif ($filterStatus === 'sudah_dibaca') {
            $query = $user->readNotifications();
        }

        $notifications = $query->paginate(15)->withQueryString();
        $unreadCount = $user->unreadNotifications()->count();
        $totalCount = $user->notifications()->count();

        return view('supervisor.notifikasi', compact(
            'notifications',
            'unreadCount',
            'totalCount',
            'filterStatus'
        ));
    }

    /**
     * Tandai Notifikasi Sebagai Terbaca dan Alihkan ke Pekerjaan Terkait
     */
    public function bacaNotifikasi(string $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            $actionUrl = $notification->data['action_url'] ?? url('/supervisor/approval-center');

            return redirect($actionUrl);
        }

        return redirect()->route('supervisor.approval-center');
    }

    /**
     * Tandai Seluruh Notifikasi Sebagai Terbaca
     */
    public function bacaSemuaNotifikasi(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }

        return back()->with('success', 'Seluruh notifikasi telah ditandai sebagai sudah dibaca.');
    }

    public function approve(int|string $id): RedirectResponse
    {
        $transaksi = Transaksi::findOrFail($id);
        $statusSebelum = $transaksi->status;
        $transaksi->update(['status' => 'sukses']);

        $spvName = Auth::user()->nama_petugas ?? 'Supervisor';

        // Rekam Approval History
        ApprovalHistory::record(
            pekerjaanType: 'transaksi',
            pekerjaanId: $transaksi->id,
            nomorReferensi: "TRX-{$transaksi->id}",
            tellerId: $transaksi->petugas_id,
            namaTeller: $transaksi->petugas?->nama_petugas ?? 'Teller',
            jenisKeputusan: 'disetujui',
            statusSebelum: $statusSebelum,
            statusSesudah: 'sukses',
            nominal: $transaksi->nominal,
            catatan: "Otorisasi transaksi loket disetujui oleh {$spvName}"
        );

        AuditHelper::log(
            jenisAktivitas: 'SETUJUI_TRANSAKSI',
            modul: 'Pusat Persetujuan',
            objekId: "TRX-{$transaksi->id}",
            oldValues: ['status' => $statusSebelum],
            newValues: ['status' => 'sukses']
        );

        return back()->with('success', 'Transaksi #'.$transaksi->id.' berhasil disetujui oleh Supervisor!');
    }

    public function cetakHarian(): View
    {
        $hariIni = Carbon::today();

        $brankas = Brankas::whereDate('tanggal', $hariIni->toDateString())->first();
        if (! $brankas) {
            $brankas = (object) [
                'saldo_awal' => 0,
                'masuk' => 0,
                'keluar' => 0,
                'total' => 0,
            ];
        }

        $totalAdmin = Transaksi::whereDate('created_at', $hariIni)
            ->where('jenis_transaksi', 'admin')
            ->sum('nominal');

        $transaksiHariIni = Transaksi::with(['nasabah', 'petugas'])
            ->whereDate('created_at', $hariIni)
            ->orderBy('created_at', 'asc')
            ->get();

        $petugasTeller = $transaksiHariIni->pluck('petugas.nama_petugas')->filter()->unique();

        return view('supervisor.cetak-harian', compact('brankas', 'totalAdmin', 'transaksiHariIni', 'petugasTeller'));
    }

    public function profil(): View
    {
        $user = Auth::user();

        return view('supervisor.profil', compact('user'));
    }

    public function updateProfil(Request $request, ProfileImageService $imageService): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'nama_petugas' => 'required|string|max:150',
            'no_hp' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+\s\-()]+$/'],
            'email' => 'nullable|email|max:150|unique:users,email,'.$user->id,
            'foto' => 'nullable|file|image|mimes:jpeg,png,jpg|mimetypes:image/jpeg,image/png|max:2048',
        ], [
            'nama_petugas.required' => 'Nama lengkap wajib diisi.',
            'nama_petugas.max' => 'Nama lengkap maksimal 150 karakter.',
            'no_hp.regex' => 'Format nomor telepon tidak valid. Gunakan angka, contoh: 081234567890.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh akun lain.',
            'foto.file' => 'File foto yang dipilih tidak valid.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.mimetypes' => 'Format file ditolak! Hanya format PNG dan JPG yang diizinkan.',
            'foto.max' => 'Ukuran file foto maksimal adalah 2 MB.',
        ]);

        $oldValues = [
            'nama_petugas' => $user->nama_petugas,
            'email' => $user->email,
            'no_hp' => $user->no_hp,
        ];

        $user->nama_petugas = $validated['nama_petugas'];
        $user->no_hp = $validated['no_hp'] ?? null;
        $user->email = $validated['email'] ?? null;

        if ($request->hasFile('foto')) {
            $user->foto = $imageService->validateAndStore($request->file('foto'), $user->foto);
        }

        $user->save();
        AvalisSyncHelper::autoSync(true);

        AuditHelper::log(
            jenisAktivitas: 'UPDATE_PROFIL',
            modul: 'Profil',
            objekId: $user->username,
            oldValues: $oldValues,
            newValues: [
                'nama_petugas' => $user->nama_petugas,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
            ]
        );

        return back()->with('success', 'Profil dan foto profil Anda berhasil diperbarui!');
    }
}
