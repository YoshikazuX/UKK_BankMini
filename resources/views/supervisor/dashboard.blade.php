<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Pengawasan Operasional - Supervisor Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scrollbar-gutter: stable; overflow-y: scroll; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR RESPONSIF -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- NOTIFIKASI SUKSES / ERROR -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- HEADER PENGAWASAN OPERASIONAL -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Otorisasi & Pengawasan Operasional</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Otorisasi droping kas, verifikasi rekonsiliasi daily report, dan monitoring kepatuhan transaksi seluruh loket Teller.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('supervisor.approval-center') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-xs shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Pusat Persetujuan Lengkap</span>
                    @if($totalPekerjaanPending > 0)
                        <span class="text-indigo-200 font-bold">({{ $totalPekerjaanPending }})</span>
                    @endif
                </a>
                <a href="{{ url('/supervisor/cetak-harian') }}" target="_blank" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-3.5 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Buku Harian
                </a>
            </div>
        </div>

        <!-- 1. RINGKASAN ATAS: KPI PENGAMBILAN KEPUTUSAN OPERASIONAL -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- 1. Pengajuan Kas Menunggu Otorisasi -->
            <a href="{{ route('supervisor.pengajuan-kas') }}" class="group bg-white p-5 rounded-xl border {{ $pendingPengajuanKasCount > 0 ? 'border-amber-300 ring-2 ring-amber-100' : 'border-slate-200' }} hover:border-slate-300 shadow-sm transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Pengajuan Droping Kas</span>
                        <div class="w-8 h-8 rounded-lg {{ $pendingPengajuanKasCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black {{ $pendingPengajuanKasCount > 0 ? 'text-amber-600' : 'text-slate-900' }} tabular-nums">
                        {{ $pendingPengajuanKasCount }}
                        <span class="text-xs font-normal text-slate-500">Permohonan</span>
                    </p>
                    <span class="inline-block text-[11px] {{ $pendingPengajuanKasCount > 0 ? 'text-amber-700 font-semibold' : 'text-slate-400' }} mt-2">
                        {{ $pendingPengajuanKasCount > 0 ? 'Menunggu persetujuan Checker' : 'Semua pengajuan telah diproses' }}
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-amber-700 font-semibold group-hover:text-amber-900">
                    <span>Otorisasi Droping Kas</span>
                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <!-- 2. Daily Report Menunggu Verifikasi -->
            <a href="{{ route('supervisor.daily-report') }}" class="group bg-white p-5 rounded-xl border {{ $pendingDailyReportCount > 0 ? 'border-blue-300 ring-2 ring-blue-100' : 'border-slate-200' }} hover:border-slate-300 shadow-sm transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Daily Report Menunggu Verifikasi</span>
                        <div class="w-8 h-8 rounded-lg {{ $pendingDailyReportCount > 0 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black {{ $pendingDailyReportCount > 0 ? 'text-blue-600' : 'text-slate-900' }} tabular-nums">
                        {{ $pendingDailyReportCount }}
                        <span class="text-xs font-normal text-slate-500">Laporan Tutup Kas</span>
                    </p>
                    <span class="inline-block text-[11px] {{ $pendingDailyReportCount > 0 ? 'text-blue-700 font-semibold' : 'text-slate-400' }} mt-2">
                        {{ $pendingDailyReportCount > 0 ? 'Menunggu pemeriksaan fisik kas' : 'Semua laporan telah disahkan' }}
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-blue-700 font-semibold group-hover:text-blue-900">
                    <span>Verifikasi Laporan</span>
                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <!-- 3. Selisih Kas yang Memerlukan Pemeriksaan -->
            <a href="{{ route('supervisor.rekonsiliasi') }}" class="group bg-white p-5 rounded-xl border {{ $supervisorAlerts['discrepancy_count'] > 0 ? 'border-rose-300 ring-2 ring-rose-100' : 'border-slate-200' }} hover:border-slate-300 shadow-sm transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Selisih Kas Rekonsiliasi</span>
                        <div class="w-8 h-8 rounded-lg {{ $supervisorAlerts['discrepancy_count'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-black {{ $supervisorAlerts['discrepancy_count'] > 0 ? 'text-rose-600' : 'text-slate-900' }} tabular-nums">
                        {{ $supervisorAlerts['discrepancy_count'] }}
                        <span class="text-xs font-normal text-slate-500">Kasus Selisih</span>
                    </p>
                    <span class="inline-block text-[11px] {{ $supervisorAlerts['discrepancy_count'] > 0 ? 'text-rose-600 font-semibold' : 'text-emerald-700' }} mt-2">
                        {{ $supervisorAlerts['discrepancy_count'] > 0 ? 'Memerlukan investigasi fisik' : 'Seluruh kas klop (Selisih Rp 0)' }}
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs {{ $supervisorAlerts['discrepancy_count'] > 0 ? 'text-rose-700' : 'text-slate-600' }} font-semibold group-hover:text-slate-900">
                    <span>Rekonsiliasi Kas Harian</span>
                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <!-- 4. Posisi Kas Fisik & Likuiditas Tersedia -->
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Kas Fisik Tersedia Loket</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-100 text-rose-800' : (($kasTersedia ?? 0) < 500000 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                            {{ ($kasTersedia ?? 0) <= 0 ? 'Kas Habis' : (($kasTersedia ?? 0) < 500000 ? 'Menipis' : 'Likuiditas Aman') }}
                        </span>
                    </div>
                    <p class="text-2xl font-black text-slate-900 tabular-nums">
                        Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}
                    </p>
                    <span class="text-[11px] text-slate-400 mt-2 block">
                        Kas Awal: Rp {{ number_format($kasAwal ?? 0, 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-indigo-600 font-semibold">
                    <a href="{{ route('supervisor.monitoring-teller') }}" class="hover:text-indigo-800 flex items-center gap-1">
                        <span>Monitor Loket Teller</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- 2. PERINGATAN OPERASIONAL SUPERVISOR (HANYA KETIKA ADA TINDAKAN DIBUTUHKAN) -->
        @if(count($supervisorAlerts['alerts']) > 0)
        <div class="mb-8 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Perhatian Supervisor</h3>
                    <p class="text-xs text-amber-800 mt-0.5">
                        Terdapat {{ $pendingPengajuanKasCount }} pengajuan kas loket, {{ $pendingDailyReportCount }} daily report tutup kas, dan {{ $supervisorAlerts['discrepancy_count'] }} laporan selisih yang memerlukan pemeriksaan.
                    </p>
                </div>
            </div>
            <a href="{{ route('supervisor.approval-center') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition whitespace-nowrap shadow-sm">
                Buka Pusat Persetujuan
            </a>
        </div>
        @endif

        <!-- 3. PUSAT PERSETUJUAN: PEKERJAAN MENUNGGU TINDAKAN (FOKUS UTAMA SUPERVISOR DENGAN TABS) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pekerjaan Menunggu Tindakan (Approval Center)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar permohonan kas loket dan laporan tutup kas harian yang membutuhkan otorisasi Anda.</p>
                </div>

                <!-- Filter Tabs: Semua / Pengajuan Kas / Daily Report -->
                <div class="inline-flex rounded-lg bg-slate-100 p-1 text-xs font-semibold">
                    <button type="button" onclick="filterApprovalTab('all')" id="tab-btn-all" class="px-3 py-1.5 rounded-md bg-white text-slate-900 shadow-sm transition">
                        Semua ({{ count($pekerjaanMenungguTindakan) }})
                    </button>
                    <button type="button" onclick="filterApprovalTab('kas')" id="tab-btn-kas" class="px-3 py-1.5 rounded-md text-slate-600 hover:text-slate-900 transition">
                        Pengajuan Kas ({{ $pendingPengajuanKasCount }})
                    </button>
                    <button type="button" onclick="filterApprovalTab('report')" id="tab-btn-report" class="px-3 py-1.5 rounded-md text-slate-600 hover:text-slate-900 transition">
                        Daily Report ({{ $pendingDailyReportCount }})
                    </button>
                </div>
            </div>

            <!-- Tabel Pekerjaan Menunggu Tindakan -->
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Kategori / Ref</th>
                            <th class="py-3 px-4">Petugas Teller</th>
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">Ringkasan Pekerjaan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Tindakan Supervisor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700" id="approval-table-body">
                        @forelse($pekerjaanMenungguTindakan as $item)
                        <tr class="approval-row hover:bg-slate-50/70 transition" data-category="{{ $item['kategori'] }}">
                            <td class="py-3 px-4 tabular-nums">
                                <span class="text-xs font-semibold uppercase {{ $item['kategori'] === 'kas' ? 'text-amber-700' : 'text-blue-700' }} block">
                                    {{ $item['kategori_label'] }}
                                </span>
                                <span class="text-slate-500 font-mono text-[11px] block mt-0.5">{{ $item['nomor_ref'] }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-900">
                                {{ $item['teller'] }}
                                <span class="block text-[11px] font-normal text-slate-400">{{ $item['teller_kelas'] }}</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500">
                                {{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">{{ $item['ringkasan'] }}</span>
                                <span class="text-slate-500 text-[11px] truncate block max-w-xs" title="{{ $item['detail'] }}">
                                    {{ $item['detail'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="text-xs font-semibold {{ $item['status_color'] === 'amber' ? 'text-amber-700' : 'text-blue-700' }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <!-- Tombol Periksa Detail Sesuai Prinsip Maker-Checker (Jangan Approve Tanpa Melihat Detail) -->
                                <a href="{{ $item['url_detail'] }}" class="inline-flex items-center gap-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-1.5 px-3 rounded-lg text-xs transition shadow-sm">
                                    <span>Periksa Detail</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr id="empty-row">
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                Semua antrean bersih. Tidak ada pekerjaan operasional yang menunggu otorisasi saat ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. SECTION BAWAH (2 KOLOM): AKTIVITAS & KEPUTUSAN TERBARU VS REKONSILIASI & KEUANGAN -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- AKTIVITAS DAN KEPUTUSAN TERBARU (2 KOLOM) -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-slate-900">Aktivitas dan Jejak Keputusan Terbaru</h2>
                    <a href="{{ route('supervisor.monitoring-teller') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        Monitoring Lengkap &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($riwayatAktivitasDanKeputusan as $act)
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-between text-xs hover:bg-slate-100/60 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg {{ $act['is_keputusan'] ? 'bg-indigo-50 text-indigo-700' : 'bg-white border border-slate-200 text-slate-600' }} flex items-center justify-center font-bold shrink-0">
                                @if($act['is_keputusan'])
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900">{{ $act['jenis'] }}</span>
                                    @if($act['is_keputusan'])
                                        <span class="text-xs font-medium text-indigo-700">(Otorisasi)</span>
                                    @endif
                                </div>
                                <span class="text-slate-500 text-[11px]">
                                    Oleh: <strong>{{ $act['pelaku'] }}</strong> &bull; {{ $act['keterangan'] }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-semibold {{ $act['status_color'] === 'emerald' ? 'text-emerald-700' : ($act['status_color'] === 'rose' ? 'text-rose-700' : ($act['status_color'] === 'amber' ? 'text-amber-700' : 'text-slate-600')) }}">
                                {{ $act['status'] }}
                            </span>
                            <span class="block text-[10px] text-slate-400 tabular-nums mt-0.5">{{ $act['time_ago'] }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada catatan aktivitas maupun keputusan terbaru.</p>
                    @endforelse
                </div>
            </div>

            <!-- PANEL KANAN: REKONSILIASI KAS & POSISI KEUANGAN (1 KOLOM) -->
            <div class="space-y-6">
                <!-- 1. RINGKASAN REKONSILIASI KAS HARIAN -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <h2 class="text-sm font-bold text-slate-900">Rekonsiliasi Kas Harian</h2>
                        <a href="{{ route('supervisor.rekonsiliasi') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Rincian &rarr;
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 text-[10px] font-semibold uppercase">Laporan Masuk</span>
                            <p class="text-base font-bold text-slate-900 mt-0.5">{{ $rekapRekonsiliasiHariIni['total_laporan'] }}</p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 text-[10px] font-semibold uppercase">Klop (Sesuai)</span>
                            <p class="text-base font-bold text-emerald-700 mt-0.5">{{ $rekapRekonsiliasiHariIni['seimbang'] }}</p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 text-[10px] font-semibold uppercase">Ada Selisih</span>
                            <p class="text-base font-bold {{ $rekapRekonsiliasiHariIni['selisih'] > 0 ? 'text-rose-600' : 'text-slate-700' }} mt-0.5">
                                {{ $rekapRekonsiliasiHariIni['selisih'] }}
                            </p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                            <span class="text-slate-500 text-[10px] font-semibold uppercase">Pending Review</span>
                            <p class="text-base font-bold text-amber-700 mt-0.5">{{ $pendingDailyReportCount }}</p>
                        </div>
                    </div>
                </div>

                <!-- 2. RINGKASAN POSISI KEUANGAN (SATU BAGIAN KEUANGAN TERPADU) -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <h2 class="text-sm font-bold text-slate-900">Ringkasan Posisi Keuangan</h2>
                        <a href="{{ route('supervisor.laporan') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                            Laporan &rarr;
                        </a>
                    </div>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Kas Fisik Tersedia:</span>
                            <strong class="text-slate-900 tabular-nums">Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}</strong>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Akumulasi Saldo Nasabah:</span>
                            <strong class="text-slate-900 tabular-nums">Rp {{ number_format($totalSaldo ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100 text-center">
                        <a href="{{ url('/supervisor/cetak-harian') }}" target="_blank" class="block w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition shadow-sm">
                            Cetak Rekap Buku Harian
                        </a>
                    </div>
                </div>

                <!-- 3. PRESENSI AVALIS BERTUGAS (RINGKASAN KEHADIRAN) -->
                @if(isset($absensiSummary))
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 text-xs space-y-2">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="font-bold text-slate-900">Kehadiran Avalis Hari Ini</span>
                        <a href="{{ url('/supervisor/absensi-avalis') }}" class="text-[11px] font-semibold text-indigo-600">Jadwal &rarr;</a>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Petugas Hadir:</span>
                        <span class="font-bold text-emerald-700">{{ $absensiSummary->totalHadir }} / {{ $absensiSummary->totalPetugas }}</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Shift Operasional:</span>
                        <span>Pagi: {{ $absensiSummary->totalShiftPagi }} &bull; Siang: {{ $absensiSummary->totalShiftSiang }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </main>

    <!-- SCRIPT CLIENT-SIDE TAB FILTER UNTUK PUSAT PERSETUJUAN -->
    <script>
        function filterApprovalTab(category) {
            const rows = document.querySelectorAll('.approval-row');
            const allBtn = document.getElementById('tab-btn-all');
            const kasBtn = document.getElementById('tab-btn-kas');
            const reportBtn = document.getElementById('tab-btn-report');

            [allBtn, kasBtn, reportBtn].forEach(btn => {
                btn.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
                btn.classList.add('text-slate-600');
            });

            const activeBtn = category === 'all' ? allBtn : (category === 'kas' ? kasBtn : reportBtn);
            activeBtn.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
            activeBtn.classList.remove('text-slate-600');

            let visibleCount = 0;
            rows.forEach(row => {
                if (category === 'all' || row.getAttribute('data-category') === category) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>
