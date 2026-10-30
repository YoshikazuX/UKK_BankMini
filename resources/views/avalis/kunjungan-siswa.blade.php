<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring & Verifikasi Kunjungan Siswa - LKM Mitra Siswa Abadi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        html { scrollbar-gutter: stable; overflow-y: scroll; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            main { max-width: 100% !important; margin: 0 !important; padding: 0 !important; }
            .print-table { border-collapse: collapse !important; width: 100% !important; }
            .print-table th, .print-table td { border: 1px solid #cbd5e1 !important; padding: 6px 8px !important; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR UNIFIKASI -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        <!-- HEADER CETAK (SAAT PRINT) -->
        <div class="hidden print:block mb-6 text-center border-b-2 border-slate-800 pb-4">
            <h2 class="text-xl font-bold tracking-tight text-slate-900">LKM MITRA SISWA ABADI</h2>
            <p class="text-xs text-slate-600">Sistem Monitoring & Verifikasi Kehadiran Fisik Siswa di Loket Pelayanan</p>
            <h3 class="text-base font-semibold text-slate-800 mt-2">TANGGAL VERIFIKASI: {{ date('d/m/Y', strtotime($tanggal)) }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">Dicetak pada: {{ date('d/m/Y H:i') }} WIB</p>
        </div>

        <!-- NOTIFIKASI SUCCESS -->
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 mb-6 rounded-xl flex items-center justify-between no-print shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 text-sm p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        <!-- NOTIFIKASI ERROR -->
        @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 mb-6 rounded-xl no-print shadow-sm">
            <div class="flex items-center space-x-2 mb-1">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-semibold text-sm">Terdapat kesalahan:</p>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-800 space-y-0.5 ml-6">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- ========================================================================= -->
        <!-- BREADCRUMB & HEADER HALAMAN -->
        <!-- ========================================================================= -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200/90 no-print">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-1.5">
                    <span>Administrasi</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <a href="{{ url('/absensi-avalis') }}" class="hover:text-slate-800 transition">Petugas Avalis</a>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-slate-700 font-medium">Monitoring Kunjungan</span>
                </nav>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring & Verifikasi Kunjungan Siswa</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Loket Operasional
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">
                    Pantau kehadiran fisik siswa di loket pelayanan dan verifikasi kunjungan berdasarkan petugas yang bertugas.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ url('/absensi-avalis') }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs sm:text-sm px-3.5 py-2 rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Absensi Petugas</span>
                </a>

                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs sm:text-sm px-3.5 py-2 rounded-lg shadow-sm transition">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- JADWAL PETUGAS LOKET (SINKRON DATA ABSENSI PETUGAS ADMIN) -->
        <!-- ========================================================================= -->
        <div class="my-6 bg-white rounded-xl border border-slate-200/90 p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3.5 mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Jadwal Petugas Loket</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Petugas operasional yang bertugas dan hadir pada tanggal {{ date('d F Y', strtotime($tanggal)) }} berdasarkan penugasan shift resmi Administrator.</p>
                    </div>
                </div>
                <div class="text-xs text-slate-500">
                    Sumber data: <span class="font-semibold text-slate-700">Absensi Petugas Avalis</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Shift Pagi -->
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80">
                    <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-slate-200/80">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                            <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Shift Pagi</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">
                            07.30 - 11.30 WIB
                        </span>
                    </div>

                    @if(count($jadwalShiftPagi) > 0)
                    <div class="space-y-2">
                        @foreach($jadwalShiftPagi as $p)
                        <div class="bg-white rounded-lg p-2.5 border border-slate-200/70 flex items-center justify-between gap-2.5">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                @php
                                    $pFotoUrl = $p->foto_url;
                                    $pInitial = strtoupper(mb_substr(trim($p->nama), 0, 1));
                                @endphp
                                @if($pFotoUrl)
                                    <img src="{{ $pFotoUrl }}" alt="Foto {{ $p->nama }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-sm">
                                        {{ $pInitial }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $p->nama }}</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">
                                        {{ $p->kelas }} &bull; <span class="font-medium text-slate-700">{{ $p->keterangan ?: 'Loket Pelayanan 1' }}</span>
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 {{ $p->status === 'Hadir' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($p->status === 'Izin' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $p->status === 'Hadir' ? 'bg-emerald-500' : ($p->status === 'Izin' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                {{ $p->status }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-slate-400 italic py-2">Belum ada petugas yang dijadwalkan pada Shift Pagi.</p>
                    @endif
                </div>

                <!-- Shift Siang -->
                <div class="bg-slate-50/80 rounded-xl p-4 border border-slate-200/80">
                    <div class="flex items-center justify-between pb-2.5 mb-2.5 border-b border-slate-200/80">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                            <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Shift Siang</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-600 bg-white px-2 py-0.5 rounded border border-slate-200">
                            11.30 - 15.30 WIB
                        </span>
                    </div>

                    @if(count($jadwalShiftSiang) > 0)
                    <div class="space-y-2">
                        @foreach($jadwalShiftSiang as $p)
                        <div class="bg-white rounded-lg p-2.5 border border-slate-200/70 flex items-center justify-between gap-2.5">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                @php
                                    $pFotoUrl = $p->foto_url;
                                    $pInitial = strtoupper(mb_substr(trim($p->nama), 0, 1));
                                @endphp
                                @if($pFotoUrl)
                                    <img src="{{ $pFotoUrl }}" alt="Foto {{ $p->nama }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-sm">
                                        {{ $pInitial }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $p->nama }}</p>
                                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">
                                        {{ $p->kelas }} &bull; <span class="font-medium text-slate-700">{{ $p->keterangan ?: 'Loket Pelayanan 2' }}</span>
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 {{ $p->status === 'Hadir' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($p->status === 'Izin' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-rose-50 text-rose-700 border border-rose-200') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $p->status === 'Hadir' ? 'bg-emerald-500' : ($p->status === 'Izin' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                {{ $p->status }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-slate-400 italic py-2">Belum ada petugas yang dijadwalkan pada Shift Siang.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- RINGKASAN STATISTIK KEHADIRAN SISWA DI LOKET (4 METRIK) -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 my-6 no-print">
            <!-- 1. Total Siswa -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa</p>
                <p class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ number_format($totalSiswa) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Populasi nasabah aktif</p>
            </div>

            <!-- 2. Terverifikasi Hadir -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Terverifikasi Hadir</p>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <p class="text-2xl font-bold text-emerald-700 tracking-tight mt-1">{{ number_format($totalTerverifikasiHadir) }}</p>
                <p class="text-[11px] text-emerald-600 font-medium mt-1">Konfirmasi fisik loket</p>
            </div>

            <!-- 3. Belum Diverifikasi -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Belum Diverifikasi</p>
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                </div>
                <p class="text-2xl font-bold text-slate-700 tracking-tight mt-1">{{ number_format($totalBelumDiverifikasi) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Menunggu pemeriksaan</p>
            </div>

            <!-- 4. Tidak Hadir -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tidak Hadir</p>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <p class="text-2xl font-bold text-rose-700 tracking-tight mt-1">{{ number_format($totalTidakHadir) }}</p>
                <p class="text-[11px] text-rose-600 font-medium mt-1">Pemeriksaan absen</p>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TOOLBAR PENCARIAN & FILTER -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm mb-6 no-print">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5 flex-grow">
                    <!-- Tanggal -->
                    <div class="w-full sm:w-40">
                        <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full py-2 px-3 text-xs sm:text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 transition" title="Pilih tanggal kunjungan">
                    </div>

                    <!-- Search Siswa -->
                    <div class="relative w-full sm:w-60">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, kelas..." class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 transition">
                    </div>

                    <!-- Filter Kelas -->
                    <div class="w-full sm:w-40">
                        <select name="kelas" class="w-full py-2 px-3 text-xs sm:text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 transition">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status Kunjungan -->
                    <div class="w-full sm:w-48">
                        <select name="status_kunjungan" class="w-full py-2 px-3 text-xs sm:text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 transition">
                            <option value="">Semua Status Kunjungan</option>
                            <option value="BELUM DIVERIFIKASI" {{ request('status_kunjungan') == 'BELUM DIVERIFIKASI' ? 'selected' : '' }}>Belum Diverifikasi</option>
                            <option value="TERVERIFIKASI HADIR" {{ request('status_kunjungan') == 'TERVERIFIKASI HADIR' ? 'selected' : '' }}>Terverifikasi Hadir</option>
                            <option value="TIDAK HADIR" {{ request('status_kunjungan') == 'TIDAK HADIR' ? 'selected' : '' }}>Tidak Hadir</option>
                        </select>
                    </div>

                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-semibold rounded-lg transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter</span>
                    </button>

                    @if(request()->hasAny(['q', 'kelas', 'status_kunjungan', 'tanggal']))
                        <a href="{{ url()->current() }}" class="inline-flex items-center gap-1 px-3 py-2 text-xs sm:text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>

                <div class="text-xs text-slate-500 whitespace-nowrap self-center">
                    Daftar Kunjungan: <strong class="text-slate-800">{{ count($daftarKunjungan) }}</strong> siswa
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TABEL MONITORING & VERIFIKASI KUNJUNGAN SISWA (9 KOLOM RESMI) -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm print-table">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="py-3.5 px-3.5 text-center w-12">No.</th>
                            <th scope="col" class="py-3.5 px-3.5 w-24 whitespace-nowrap">NIS</th>
                            <th scope="col" class="py-3.5 px-3.5 min-w-[180px]">Nama Lengkap Siswa</th>
                            <th scope="col" class="py-3.5 px-3.5 w-24 whitespace-nowrap">Kelas</th>
                            <th scope="col" class="py-3.5 px-3.5 w-40 whitespace-nowrap">Status Kunjungan</th>
                            <th scope="col" class="py-3.5 px-3.5 w-36 whitespace-nowrap">Waktu Verifikasi</th>
                            <th scope="col" class="py-3.5 px-3.5 min-w-[180px]">Transaksi Pelayanan</th>
                            <th scope="col" class="py-3.5 px-3.5 min-w-[160px]">Petugas Verifikator</th>
                            <th scope="col" class="py-3.5 px-3.5 text-center w-36 no-print whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($daftarKunjungan as $idx => $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- 1. No. -->
                            <td class="py-3.5 px-3.5 text-center text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>

                            <!-- 2. NIS -->
                            <td class="py-3.5 px-3.5 font-mono text-slate-700 text-xs tabular-nums">{{ $item->nis }}</td>

                            <!-- 3. Nama Lengkap Siswa -->
                            <td class="py-3.5 px-3.5">
                                <div class="font-semibold text-slate-900 text-xs sm:text-sm">{{ $item->nama_siswa }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Rek: {{ $item->no_rekening }}</div>
                            </td>

                            <!-- 4. Kelas -->
                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded text-xs font-medium text-slate-700 bg-slate-100 border border-slate-200/80">
                                    {{ $item->kelas }}
                                </span>
                            </td>

                            <!-- 5. Status Kunjungan (3 Status) -->
                            <td class="py-3.5 px-3.5 whitespace-nowrap">
                                @if($item->status_kunjungan === 'TERVERIFIKASI HADIR')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Terverifikasi Hadir
                                    </span>
                                @elseif($item->status_kunjungan === 'TIDAK HADIR')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Tidak Hadir
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Belum Diverifikasi
                                    </span>
                                @endif
                            </td>

                            <!-- 6. Waktu Verifikasi -->
                            <td class="py-3.5 px-3.5 whitespace-nowrap tabular-nums text-xs">
                                @if($item->waktu_verifikasi !== '-')
                                    <span class="font-medium text-slate-800">{{ $item->waktu_verifikasi }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <!-- 7. Transaksi Pelayanan (Data Terpisah dari Kehadiran Fisik) -->
                            <td class="py-3.5 px-3.5">
                                @if($item->latest_trx)
                                    <div class="font-semibold text-slate-900 text-xs capitalize flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        {{ $item->latest_trx->jenis_transaksi }}: Rp {{ number_format($item->latest_trx->nominal, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ date('H:i', strtotime($item->latest_trx->created_at)) }} WIB &bull; Teller: {{ $item->latest_trx->nama_teller_layanan ?? 'Loket' }}
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum tercatat</span>
                                @endif
                            </td>

                            <!-- 8. Petugas yang Memverifikasi -->
                            <td class="py-3.5 px-3.5">
                                @if($item->nama_verifikator !== '-')
                                    <div class="text-xs font-semibold text-slate-800">{{ $item->nama_verifikator }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $item->loket ?: 'Loket Pelayanan' }} &bull; {{ str_contains($item->shift, 'Pagi') ? 'Shift Pagi' : 'Shift Siang' }}</div>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>

                            <!-- 9. Aksi -->
                            <td class="py-3.5 px-3.5 text-center whitespace-nowrap no-print">
                                @if($item->status_kunjungan === 'BELUM DIVERIFIKASI')
                                    @if($canVerify)
                                        <!-- Tombol Verifikasi Kehadiran untuk Teller & Admin -->
                                        <button type="button" onclick='openModalVerifikasi(@json($item))' class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>Verifikasi</span>
                                        </button>
                                    @else
                                        <!-- SPV Mode Pantau -->
                                        <span class="text-xs text-slate-400 italic">Menunggu Verifikasi</span>
                                    @endif
                                @else
                                    <!-- Sudah Diverifikasi -->
                                    <div class="inline-flex items-center justify-center gap-1.5">
                                        <button type="button" onclick='openModalDetail(@json($item))' class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-sm transition" title="Lihat detail verifikasi">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Detail</span>
                                        </button>

                                        @if($isAdmin && $item->verifikasi_id)
                                        <!-- Khusus Administrator: Opsi Reset / Koreksi Verifikasi -->
                                        <form action="{{ url('/kunjungan-siswa/reset/'.$item->verifikasi_id) }}" method="POST" class="inline" onsubmit="return confirm('Reset verifikasi kehadiran {{ addslashes($item->nama_siswa) }} menjadi Belum Diverifikasi?')">
                                            @csrf
                                            <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded transition" title="Reset verifikasi siswa">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-12 px-4 text-center text-slate-400">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada data siswa</p>
                                    <p class="text-xs text-slate-400 mt-1">Belum ada siswa yang sesuai dengan filter tanggal atau kriteria pencarian.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500 no-print">
                <span>Menampilkan <strong>{{ count($daftarKunjungan) }}</strong> siswa terdaftar</span>
                <span>LKM Mitra Siswa Abadi &bull; Monitoring Kehadiran Loket Operasional</span>
            </div>
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL: VERIFIKASI KEHADIRAN SISWA (TELLER & ADMIN) -->
    <!-- ========================================================================= -->
    @if($canVerify)
    <div id="modalVerifikasi" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden p-4 overflow-y-auto" onclick="handleBackdropClick(event, 'modalVerifikasi')">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden" onclick="event.stopPropagation()">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Verifikasi Kehadiran Siswa</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Konfirmasi fisik kehadiran siswa di loket perbankan.</p>
                    </div>
                    <button type="button" onclick="closeModalVerifikasi()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ url('/kunjungan-siswa/verifikasi') }}" method="POST" onsubmit="return confirm('Simpan hasil verifikasi kehadiran fisik siswa ini?')">
                    @csrf
                    <input type="hidden" name="nasabah_id" id="verif_nasabah_id">
                    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                    <div class="p-6 space-y-4">
                        <!-- Identitas Siswa Target -->
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Identitas Siswa</span>
                            <p class="text-sm font-bold text-slate-900 mt-0.5" id="verif_nama_siswa">-</p>
                            <p class="text-xs text-slate-600 mt-0.5">
                                NIS: <span class="font-mono font-medium text-slate-800" id="verif_nis">-</span> &bull; Kelas: <span class="font-medium text-slate-800" id="verif_kelas">-</span>
                            </p>
                        </div>

                        <!-- Pilihan Status Kehadiran -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                                Status Kehadiran Fisik Siswa <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="border border-slate-300 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50/50 has-[:checked]:ring-2 has-[:checked]:ring-emerald-200">
                                    <input type="radio" name="status_kunjungan" value="TERVERIFIKASI HADIR" checked class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Terverifikasi Hadir</p>
                                        <p class="text-[11px] text-slate-500">Siswa datang ke loket</p>
                                    </div>
                                </label>
                                <label class="border border-slate-300 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/50 has-[:checked]:ring-2 has-[:checked]:ring-rose-200">
                                    <input type="radio" name="status_kunjungan" value="TIDAK HADIR" class="text-rose-600 focus:ring-rose-500">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900">Tidak Hadir</p>
                                        <p class="text-[11px] text-slate-500">Siswa tidak datang</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Loket Pelayanan -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Loket Pelayanan <span class="text-rose-500">*</span></label>
                                <select name="loket" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs sm:text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                    <option value="Loket 1">Loket 1 (Setoran & Penarikan)</option>
                                    <option value="Loket 2">Loket 2 (Buku & Administrasi)</option>
                                    <option value="Loket 3">Loket 3 (Layanan Khusus)</option>
                                    <option value="Loket Layanan Mandiri">Loket Layanan Mandiri</option>
                                </select>
                            </div>

                            <!-- Shift Jaga -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Shift Operasional <span class="text-rose-500">*</span></label>
                                <select name="shift" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs sm:text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                    <option value="Shift Pagi (07.30 - 11.30)">Shift Pagi (07.30 - 11.30)</option>
                                    <option value="Shift Siang (11.30 - 15.30)">Shift Siang (11.30 - 15.30)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Petugas Avalis Terjadwal -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Petugas Avalis Terjadwal (Opsional)</label>
                            <select name="petugas_avalis_nama" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs sm:text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                <option value="">-- Berdasarkan Jadwal Loket Terpilih --</option>
                                @foreach($petugasAvalisPilihan as $pNama)
                                    <option value="{{ $pNama }}">{{ $pNama }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Mengaitkan pemeriksaan dengan petugas piket avalis yang hadir pada shift ini.</p>
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Catatan Pemeriksaan</label>
                            <input type="text" name="keterangan" placeholder="Contoh: Siswa hadir bersama wali kelas / konsultasi tabungan" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                        </div>

                        <!-- Verifikator Logged-in Info -->
                        <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-3 flex items-center gap-2.5 text-xs text-blue-900">
                            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Diverifikasi oleh: <strong>{{ Auth::user()->nama_petugas ?? 'Petugas' }}</strong> ({{ ucfirst($role) }}) secara sistemik.</span>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModalVerifikasi()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg text-xs sm:text-sm transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-lg text-xs sm:text-sm shadow-sm transition-colors">
                            Konfirmasi & Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- ========================================================================= -->
    <!-- MODAL: DETAIL VERIFIKASI (UNTUK SEMUA ROLE: ADMIN, SPV, TELLER) -->
    <!-- ========================================================================= -->
    <div id="modalDetail" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden p-4 overflow-y-auto" onclick="handleBackdropClick(event, 'modalDetail')">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden" onclick="event.stopPropagation()">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Detail Verifikasi Kunjungan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Informasi lengkap riwayat pemeriksaan fisik siswa di loket.</p>
                    </div>
                    <button type="button" onclick="closeModalDetail()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs sm:text-sm">
                    <!-- Siswa -->
                    <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200/80">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Identitas Siswa</p>
                        <p class="text-sm font-bold text-slate-900 mt-0.5" id="detail_nama_siswa">-</p>
                        <p class="text-xs text-slate-600 mt-0.5">
                            NIS: <span class="font-mono font-medium text-slate-800" id="detail_nis">-</span> &bull; Kelas: <span class="font-medium text-slate-800" id="detail_kelas">-</span>
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="p-3 rounded-lg border border-slate-200">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase">Status Kunjungan</span>
                            <div class="mt-1" id="detail_status_badge">-</div>
                        </div>

                        <div class="p-3 rounded-lg border border-slate-200">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase">Waktu Verifikasi</span>
                            <p class="font-semibold text-slate-900 mt-1" id="detail_waktu">-</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5">
                        <div class="p-3 rounded-lg border border-slate-200">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase">Petugas Verifikator</span>
                            <p class="font-semibold text-slate-900 mt-1" id="detail_verifikator">-</p>
                        </div>

                        <div class="p-3 rounded-lg border border-slate-200">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase">Loket & Shift</span>
                            <p class="font-medium text-slate-800 mt-1" id="detail_loket_shift">-</p>
                        </div>
                    </div>

                    <!-- Petugas Avalis Terjadwal -->
                    <div class="p-3 rounded-lg border border-slate-200">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase">Petugas Avalis Terjadwal</span>
                        <p class="font-medium text-slate-800 mt-1" id="detail_avalis">-</p>
                    </div>

                    <!-- Transaksi Pelayanan Terkait -->
                    <div class="p-3 rounded-lg border border-slate-200">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase">Transaksi Pelayanan Terkait</span>
                        <div class="mt-1" id="detail_transaksi">-</div>
                    </div>

                    <!-- Catatan / Keterangan -->
                    <div class="p-3 rounded-lg border border-slate-200">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase">Catatan / Keterangan</span>
                        <p class="text-slate-700 mt-1" id="detail_keterangan">-</p>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end">
                    <button type="button" onclick="closeModalDetail()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg text-xs sm:text-sm transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT MODAL & DATA POPULATION -->
    <script>
        function openModalVerifikasi(item) {
            document.getElementById('verif_nasabah_id').value = item.id;
            document.getElementById('verif_nama_siswa').textContent = item.nama_siswa;
            document.getElementById('verif_nis').textContent = item.nis;
            document.getElementById('verif_kelas').textContent = item.kelas;
            document.getElementById('modalVerifikasi').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModalVerifikasi() {
            const modal = document.getElementById('modalVerifikasi');
            if (modal) modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function openModalDetail(item) {
            document.getElementById('detail_nama_siswa').textContent = item.nama_siswa;
            document.getElementById('detail_nis').textContent = item.nis;
            document.getElementById('detail_kelas').textContent = item.kelas;
            document.getElementById('detail_waktu').textContent = item.waktu_verifikasi;
            document.getElementById('detail_verifikator').textContent = item.nama_verifikator;
            document.getElementById('detail_loket_shift').textContent = (item.loket || 'Loket 1') + ' &bull; ' + (item.shift || 'Shift Pagi');
            document.getElementById('detail_avalis').textContent = item.petugas_avalis_nama || 'Sesuai jadwal piket avalis';
            document.getElementById('detail_keterangan').textContent = item.keterangan || 'Tidak ada catatan tambahan.';

            // Status Badge
            const badgeContainer = document.getElementById('detail_status_badge');
            if (item.status_kunjungan === 'TERVERIFIKASI HADIR') {
                badgeContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Terverifikasi Hadir</span>';
            } else if (item.status_kunjungan === 'TIDAK HADIR') {
                badgeContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Tidak Hadir</span>';
            } else {
                badgeContainer.innerHTML = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Belum Diverifikasi</span>';
            }

            // Transaksi
            const trxContainer = document.getElementById('detail_transaksi');
            if (item.latest_trx) {
                trxContainer.innerHTML = '<span class="font-semibold text-slate-900 capitalize">' + item.latest_trx.jenis_transaksi + ': Rp ' + new Intl.NumberFormat('id-ID').format(item.latest_trx.nominal) + '</span><span class="block text-[11px] text-slate-500">' + item.latest_trx.created_at.substring(11, 16) + ' WIB &bull; Teller: ' + (item.latest_trx.nama_teller_layanan || '-') + '</span>';
            } else {
                trxContainer.innerHTML = '<span class="text-slate-400 italic">Belum tercatat transaksi pelayanan di sistem.</span>';
            }

            document.getElementById('modalDetail').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModalDetail() {
            const modal = document.getElementById('modalDetail');
            if (modal) modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function handleBackdropClick(event, modalId) {
            if (event.target.id === modalId) {
                if (modalId === 'modalVerifikasi') closeModalVerifikasi();
                if (modalId === 'modalDetail') closeModalDetail();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModalVerifikasi();
                closeModalDetail();
            }
        });
    </script>
</body>
</html>
