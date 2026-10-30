<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Petugas Avalis - LKM Mitra Siswa Abadi</title>
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
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
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

    <!-- ========================================================================= -->
    <!-- NAVBAR APLIKASI -->
    <!-- ========================================================================= -->
    <x-navbar />

    <!-- ========================================================================= -->
    <!-- KONTEN UTAMA: ABSENSI PETUGAS AVALIS -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        <!-- HEADER CETAK (HANYA MUNCUL SAAT PRINT) -->
        <div class="hidden print:block mb-6 text-center border-b-2 border-slate-800 pb-4">
            <h2 class="text-xl font-bold tracking-tight text-slate-900">LKM MITRA SISWA ABADI</h2>
            <p class="text-xs text-slate-600">Sistem Informasi Administrasi Operasional Loket & Perbankan Siswa</p>
            <h3 class="text-base font-semibold text-slate-800 mt-2">REKAPITULASI ABSENSI PETUGAS AVALIS</h3>
            <p class="text-xs text-slate-500 mt-0.5">Tanggal Cetak: {{ date('d/m/Y H:i') }} WIB</p>
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
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 text-sm font-medium p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        <!-- NOTIFIKASI ERROR -->
        @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 mb-6 rounded-xl no-print shadow-sm">
            <div class="flex items-center space-x-2 mb-1">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-semibold text-sm">Terdapat kesalahan pada input:</p>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-800 space-y-0.5 ml-6">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- ROLE BANNER UNTUK SUPERVISOR DAN TELLER (READ-ONLY) -->
        @if(!$isAdmin)
        <div class="bg-slate-100 border border-slate-200 text-slate-700 px-4 py-3 mb-6 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 no-print">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-900">Mode Pemantauan Presensi (Read-Only)</span>
                    <p class="text-xs text-slate-600 mt-0.5">Anda masuk sebagai <strong>{{ ucfirst($role) }}</strong>. Wewenang penentuan jadwal shift dan status kehadiran dikelola terpusat oleh Administrator Utama.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/kunjungan-siswa') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Verifikasi Kunjungan Siswa
                </a>
            </div>
        </div>
        @endif

        <!-- ========================================================================= -->
        <!-- HEADER HALAMAN: TITLE, DESKRIPSI & PRIMARY ACTIONS -->
        <!-- ========================================================================= -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-200/90 no-print">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-1.5">
                    <span>Administrasi</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-slate-700 font-medium">Petugas Avalis</span>
                </nav>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Absensi Petugas Avalis</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80" title="Tersinkronisasi otomatis dengan data akun pegawai / petugas asli di sistem">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sinkron Otomatis
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1">Pencatatan dan pemantauan kehadiran serta jadwal jaga harian petugas loket operasional.</p>
            </div>

            <!-- TOMBOL AKSI: HANYA TAMPIL KEPADA ADMINISTRATOR -->
            @if($isAdmin)
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Action: Verifikasi Kunjungan Siswa -->
                <a href="{{ url('/kunjungan-siswa') }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs sm:text-sm px-3.5 py-2 rounded-lg shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1" title="Lihat daftar presensi nasabah/siswa di loket">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Kunjungan Siswa</span>
                </a>

                <!-- Action: Sinkronkan dari Petugas Asli -->
                <a href="{{ url('/absensi-avalis/sync-from-petugas') }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs sm:text-sm px-3.5 py-2 rounded-lg shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1" title="Sinkronkan catatan absensi dengan data petugas asli dari sistem">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Sinkronkan Petugas</span>
                </a>

                <!-- Primary Action: Tambah Absensi -->
                <button type="button" onclick="openModalTambah()" class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs sm:text-sm px-4 py-2 rounded-lg shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-1">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Absensi</span>
                </button>

                <!-- Secondary Action: Ekspor Excel / CSV -->
                <a href="{{ url('/absensi-avalis/export') }}?tanggal={{ request('tanggal') }}&shift={{ request('shift') }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs sm:text-sm px-3.5 py-2 rounded-lg shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Ekspor Excel</span>
                </a>

                <!-- Tertiary Action: Cetak -->
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs sm:text-sm px-3.5 py-2 rounded-lg shadow-sm transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak</span>
                </button>
            </div>
            @endif
        </div>

        <!-- ========================================================================= -->
        <!-- RINGKASAN STATISTIK EKSEKUTIF (6 KARTU RESMI) -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5 my-6 no-print">
            <!-- 1. Total Petugas -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm hover:border-slate-300 transition-colors">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Petugas</p>
                <p class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ number_format($totalAvalis) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Akumulasi absensi</p>
            </div>

            <!-- 2. Hadir -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Hadir</p>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <p class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ number_format($totalHadir) }}</p>
                <p class="text-[11px] text-emerald-700 font-medium mt-1">Bertugas di loket</p>
            </div>

            <!-- 3. Izin -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Izin</p>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <p class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ number_format($totalIzin) }}</p>
                <p class="text-[11px] text-amber-700 font-medium mt-1">Dispensasi resmi</p>
            </div>

            <!-- 4. Tidak Masuk -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tidak Masuk</p>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <p class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ number_format($totalTidakMasuk) }}</p>
                <p class="text-[11px] text-rose-700 font-medium mt-1">Tanpa keterangan</p>
            </div>

            <!-- 5. Shift Pagi -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift Pagi</p>
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                </div>
                <p class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ number_format($totalShiftPagi) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">07.30 - 11.30 WIB</p>
            </div>

            <!-- 6. Shift Siang -->
            <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm hover:border-slate-300 transition-colors">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift Siang</p>
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                </div>
                <p class="text-2xl font-bold text-slate-900 tracking-tight mt-1">{{ number_format($totalShiftSiang) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">11.30 - 15.30 WIB</p>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- AREA FILTER & PENCARIAN -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-sm mb-6 no-print">
            <form method="GET" action="{{ url()->current() }}" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5 flex-grow">
                    <!-- Input Search -->
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, kelas, no WA..." class="w-full pl-9 pr-3 py-2 text-xs sm:text-sm border border-slate-300 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                    </div>

                    <!-- Filter Shift -->
                    <div class="w-full sm:w-48">
                        <select name="shift" class="w-full py-2 px-3 text-xs sm:text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                            <option value="">Semua Shift Jaga</option>
                            <option value="Pagi" {{ request('shift') == 'Pagi' ? 'selected' : '' }}>Shift Pagi (07.30 - 11.30)</option>
                            <option value="Siang" {{ request('shift') == 'Siang' ? 'selected' : '' }}>Shift Siang (11.30 - 15.30)</option>
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="w-full sm:w-40">
                        <select name="status" class="w-full py-2 px-3 text-xs sm:text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                            <option value="">Semua Status</option>
                            <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                            <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                            <option value="Tidak Masuk" {{ request('status') == 'Tidak Masuk' ? 'selected' : '' }}>Tidak Masuk</option>
                        </select>
                    </div>

                    <!-- Filter Tanggal -->
                    <div class="w-full sm:w-44">
                        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full py-2 px-3 text-xs sm:text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent transition">
                    </div>

                    <!-- Tombol Terapkan Filter -->
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-medium rounded-lg transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter</span>
                    </button>

                    <!-- Tombol Reset Filter -->
                    @if(request()->hasAny(['q', 'shift', 'status', 'tanggal']))
                        <a href="{{ url()->current() }}" class="inline-flex items-center justify-center gap-1 px-3 py-2 text-xs sm:text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>

                <div class="text-xs text-slate-500 whitespace-nowrap self-center">
                    Total data: <strong class="font-semibold text-slate-800">{{ count($absensi) }}</strong> baris
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TABEL DATA ABSENSI PETUGAS AVALIS -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm print-table">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="py-3.5 px-4 text-center w-12">No.</th>
                            <th scope="col" class="py-3.5 px-4 w-28 whitespace-nowrap">Tanggal</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[190px]">Nama Petugas</th>
                            <th scope="col" class="py-3.5 px-4 w-28 whitespace-nowrap">Kelas</th>
                            <th scope="col" class="py-3.5 px-4 w-36 whitespace-nowrap">Nomor WhatsApp</th>
                            <th scope="col" class="py-3.5 px-4 w-44 whitespace-nowrap">Shift Jaga</th>
                            <th scope="col" class="py-3.5 px-4 w-36 whitespace-nowrap">Status Kehadiran</th>
                            <th scope="col" class="py-3.5 px-4 min-w-[200px]">Keterangan / Tugas</th>
                            @if($isAdmin)
                            <th scope="col" class="py-3.5 px-4 text-center w-24 no-print whitespace-nowrap">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($absensi as $index => $item)
                        <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                            <!-- 1. No. -->
                            <td class="py-3.5 px-4 text-center tabular-nums text-slate-400 font-medium text-xs">
                                {{ $index + 1 }}
                            </td>

                            <!-- 2. Tanggal -->
                            <td class="py-3.5 px-4 tabular-nums text-slate-700 whitespace-nowrap text-xs sm:text-sm">
                                {{ date('d/m/Y', strtotime($item->tanggal)) }}
                            </td>

                            <!-- 3. Nama Petugas & Foto Profil -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $userFotoUrl = $item->foto_url;
                                        $initial = strtoupper(mb_substr(trim($item->nama), 0, 1));
                                    @endphp
                                    <div class="relative shrink-0">
                                        @if($userFotoUrl)
                                            <button type="button" 
                                                    onclick="openFotoPreview('{{ $userFotoUrl }}', '{{ addslashes($item->nama) }}', '{{ addslashes($item->kelas) }}')" 
                                                    class="block rounded-full focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-1 group"
                                                    title="Klik untuk memperbesar foto profil">
                                                <img src="{{ $userFotoUrl }}"
                                                     alt="Foto {{ $item->nama }}"
                                                     class="w-10 h-10 rounded-full object-cover border border-slate-200/90 shadow-sm group-hover:scale-105 transition-transform duration-150">
                                            </button>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-sm shadow-sm ring-1 ring-slate-200 select-none"
                                                 title="Belum ada foto profil kustom">
                                                {{ $initial }}
                                            </div>
                                        @endif
                                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white {{ $item->status === 'Hadir' ? 'bg-emerald-500' : ($item->status === 'Izin' ? 'bg-amber-500' : 'bg-rose-500') }}"
                                              title="Status: {{ $item->status }}"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-slate-900 text-xs sm:text-sm truncate">{{ $item->nama }}</div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 truncate">Pencatat: {{ $item->created_by ?? 'Sistem' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 4. Kelas -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-0.5 rounded text-xs font-medium text-slate-700 bg-slate-100 border border-slate-200/80">
                                    {{ $item->kelas }}
                                </span>
                            </td>

                            <!-- 5. Nomor WhatsApp (Teks dengan Ikon Kecil) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $cleanWa = preg_replace('/[^0-9]/', '', $item->no_wa);
                                    if (str_starts_with($cleanWa, '0')) {
                                        $cleanWa = '62' . substr($cleanWa, 1);
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $cleanWa }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs text-slate-700 hover:text-emerald-700 font-medium tabular-nums group transition-colors" title="Hubungi via WhatsApp">
                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600 transition-colors shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    <span>{{ $item->no_wa }}</span>
                                </a>
                            </td>

                            <!-- 6. Shift Jaga -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($isAdmin)
                                    <!-- Administrator Quick Shift Selector -->
                                    <form action="{{ url('/absensi-avalis/ubah-shift/'.$item->id) }}" method="POST" class="inline-block no-print">
                                        @csrf
                                        <select name="shift_jaga" onchange="this.form.submit()" class="text-xs font-medium rounded-lg px-2.5 py-1 border border-slate-200 bg-white text-slate-800 hover:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-900 cursor-pointer shadow-sm transition" title="Ubah shift petugas (Khusus Administrator)">
                                            <option value="Shift Pagi (07.30 - 11.30)" {{ str_contains($item->shift_jaga, 'Pagi') ? 'selected' : '' }}>Shift Pagi (07.30 - 11.30)</option>
                                            <option value="Shift Siang (11.30 - 15.30)" {{ str_contains($item->shift_jaga, 'Siang') ? 'selected' : '' }}>Shift Siang (11.30 - 15.30)</option>
                                        </select>
                                    </form>
                                    <!-- Print view for shift -->
                                    <span class="hidden print:inline text-xs text-slate-800 font-medium">
                                        {{ str_contains($item->shift_jaga, 'Pagi') ? 'Shift Pagi' : 'Shift Siang' }}
                                    </span>
                                @else
                                    <!-- SPV / Teller Read-Only Shift Badge -->
                                    @if(str_contains($item->shift_jaga, 'Pagi'))
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                                            Shift Pagi (07.30 - 11.30)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            Shift Siang (11.30 - 15.30)
                                        </span>
                                    @endif
                                @endif
                            </td>

                            <!-- 7. Status Kehadiran (3 Status: Hadir, Izin, Tidak Masuk) -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($isAdmin)
                                    <!-- Administrator Quick Status Selector -->
                                    <form action="{{ url('/absensi-avalis/ubah-status/'.$item->id) }}" method="POST" class="inline-block no-print">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()" class="text-xs font-semibold rounded-full px-3 py-1 border transition cursor-pointer shadow-sm focus:outline-none focus:ring-1 focus:ring-slate-900 {{ $item->status === 'Hadir' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : ($item->status === 'Izin' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-rose-50 text-rose-800 border-rose-300') }}" title="Ubah status kehadiran (Khusus Administrator)">
                                            <option value="Hadir" {{ $item->status === 'Hadir' ? 'selected' : '' }}>● Hadir</option>
                                            <option value="Izin" {{ $item->status === 'Izin' ? 'selected' : '' }}>● Izin</option>
                                            <option value="Tidak Masuk" {{ $item->status === 'Tidak Masuk' ? 'selected' : '' }}>● Tidak Masuk</option>
                                        </select>
                                    </form>
                                    <!-- Print view for status -->
                                    <span class="hidden print:inline text-xs font-semibold {{ $item->status === 'Hadir' ? 'text-emerald-700' : ($item->status === 'Izin' ? 'text-amber-700' : 'text-rose-700') }}">
                                        {{ $item->status }}
                                    </span>
                                @else
                                    <!-- SPV / Teller Read-Only Status Indicator -->
                                    @if($item->status === 'Hadir')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Hadir
                                        </span>
                                    @elseif($item->status === 'Izin')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Izin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Tidak Masuk
                                        </span>
                                    @endif
                                @endif
                            </td>

                            <!-- 8. Keterangan / Tugas -->
                            <td class="py-3.5 px-4">
                                <span class="text-xs text-slate-600 leading-relaxed">{{ $item->keterangan ?: '-' }}</span>
                            </td>

                            <!-- 9. Aksi: HANYA TAMPIL KEPADA ADMINISTRATOR -->
                            @if($isAdmin)
                            <td class="py-3.5 px-4 text-center whitespace-nowrap no-print">
                                <div class="inline-flex items-center justify-center gap-1">
                                    <button type="button" onclick='openModalEdit(@json($item))' class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors" title="Edit data absensi lengkap">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <a href="{{ url('/absensi-avalis/hapus/'.$item->id) }}" onclick="return confirm('Apakah Anda yakin ingin menghapus catatan absensi {{ addslashes($item->nama) }}?')" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus data absensi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? '9' : '8' }}" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">Tidak ada data absensi</p>
                                    <p class="text-xs text-slate-500 mt-1">Belum ada catatan absensi yang sesuai dengan kriteria filter saat ini.</p>
                                    @if(request()->hasAny(['q', 'shift', 'status', 'tanggal']))
                                        <a href="{{ url()->current() }}" class="mt-3 text-xs font-semibold text-slate-800 hover:text-slate-900 underline">
                                            Reset filter pencarian
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Summary -->
            <div class="px-5 py-3.5 bg-slate-50/70 border-t border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-500 no-print">
                <span>Menampilkan <strong>{{ count($absensi) }}</strong> catatan absensi petugas avalis</span>
                <span>LKM Mitra Siswa Abadi &bull; Sistem Administrasi Operasional</span>
            </div>
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL KHUSUS ADMINISTRATOR (HANYA DIRENDER JIKA ADMIN) -->
    <!-- ========================================================================= -->
    @if($isAdmin)
    <!-- MODAL: TAMBAH CATATAN ABSENSI BARU -->
    <div id="modalTambah" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden p-4 overflow-y-auto" onclick="handleBackdropClick(event, 'modalTambah')">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden" onclick="event.stopPropagation()">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tambah Catatan Absensi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Penugasan shift dan status kehadiran petugas avalis.</p>
                    </div>
                    <button type="button" onclick="closeModalTambah()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ url('/absensi-avalis/simpan') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <!-- Opsi Cepat: Pilih dari Petugas Avalis Asli Terdaftar -->
                        @if(isset($petugasList) && count($petugasList) > 0)
                        <div class="bg-slate-50 border border-slate-200/90 rounded-xl p-3.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Pilih dari Petugas Terdaftar</label>
                            <select id="pilih_petugas_auto" onchange="autoFillPetugas(this)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs sm:text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                <option value="">-- Pilih Petugas Avalis Asli --</option>
                                @foreach($petugasList as $p)
                                    <option value="{{ $p->nama }}" data-kelas="{{ $p->kelas }}" data-wa="{{ $p->no_wa ?? $p->no_hp ?? '' }}">
                                        {{ $p->nama }} &bull; {{ $p->kelas }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Memilih petugas akan otomatis mengisi Nama, Kelas, dan No. WhatsApp.</p>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Tanggal -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal <span class="text-rose-500">*</span></label>
                                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                            </div>

                            <!-- Shift Jaga (Ditetapkan Admin) -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Shift Jaga (Admin) <span class="text-rose-500">*</span></label>
                                <select name="shift_jaga" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                    <option value="Shift Pagi (07.30 - 11.30)">Shift Pagi (07.30 - 11.30)</option>
                                    <option value="Shift Siang (11.30 - 15.30)">Shift Siang (11.30 - 15.30)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nama Petugas Avalis -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Petugas Avalis <span class="text-rose-500">*</span></label>
                            <input type="text" id="tambah_nama" name="nama" placeholder="Contoh: Petugas Loket Teller 1" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Kelas -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                                <input type="text" id="tambah_kelas" name="kelas" placeholder="Contoh: XII AKL 1" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                            </div>

                            <!-- No WhatsApp -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp <span class="text-rose-500">*</span></label>
                                <input type="text" id="tambah_no_wa" name="no_wa" placeholder="Contoh: 081234567890" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition tabular-nums">
                            </div>
                        </div>

                        <!-- Status Kehadiran (3 Pilihan: Hadir, Izin, Tidak Masuk) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Status Kehadiran <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Tidak Masuk">Tidak Masuk</option>
                            </select>
                        </div>

                        <!-- Keterangan / Pos Tugas -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Pos Tugas</label>
                            <input type="text" name="keterangan" placeholder="Contoh: Standby Loket 1 (Setoran & Penarikan)" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModalTambah()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg text-xs sm:text-sm transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-lg text-xs sm:text-sm shadow-sm transition-colors">
                            Simpan Absensi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: EDIT CATATAN ABSENSI -->
    <div id="modalEdit" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden p-4 overflow-y-auto" onclick="handleBackdropClick(event, 'modalEdit')">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden" onclick="event.stopPropagation()">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Edit Catatan Absensi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui penugasan shift dan kehadiran petugas avalis.</p>
                    </div>
                    <button type="button" onclick="closeModalEdit()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form id="formEdit" method="POST">
                    @csrf
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Tanggal -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal <span class="text-rose-500">*</span></label>
                                <input type="date" id="edit_tanggal" name="tanggal" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                            </div>

                            <!-- Shift Jaga (Ditetapkan Admin) -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Shift Jaga (Admin) <span class="text-rose-500">*</span></label>
                                <select id="edit_shift_jaga" name="shift_jaga" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                    <option value="Shift Pagi (07.30 - 11.30)">Shift Pagi (07.30 - 11.30)</option>
                                    <option value="Shift Siang (11.30 - 15.30)">Shift Siang (11.30 - 15.30)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Nama Petugas Avalis -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Petugas Avalis <span class="text-rose-500">*</span></label>
                            <input type="text" id="edit_nama" name="nama" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Kelas -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kelas <span class="text-rose-500">*</span></label>
                                <input type="text" id="edit_kelas" name="kelas" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                            </div>

                            <!-- No WhatsApp -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp <span class="text-rose-500">*</span></label>
                                <input type="text" id="edit_no_wa" name="no_wa" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition tabular-nums">
                            </div>
                        </div>

                        <!-- Status Kehadiran (3 Pilihan: Hadir, Izin, Tidak Masuk) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Status Kehadiran <span class="text-rose-500">*</span></label>
                            <select id="edit_status" name="status" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 bg-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Tidak Masuk">Tidak Masuk</option>
                            </select>
                        </div>

                        <!-- Keterangan / Pos Tugas -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Pos Tugas</label>
                            <input type="text" id="edit_keterangan" name="keterangan" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModalEdit()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg text-xs sm:text-sm transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-lg text-xs sm:text-sm shadow-sm transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT HANYA UNTUK ADMINISTRATOR -->
    <script>
        // Auto-fill form saat memilih petugas terdaftar
        function autoFillPetugas(select) {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.value) {
                document.getElementById('tambah_nama').value = opt.value;
                document.getElementById('tambah_kelas').value = opt.getAttribute('data-kelas') || '';
                document.getElementById('tambah_no_wa').value = opt.getAttribute('data-wa') || '';
            }
        }

        // Modal Tambah
        function openModalTambah() {
            document.getElementById('modalTambah').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeModalTambah() {
            document.getElementById('modalTambah').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Modal Edit
        function openModalEdit(item) {
            document.getElementById('formEdit').action = "{{ url('/absensi-avalis/update') }}/" + item.id;
            document.getElementById('edit_tanggal').value = item.tanggal;
            document.getElementById('edit_nama').value = item.nama;
            document.getElementById('edit_kelas').value = item.kelas;
            document.getElementById('edit_no_wa').value = item.no_wa;
            
            // Normalize shift value
            if (item.shift_jaga.indexOf('Pagi') !== -1) {
                document.getElementById('edit_shift_jaga').value = 'Shift Pagi (07.30 - 11.30)';
            } else {
                document.getElementById('edit_shift_jaga').value = 'Shift Siang (11.30 - 15.30)';
            }
            
            // Normalize status
            if (item.status === 'Hadir') {
                document.getElementById('edit_status').value = 'Hadir';
            } else if (item.status === 'Izin' || item.status === 'Sakit') {
                document.getElementById('edit_status').value = 'Izin';
            } else {
                document.getElementById('edit_status').value = 'Tidak Masuk';
            }

            document.getElementById('edit_keterangan').value = item.keterangan || '';
            document.getElementById('modalEdit').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Klik backdrop untuk menutup modal
        function handleBackdropClick(event, modalId) {
            if (event.target.id === modalId) {
                if (modalId === 'modalTambah') closeModalTambah();
                if (modalId === 'modalEdit') closeModalEdit();
            }
        }

        // Tutup modal dengan tombol Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModalTambah();
                closeModalEdit();
            }
        });
    </script>
    @endif

    <!-- MODAL PREVIEW FOTO PROFIL PETUGAS AVALIS (SEMUA ROLE) -->
    <div id="modalFotoPreview" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity no-print" onclick="handlePreviewBackdropClick(event)">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <div class="relative bg-white rounded-2xl shadow-xl border border-slate-200 max-w-sm w-full p-6 text-left transform transition-all overflow-hidden" onclick="event.stopPropagation()">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div class="min-w-0 pr-2">
                        <h3 class="text-sm font-bold text-slate-900 truncate" id="previewPetugasNama">Foto Profil Petugas</h3>
                        <p class="text-xs text-slate-500 truncate mt-0.5" id="previewPetugasKelas">-</p>
                    </div>
                    <button type="button" onclick="closeFotoPreview()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition shrink-0" title="Tutup Modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="flex justify-center items-center py-2">
                    <img id="previewPetugasImg" src="" alt="Foto Profil" class="w-64 h-64 object-cover rounded-xl shadow-md border border-slate-200">
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end">
                    <button type="button" onclick="closeFotoPreview()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-xs transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT GLOBAL FOTO PREVIEW -->
    <script>
        function openFotoPreview(url, nama, kelas) {
            const img = document.getElementById('previewPetugasImg');
            const title = document.getElementById('previewPetugasNama');
            const sub = document.getElementById('previewPetugasKelas');
            const modal = document.getElementById('modalFotoPreview');

            if (img && title && sub && modal) {
                img.src = url;
                title.textContent = nama;
                sub.textContent = 'Petugas Loket ' + (kelas ? '(' + kelas + ')' : '');
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeFotoPreview() {
            const modal = document.getElementById('modalFotoPreview');
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function handlePreviewBackdropClick(event) {
            if (event.target.id === 'modalFotoPreview') {
                closeFotoPreview();
            }
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFotoPreview();
            }
        });
    </script>

</body>
</html>