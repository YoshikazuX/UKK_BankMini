<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Loket Teller - Bank Mini Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scrollbar-gutter: stable; overflow-y: scroll; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR RESPONSIF -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <!-- NOTIFIKASI TRANSAKSI SUKSES / SLIP -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="font-bold text-sm">Transaksi Berhasil</p>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
            @if(session('slip_url'))
            <a href="{{ session('slip_url') }}" target="_blank" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs px-3.5 py-1.5 rounded-lg shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Slip {{ session('slip_jenis') ?? 'Transaksi' }}
            </a>
            @endif
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

        <!-- HEADER OPERASIONAL TELLER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-5 mb-6 border-b border-slate-200 gap-3">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pelayanan Loket Kasir</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-0.5">
                    Petugas: <strong>{{ Auth::user()->nama_petugas }}</strong> &bull; LKM Mitra Siswa Abadi SMK Negeri 1 Bantul
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ url('/teller/daily-report') }}" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-3.5 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Tutup Kas Harian
                </a>
                <a href="{{ url('/teller/pengajuan-kas') }}" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Ajukan Droping Kas
                </a>
            </div>
        </div>

        <!-- 1. PERINGATAN OPERASIONAL TELLER (HANYA MUNCUL JIKA ADA TINDAKAN DIPERLUKAN) -->
        @if(isset($tellerAlerts) && $tellerAlerts['status'] !== 'normal')
        <div class="mb-6 p-4 rounded-xl border {{ $tellerAlerts['status'] === 'critical' ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-amber-50 border-amber-300 text-amber-900' }} shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full {{ $tellerAlerts['status'] === 'critical' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }} flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider">{{ $tellerAlerts['status_badge'] }}</h3>
                    <p class="text-xs mt-0.5 font-medium">
                        @if($tellerAlerts['kas_alert']['level'] !== 'normal')
                            {{ $tellerAlerts['kas_alert']['detail'] }}
                        @elseif($tellerAlerts['closing_alert']['level'] !== 'normal')
                            {{ $tellerAlerts['closing_alert']['detail'] }}
                        @elseif($tellerAlerts['pending_kas_alert']['count'] > 0)
                            Terdapat {{ $tellerAlerts['pending_kas_alert']['count'] }} pengajuan droping kas loket sedang menunggu otorisasi Supervisor.
                        @endif
                    </p>
                </div>
            </div>
            <div>
                @if($tellerAlerts['kas_alert']['level'] !== 'normal')
                    <a href="{{ url('/teller/pengajuan-kas') }}" class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition whitespace-nowrap shadow-sm">
                        Minta Tambah Kas
                    </a>
                @elseif($tellerAlerts['closing_alert']['level'] !== 'normal')
                    <a href="{{ url('/teller/daily-report') }}" class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-slate-900 hover:bg-slate-800 text-white transition whitespace-nowrap shadow-sm">
                        Proses Tutup Kas
                    </a>
                @endif
            </div>
        </div>
        @endif

        <!-- 2. PENCARIAN NASABAH (PRIORITAS UTAMA — PALING ATAS SETELAH HEADER) -->
        <div id="tabel-nasabah" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Pencarian Rekening & Layani Nasabah</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Ketik NIS, Nomor Rekening, atau Nama Siswa untuk langsung memulai transaksi loket.</p>
                </div>
                <div>
                    <a href="{{ url('/teller/scan') }}" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2 rounded-lg text-xs shadow-sm transition inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        Scan Kartu / QR Siswa
                    </a>
                </div>
            </div>

            <!-- Form Pencarian -->
            <form action="{{ url('/teller/dashboard') }}" method="GET" class="flex items-center gap-3 mb-5">
                <div class="flex-grow relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari NIS, Nomor Rekening, atau Nama Siswa..." class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-slate-900 text-xs sm:text-sm" autocomplete="off" autofocus>
                </div>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 px-5 rounded-lg transition shadow-sm text-xs sm:text-sm whitespace-nowrap">
                    Cari Nasabah
                </button>
                @if(request('cari'))
                    <a href="{{ url('/teller/dashboard') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-3.5 rounded-lg transition text-xs sm:text-sm whitespace-nowrap">
                        Reset
                    </a>
                @endif
            </form>

            <!-- Tabel Hasil Nasabah -->
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4">Rekening / NIS</th>
                            <th class="py-2.5 px-4">Nama Lengkap</th>
                            <th class="py-2.5 px-4">Kelas & Jurusan</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                            <th class="py-2.5 px-4 text-right">Saldo Tabungan</th>
                            <th class="py-2.5 px-4 text-center">Tindakan Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($nasabah as $n)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-bold text-slate-900 tabular-nums">
                                {{ $n->no_rekening }}
                                <span class="text-slate-400 block text-[11px] font-normal">NIS: {{ $n->nis }}</span>
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $n->nama_siswa }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $n->kelas }} - {{ $n->jurusan }}</td>
                            <td class="py-3 px-4 text-center">
                                @if($n->status === 'aktif')
                                    <span class="text-xs font-semibold text-emerald-700">Aktif</span>
                                @else
                                    <span class="text-xs font-semibold text-slate-500">{{ ucfirst($n->status) }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900 tabular-nums">
                                Rp {{ number_format($n->saldo, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap space-x-1.5">
                                <a href="{{ url('/teller/transaksi/'.$n->id) }}" class="inline-block bg-slate-900 hover:bg-slate-800 text-white font-semibold py-1 px-3 rounded-lg transition text-xs shadow-sm">
                                    Layani Transaksi &rarr;
                                </a>
                                <a href="{{ url('/buku-tabungan/'.$n->id) }}" class="inline-block bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium py-1 px-2.5 rounded-lg transition text-xs shadow-sm">
                                    Buku
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                @if(request('cari'))
                                    Nasabah dengan kata kunci "{{ request('cari') }}" tidak ditemukan.
                                @else
                                    Ketik NIS atau nama nasabah di atas untuk menampilkan data rekening siswa.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. POSISI KAS LOKET (SATU BAGIAN KAS TERPADU MENGGANTIKAN 3 KARTU TERPISAH) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Posisi Kas Loket Teller Hari Ini</h2>
                        <p class="text-xs text-slate-500">Arus kas fisik laci kasir dan ketersediaan modal operasional.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ url('/teller/pengajuan-kas') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        Atur Kas
                    </a>
                    <a href="{{ url('/teller/pengajuan-kas') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-sm">
                        Ajukan Droping Kas
                    </a>
                </div>
            </div>

            <!-- Grid 4 Nilai Arus Kas Sejajar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Saldo Awal Loket</span>
                    <p class="text-lg font-black text-slate-900 tabular-nums mt-1">
                        Rp {{ number_format($kasAwal ?? 0, 0, ',', '.') }}
                    </p>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Modal awal buka kasir</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Kas Masuk (Setor)</span>
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </div>
                    <p class="text-lg font-black text-emerald-700 tabular-nums mt-1">
                        + Rp {{ number_format($kasMasuk ?? 0, 0, ',', '.') }}
                    </p>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Total setoran siswa hari ini</span>
                </div>

                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider">Kas Keluar (Tarik)</span>
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <p class="text-lg font-black text-rose-700 tabular-nums mt-1">
                        - Rp {{ number_format($kasKeluar ?? 0, 0, ',', '.') }}
                    </p>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Penarikan tabungan hari ini</span>
                </div>

                <div class="p-3.5 rounded-xl border {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-50 border-rose-200' : (($kasTersedia ?? 0) < 100000 ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50/50 border-emerald-200') }}">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-800 uppercase tracking-wider">Kas Fisik Tersedia</span>
                        <span class="text-xs font-semibold {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-700' : (($kasTersedia ?? 0) < 100000 ? 'text-amber-700' : 'text-emerald-700') }}">
                            {{ ($kasTersedia ?? 0) <= 0 ? 'Habis' : (($kasTersedia ?? 0) < 100000 ? 'Menipis' : 'Aman') }}
                        </span>
                    </div>
                    <p class="text-lg font-black {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-700' : 'text-slate-900' }} tabular-nums mt-1">
                        Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}
                    </p>
                    <span class="text-[10px] text-slate-500 mt-0.5 block">Saldo uang fisik di laci loket</span>
                </div>
            </div>
        </div>

        <!-- 4. SECTION BAWAH (2 KOLOM): AKTIVITAS SAYA & JADWAL AVALIS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- AKTIVITAS SAYA TERAKHIR (2 KOLOM) -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-sm font-bold text-slate-900">Aktivitas Transaksi Terakhir Saya</h2>
                    <a href="{{ url('/teller/riwayat') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        Riwayat Lengkap &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($tellerActivities as $act)
                    <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-between text-xs hover:bg-slate-100/60 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-600 font-bold shrink-0">
                                @if($act['type'] === 'pengajuan_kas')
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                @elseif($act['type'] === 'daily_report')
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                @endif
                            </div>
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $act['title'] }}</span>
                                <span class="text-slate-500 text-[11px]">{{ $act['subtitle'] }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-xs font-semibold {{ $act['status_color'] === 'emerald' ? 'text-emerald-700' : ($act['status_color'] === 'rose' ? 'text-rose-700' : 'text-amber-700') }}">
                                {{ $act['status_badge'] }}
                            </span>
                            <span class="block text-[10px] text-slate-400 tabular-nums mt-0.5">{{ $act['time_ago'] }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 italic py-6 text-center">Belum ada aktivitas transaksi yang tercatat hari ini.</p>
                    @endforelse
                </div>
            </div>

            <!-- PETUGAS AVALIS BERTUGAS HARI INI (RINGKASAN & JADWAL MONITORING SESUAI RBAC) -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Petugas Avalis Bertugas Hari Ini</h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Penugasan shift loket & verifikasi nasabah.</p>
                    </div>
                    <a href="{{ url('/teller/absensi-avalis') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                        Jadwal &rarr;
                    </a>
                </div>

                @if(isset($absensiSummary))
                <!-- Stat Mini Kehadiran -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 text-[10px] font-semibold uppercase">Petugas Hadir</span>
                        <p class="text-base font-bold text-emerald-700 mt-0.5">{{ $absensiSummary->totalHadir }} / {{ $absensiSummary->totalPetugas }}</p>
                    </div>
                    <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="text-slate-500 text-[10px] font-semibold uppercase">Shift Hari Ini</span>
                        <p class="text-xs font-semibold text-slate-800 mt-0.5">Pagi: {{ $absensiSummary->totalShiftPagi }} &bull; Siang: {{ $absensiSummary->totalShiftSiang }}</p>
                    </div>
                </div>

                <!-- Daftar Nama Petugas Jaga Hari Ini (Ringkas) -->
                @if(count($absensiSummary->daftarPetugas) > 0)
                <div class="space-y-2 pt-1">
                    @foreach(collect($absensiSummary->daftarPetugas)->take(4) as $petugas)
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span class="font-semibold text-slate-800 truncate max-w-[140px]">{{ $petugas->nama }}</span>
                        <span class="text-[11px] text-slate-500">
                            {{ str_contains($petugas->shift_jaga, 'Pagi') ? 'Pagi (07.30)' : 'Siang (11.30)' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif
                @endif

                <div class="pt-3 border-t border-slate-100 text-center">
                    <a href="{{ url('/teller/daily-report') }}" class="block w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition shadow-sm">
                        Lakukan Tutup Kas Selesai Shift
                    </a>
                </div>
            </div>
        </div>

    </main>
</body>
</html>
