<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengawasan - Supervisor Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- NOTIFIKASI SUKSES / ERROR -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- HEADER PENGENDALIAN OPERASIONAL -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Kendali Pengawasan Operasional</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Checker & Approver
                    </span>
                </div>
                <p class="text-slate-500 text-sm mt-1">
                    Pengawasan likuiditas loket, Maker-Checker pengajuan kas, verifikasi rekonsiliasi daily report, dan pemantauan aktivitas teller.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('supervisor.approval-center') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Buka Pusat Persetujuan
                    @if($totalPekerjaanPending > 0)
                        <span class="bg-white text-indigo-700 text-xs px-2 py-0.5 rounded-full font-black">{{ $totalPekerjaanPending }}</span>
                    @endif
                </a>
                <a href="{{ url('/supervisor/cetak-harian') }}" target="_blank" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-medium px-3.5 py-2 rounded-lg text-sm transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Buku Harian
                </a>
            </div>
        </div>

        <!-- 4 KARTU METRIK UTAMA -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- 1. Total Nasabah -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Total Nasabah Terdaftar</span>
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">{{ $totalNasabah }} <span class="text-sm font-normal text-slate-500">Siswa</span></p>
                </div>
                <p class="text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">Rekening tabungan aktif bank mini</p>
            </div>

            <!-- 2. Total Saldo Nasabah -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Akumulasi Saldo Nasabah</span>
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                </div>
                <p class="text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">Kewajiban simpanan di bank mini</p>
            </div>

            <!-- 3. Posisi Kas Loket & Brankas -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Kas Fisik Tersedia Hari Ini</span>
                        @if(($kasTersedia ?? 0) <= 0)
                            <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Kas Habis</span>
                        @elseif(($kasTersedia ?? 0) < 500000)
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Kas Menipis</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Likuiditas Aman</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-600' : 'text-slate-900' }} tabular-nums">
                        Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}
                    </p>
                </div>
                <div class="text-xs text-slate-500 mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="tabular-nums">Kas Awal: Rp {{ number_format($kasAwal ?? 0, 0, ',', '.') }}</span>
                    <a href="{{ route('supervisor.pengajuan-kas') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs">Droping &rarr;</a>
                </div>
            </div>

            <!-- 4. Pusat Pekerjaan Pending -->
            <div class="bg-white p-5 rounded-xl shadow-sm border {{ $totalPekerjaanPending > 0 ? 'border-amber-300 ring-2 ring-amber-100' : 'border-slate-200' }} flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Pekerjaan Perlu Tindakan</span>
                        @if($totalPekerjaanPending > 0)
                            <span class="bg-amber-500 text-slate-900 text-[10px] font-black px-2 py-0.5 rounded-full animate-pulse">
                                {{ $totalPekerjaanPending }} Pending
                            </span>
                        @else
                            <span class="bg-slate-100 text-slate-600 text-[10px] font-medium px-2 py-0.5 rounded-full">Semua Bersih</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">
                        {{ $totalPekerjaanPending }} <span class="text-sm font-normal text-slate-500">Pekerjaan</span>
                    </p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500">{{ $pendingPengajuanKasCount }} Kas &bull; {{ $pendingDailyReportCount }} Laporan</span>
                    <a href="{{ route('supervisor.approval-center') }}" class="inline-flex items-center font-bold text-amber-700 hover:text-amber-800">
                        Proses &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- PERINGATAN PEKERJAAN MENUNGGU TINDAKAN -->
        @if($totalPekerjaanPending > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl shadow-sm mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-900">Perhatian Supervisor: Terdapat {{ $totalPekerjaanPending }} pekerjaan menunggu pemeriksaan Anda</h3>
                    <p class="text-xs text-amber-800 mt-0.5">
                        {{ $pendingPengajuanKasCount }} pengajuan kas loket teller dan {{ $pendingDailyReportCount }} daily report tutup kas belum disahkan.
                    </p>
                </div>
            </div>
            <a href="{{ route('supervisor.approval-center') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition whitespace-nowrap shadow-sm">
                Buka Pusat Persetujuan Sekarang
            </a>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- KOLOM KIRI (2 KOLOM): DAFTAR PEKERJAAN PENDING HARI INI -->
            <div class="lg:col-span-2 space-y-8">
                <!-- 1. PENGAJUAN DROPING KAS MENUNGGU OTORISASI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <h2 class="font-bold text-slate-900 text-sm">Pengajuan Kas Loket Menunggu Otorisasi</h2>
                        </div>
                        <a href="{{ route('supervisor.pengajuan-kas') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Lihat Semua Pengajuan &rarr;
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4">No. Pengajuan / Waktu</th>
                                    <th class="py-3 px-4">Petugas Teller</th>
                                    <th class="py-3 px-4 text-right">Nominal Diajukan</th>
                                    <th class="py-3 px-4">Alasan</th>
                                    <th class="py-3 px-4 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($pendingPengajuanKas as $pk)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4 tabular-nums">
                                        <span class="font-bold text-slate-900 block">{{ $pk->nomor_pengajuan }}</span>
                                        <span class="text-slate-400 text-[10px]">{{ $pk->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        {{ $pk->user->nama_petugas ?? 'Teller' }}
                                        <span class="block text-[10px] font-normal text-slate-400">{{ $pk->user->kelas ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900 tabular-nums">
                                        Rp {{ number_format($pk->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate text-slate-600" title="{{ $pk->alasan }}">
                                        {{ $pk->alasan }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('supervisor.pengajuan-kas.detail', $pk->id) }}" class="inline-block bg-slate-900 hover:bg-slate-800 text-white font-semibold py-1.5 px-3 rounded-lg text-xs transition shadow-sm">
                                            Periksa Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 italic">
                                        Tidak ada permohonan kas yang sedang menunggu persetujuan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. DAILY REPORT MENUNGGU VERIFIKASI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                            <h2 class="font-bold text-slate-900 text-sm">Daily Report Tutup Kas Menunggu Verifikasi</h2>
                        </div>
                        <a href="{{ route('supervisor.daily-report') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Lihat Semua Laporan &rarr;
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4">Dokumen / Tanggal</th>
                                    <th class="py-3 px-4">Teller</th>
                                    <th class="py-3 px-4 text-right">Kas Sistem</th>
                                    <th class="py-3 px-4 text-right">Kas Fisik</th>
                                    <th class="py-3 px-4 text-right">Selisih</th>
                                    <th class="py-3 px-4 text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($pendingDailyReports as $dr)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4 tabular-nums">
                                        <span class="font-bold text-slate-900 block">#REP-{{ str_pad($dr->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-slate-400 text-[10px]">{{ $dr->report_date ? $dr->report_date->format('d/m/Y') : '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        {{ $dr->user->nama_petugas ?? 'Teller' }}
                                        <span class="block text-[10px] font-normal text-slate-400">{{ $dr->user->kelas ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium tabular-nums text-slate-700">
                                        Rp {{ number_format($dr->system_balance, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold tabular-nums text-slate-900">
                                        Rp {{ number_format($dr->physical_balance, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right tabular-nums font-bold {{ $dr->difference == 0 ? 'text-emerald-600' : ($dr->difference > 0 ? 'text-indigo-600' : 'text-rose-600') }}">
                                        {{ $dr->difference >= 0 ? '+' : '' }}Rp {{ number_format($dr->difference, 0, ',', '.') }}
                                        <span class="block text-[9px] font-normal uppercase">
                                            {{ $dr->difference == 0 ? 'Seimbang' : ($dr->difference > 0 ? 'Surplus' : 'Defisit') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="{{ route('supervisor.daily-report.detail', $dr->id) }}" class="inline-block bg-slate-900 hover:bg-slate-800 text-white font-semibold py-1.5 px-3 rounded-lg text-xs transition shadow-sm">
                                            Verifikasi
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                        Seluruh daily report tutup kas telah diverifikasi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. SECTION MONITORING PRESENSI PETUGAS AVALIS HARI INI -->
                @if(isset($absensiSummary))
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-800 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900">Pemantauan Presensi Petugas Avalis Hari Ini</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Jadwal shift dan status kehadiran teller tanggal {{ date('d F Y', strtotime($absensiSummary->tanggal)) }}</p>
                            </div>
                        </div>

                        <a href="{{ url('/supervisor/absensi-avalis') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 transition shadow-sm">
                            <span>Buka Presensi</span>
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Petugas</span>
                            <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $absensiSummary->totalPetugas }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hadir Loket</span>
                            <p class="text-xl font-bold text-emerald-700 mt-0.5">{{ $absensiSummary->totalHadir }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Izin</span>
                            <p class="text-xl font-bold text-amber-700 mt-0.5">{{ $absensiSummary->totalIzin }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Shift Pagi / Siang</span>
                            <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $absensiSummary->totalShiftPagi }} / {{ $absensiSummary->totalShiftSiang }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- KOLOM KANAN (1 KOLOM): REKONSILIASI KAS & FEED KEPUTUSAN -->
            <div class="space-y-8">
                <!-- KARTU STATUS REKONSILIASI KAS HARI INI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-slate-900">Rekonsiliasi Kas Hari Ini</h2>
                        <a href="{{ route('supervisor.rekonsiliasi') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Laporan Lengkap &rarr;
                        </a>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between items-center py-2 px-3 bg-slate-50 rounded-lg">
                            <span class="text-slate-600">Laporan Masuk:</span>
                            <span class="font-bold text-slate-900 tabular-nums">{{ $rekapRekonsiliasiHariIni['total_laporan'] }} Dokumen</span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 bg-emerald-50 rounded-lg text-emerald-900">
                            <span class="font-medium">Kas Fisik Seimbang:</span>
                            <span class="font-bold tabular-nums">{{ $rekapRekonsiliasiHariIni['seimbang'] }} Laporan</span>
                        </div>
                        <div class="flex justify-between items-center py-2 px-3 {{ $rekapRekonsiliasiHariIni['selisih'] > 0 ? 'bg-rose-50 text-rose-900' : 'bg-slate-50 text-slate-600' }} rounded-lg">
                            <span class="font-medium">Terdapat Selisih Fisik:</span>
                            <span class="font-bold tabular-nums">{{ $rekapRekonsiliasiHariIni['selisih'] }} Laporan</span>
                        </div>
                    </div>
                </div>

                <!-- RIWAYAT 5 KEPUTUSAN SUPERVISOR TERBARU -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-slate-900">Jejak Keputusan Terkini</h2>
                        <a href="{{ route('supervisor.riwayat-approval') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Semua Riwayat &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($keputusanTerakhir as $k)
                        <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 text-xs">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-slate-900">{{ $k->nomor_referensi }}</span>
                                <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-bold {{ $k->jenis_keputusan === 'disetujui' ? 'bg-emerald-100 text-emerald-800' : ($k->jenis_keputusan === 'ditolak' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $k->jenis_keputusan)) }}
                                </span>
                            </div>
                            <p class="text-slate-600 text-[11px]">
                                Oleh: <span class="font-semibold text-slate-800">{{ $k->nama_supervisor }}</span> &bull; 
                                Teller: <span class="text-slate-700">{{ $k->nama_teller }}</span>
                            </p>
                            @if($k->catatan)
                            <p class="text-slate-500 italic text-[11px] mt-1 bg-white p-1.5 rounded border border-slate-200/60 truncate" title="{{ $k->catatan }}">
                                "{{ $k->catatan }}"
                            </p>
                            @endif
                            <span class="block text-[10px] text-slate-400 mt-1 tabular-nums">{{ $k->created_at->diffForHumans() }}</span>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Belum ada jejak keputusan approval.</p>
                        @endforelse
                    </div>
                </div>

                <!-- NOTIFIKASI TUGAS TERKINI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-slate-900">Notifikasi Masuk</h2>
                        <a href="{{ route('supervisor.notifikasi') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Pusat Notifikasi &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($notifikasiTerbaru as $notif)
                        <a href="{{ route('supervisor.notifikasi.baca', $notif->id) }}" class="block p-3 rounded-lg hover:bg-slate-50 transition border border-slate-100 {{ is_null($notif->read_at) ? 'bg-amber-50/50' : 'bg-white' }}">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-900">{{ $notif->data['title'] ?? 'Notifikasi' }}</span>
                                <span class="text-[10px] text-slate-400 tabular-nums">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">{{ $notif->data['message'] ?? '-' }}</p>
                        </a>
                        @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Tidak ada notifikasi tugas baru.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
