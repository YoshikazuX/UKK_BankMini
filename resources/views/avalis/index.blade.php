<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Avalis Digital (Excel View) - LKM Mitra Siswa Abadi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
        /* Grid Garis Khas Spreadsheet Excel */
        .excel-table {
            border-collapse: collapse;
            width: 100%;
        }
        .excel-table th {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            padding: 8px 10px;
            user-select: none;
        }
        .excel-table td {
            border: 1px solid #e2e8f0;
            padding: 7px 12px;
            font-size: 0.875rem;
        }
        .excel-row-header {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 700;
            text-align: center;
            width: 45px;
            border-right: 2px solid #cbd5e1 !important;
            user-select: none;
        }
        .excel-table tr:hover td:not(.excel-row-header) {
            background-color: #f0fdf4;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .excel-container { box-shadow: none; border: none; margin: 0; padding: 0; width: 100%; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 min-h-screen flex flex-col">

    <!-- ========================================================================= -->
    <!-- NAVBAR STANDAR KONSISTEN (SAMA DENGAN ADMIN) -->
    <!-- ========================================================================= -->
    <nav class="bg-slate-800 text-white shadow-md relative no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>

                @if(Auth::user()->role === 'teller')
                    <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/teller/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/teller/riwayat') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Riwayat Saya</a>
                        <a href="{{ url('/teller/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Tutup Kas</a>
                        <a href="{{ url('/teller/pengajuan-kas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Pengajuan Kas</a>
                        <a href="{{ url('/teller/absensi-avalis') }}" class="text-white font-medium underline decoration-2 underline-offset-4 flex items-center whitespace-nowrap focus:outline-none">
                            <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                            Absensi Avalis
                        </a>
                @elseif(Auth::user()->role === 'supervisor')
                    <div class="flex items-center space-x-2.5 lg:space-x-3.5 xl:space-x-5 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/supervisor/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/supervisor/laporan') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Laporan Transaksi</a>
                        <a href="{{ url('/supervisor/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Daily Report</a>
                        <a href="{{ url('/supervisor/pengajuan-kas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Pengajuan Kas</a>
                        <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                        <a href="{{ url('/laporan/saldo-produk') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Produk</a>
                        <a href="{{ url('/laporan/tahunan') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Tahunan</a>
                        <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Data Teller</a>
                        <a href="{{ url('/supervisor/absensi-avalis') }}" class="text-white font-medium underline decoration-2 underline-offset-4 flex items-center whitespace-nowrap focus:outline-none">Absensi</a>
                @else
                    <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Manajemen Nasabah</a>
                        <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Petugas Avalis</a>
                        <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                        <a href="{{ url('/admin/audit') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jejak Audit</a>
                        <a href="{{ url('/admin/absensi-avalis') }}" class="text-white font-medium underline decoration-2 underline-offset-4 flex items-center whitespace-nowrap focus:outline-none">
                            <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                            Absensi
                        </a>
                        <a href="{{ url('/admin/konfigurasi') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Konfigurasi</a>
                @endif

                    <!-- Profil Dropdown -->
                    <div class="relative ml-2 shrink-0">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600 whitespace-nowrap">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline whitespace-nowrap">{{ Auth::user()->nama_petugas }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">
                                    {{ Auth::user()->role }}
                                </span>
                            </div>
                            @if($role === 'admin')
                                <a href="{{ url('/admin/profil') }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
                                </a>
                            @elseif($role === 'teller')
                                <a href="{{ url('/teller/profil') }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
                                </a>
                            @else
                                <a href="{{ url('/supervisor/profil') }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
                                </a>
                            @endif
                            <div class="border-t border-slate-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 flex items-center font-bold">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- ========================================================================= -->
    <!-- KONTEN UTAMA: SPREADSHEET DIGITAL EXCEL ABSENSI AVALIS -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 p-4 mb-5 rounded shadow-sm flex items-center justify-between no-print">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xs font-bold uppercase">&times; Tutup</button>
        </div>
        @endif

        @if(isset($errors) && $errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-600 text-rose-800 p-4 mb-5 rounded shadow-sm no-print">
            <p class="font-bold text-sm">Terdapat kesalahan pengisian data:</p>
            <ul class="list-disc list-inside text-xs mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- EXCEL SHEET WORKBOOK CONTAINER -->
        <div class="excel-container bg-white rounded-xl shadow-md border border-slate-300 overflow-hidden">

            <!-- 1. EXCEL TITLE BAR / RIBBON HEADER (HIJAU KHAS MICROSOFT EXCEL) -->
            <div class="bg-emerald-800 text-white px-5 py-3.5 flex flex-wrap justify-between items-center gap-3 no-print">
                <div class="flex items-center space-x-3">
                    <!-- Icon Excel SVG -->
                    <div class="w-9 h-9 bg-emerald-600 rounded flex items-center justify-center font-black text-white text-lg shadow border border-emerald-500">
                        X
                    </div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <h1 class="font-bold text-base tracking-wide">Absensi_Avalis_2026.xlsx</h1>
                            <span class="bg-emerald-900 text-emerald-200 text-[11px] font-semibold px-2 py-0.5 rounded border border-emerald-700">Digital Spreadsheet</span>
                            <span class="bg-white/20 text-white text-[11px] font-bold px-2 py-0.5 rounded">Tersimpan Otomatis</span>
                        </div>
                        <p class="text-xs text-emerald-200 mt-0.5">Buku Absensi & Jadwal Jaga Petugas Avalis Siswa - LKM Mitra Siswa Abadi</p>
                    </div>
                </div>

                <!-- Action Buttons: Tambah Baris & Ekspor Excel -->
                <div class="flex items-center space-x-2">
                    <button onclick="openModalTambah()" class="bg-white hover:bg-emerald-50 text-emerald-900 font-bold px-3.5 py-1.5 rounded-lg text-xs shadow-sm transition flex items-center border border-emerald-300">
                        <svg class="w-4 h-4 mr-1 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        + Input Baris Avalis
                    </button>

                    <a href="{{ url('/absensi-avalis/export') }}?tanggal={{ request('tanggal') }}&shift={{ request('shift') }}" class="bg-emerald-700 hover:bg-emerald-600 text-white font-bold px-3.5 py-1.5 rounded-lg text-xs shadow-sm transition flex items-center border border-emerald-600">
                        <svg class="w-4 h-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Unduh File Excel (.CSV)
                    </a>

                    <button onclick="window.print()" class="bg-emerald-900 hover:bg-emerald-950 text-emerald-100 font-semibold px-3 py-1.5 rounded-lg text-xs shadow-sm transition flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak
                    </button>
                </div>
            </div>

            <!-- 2. EXCEL FORMULA BAR (fx) & STATISTIK REKAP -->
            <div class="bg-slate-50 border-b border-slate-300 px-4 py-2 flex flex-wrap items-center justify-between text-xs gap-3 no-print">
                <div class="flex items-center space-x-2">
                    <span class="font-mono font-bold bg-white border border-slate-300 px-2.5 py-1 rounded text-slate-600 shadow-inner">A1</span>
                    <span class="font-serif italic font-bold text-slate-500 text-sm">fx</span>
                    <span class="font-mono text-slate-600 bg-white border border-slate-200 px-3 py-1 rounded text-xs">=REKAP_AVALIS(Nama; Kelas; No_WA; Shift_Jaga; Kehadiran)</span>
                </div>

                <!-- Chips Rekap / Statistik Excel -->
                <div class="flex items-center space-x-3 text-xs">
                    <span class="text-slate-600 font-medium">Total Terdata: <strong class="text-slate-900">{{ $totalAvalis }}</strong></span>
                    <span class="h-3 w-px bg-slate-300"></span>
                    <span class="text-emerald-700 font-medium">Hadir: <strong>{{ $totalHadir }}</strong></span>
                    <span class="h-3 w-px bg-slate-300"></span>
                    <span class="text-amber-700 font-medium">Izin: <strong>{{ $totalIzin }}</strong></span>
                    <span class="h-3 w-px bg-slate-300"></span>
                    <span class="text-indigo-700 font-medium">Shift Pagi: <strong>{{ $totalShiftPagi }}</strong></span>
                    <span class="h-3 w-px bg-slate-300"></span>
                    <span class="text-blue-700 font-medium">Shift Siang: <strong>{{ $totalShiftSiang }}</strong></span>
                </div>
            </div>

            <!-- 3. PENCARIAN & FILTER BARIS SPREADSHEET -->
            <div class="bg-white p-3.5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 no-print">
                <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-2 text-xs w-full sm:w-auto">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari Nama / Kelas / No WA..." class="border border-slate-300 rounded px-3 py-1.5 w-60 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <select name="shift" class="border border-slate-300 rounded px-2.5 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Semua Shift Jaga --</option>
                        <option value="Pagi" {{ request('shift') == 'Pagi' ? 'selected' : '' }}>Shift Pagi (07.30 - 11.30)</option>
                        <option value="Siang" {{ request('shift') == 'Siang' ? 'selected' : '' }}>Shift Siang (11.30 - 15.30)</option>
                    </select>

                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="border border-slate-300 rounded px-2.5 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:outline-none">

                    <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white font-semibold px-3 py-1.5 rounded transition">Filter Data</button>
                    @if(request()->hasAny(['q', 'shift', 'tanggal']))
                        <a href="{{ url()->current() }}" class="text-rose-600 hover:underline px-2 py-1.5">Reset</a>
                    @endif
                </form>

                <div class="text-[11px] text-slate-500 italic">
                    *Kolom wajib: <strong class="text-slate-700">Nama</strong>, <strong class="text-slate-700">Kelas</strong>, <strong class="text-slate-700">No WA</strong>, dan <strong class="text-slate-700">Shift Jaga</strong>
                </div>
            </div>

            <!-- 4. TABEL EXCEL SPREADSHEET GRID -->
            <div class="overflow-x-auto">
                <table class="excel-table">
                    <thead>
                        <tr>
                            <th class="excel-row-header">#</th>
                            <th style="width: 50px;">A: No</th>
                            <th style="width: 110px;">B: Tanggal</th>
                            <th>C: Nama Avalis</th>
                            <th style="width: 120px;">D: Kelas</th>
                            <th style="width: 170px;">E: No WhatsApp</th>
                            <th style="width: 190px;">F: Shift Jaga</th>
                            <th style="width: 110px;">G: Status</th>
                            <th>H: Keterangan / Tugas</th>
                            <th class="no-print" style="width: 110px; text-align: center;">I: Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensi as $index => $item)
                        <tr>
                            <!-- Nomor Baris Excel Kiri -->
                            <td class="excel-row-header">{{ $index + 1 }}</td>

                            <!-- A: No Urut -->
                            <td class="text-center font-mono text-slate-500 font-semibold">{{ $index + 1 }}</td>

                            <!-- B: Tanggal -->
                            <td class="font-mono text-xs whitespace-nowrap text-slate-700">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>

                            <!-- C: Nama Avalis -->
                            <td>
                                <div class="font-bold text-slate-900">{{ $item->nama }}</div>
                                <span class="text-[11px] text-slate-400">Pencatat: {{ $item->created_by ?? 'Sistem' }}</span>
                            </td>

                            <!-- D: Kelas -->
                            <td>
                                <span class="bg-slate-100 text-slate-800 font-bold px-2 py-0.5 rounded text-xs border border-slate-200">
                                    {{ $item->kelas }}
                                </span>
                            </td>

                            <!-- E: No WhatsApp (Dengan Direct Link WA) -->
                            <td class="whitespace-nowrap">
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $item->no_wa)) }}" target="_blank" class="inline-flex items-center text-emerald-700 hover:text-emerald-900 font-mono text-xs font-semibold hover:underline bg-emerald-50 px-2 py-1 rounded border border-emerald-200" title="Klik untuk Chat WhatsApp">
                                    <svg class="w-3.5 h-3.5 mr-1 text-emerald-600 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    {{ $item->no_wa }}
                                </a>
                            </td>

                            <!-- F: Shift Jaga -->
                            <td class="font-medium text-xs">
                                @if(str_contains($item->shift_jaga, 'Pagi'))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5"></span>
                                        {{ $item->shift_jaga }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                        {{ $item->shift_jaga }}
                                    </span>
                                @endif
                            </td>

                            <!-- G: Status Kehadiran -->
                            <td>
                                @if($item->status === 'Hadir')
                                    <span class="bg-emerald-100 text-emerald-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-emerald-300">
                                        HADIR
                                    </span>
                                @elseif($item->status === 'Izin')
                                    <span class="bg-amber-100 text-amber-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-amber-300">
                                        IZIN
                                    </span>
                                @elseif($item->status === 'Sakit')
                                    <span class="bg-blue-100 text-blue-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-blue-300">
                                        SAKIT
                                    </span>
                                @else
                                    <span class="bg-rose-100 text-rose-800 text-[11px] font-bold px-2 py-0.5 rounded-full border border-rose-300">
                                        {{ strtoupper($item->status) }}
                                    </span>
                                @endif
                            </td>

                            <!-- H: Keterangan -->
                            <td class="text-slate-600 text-xs">{{ $item->keterangan ?? '-' }}</td>

                            <!-- I: Aksi (Edit & Hapus) -->
                            <td class="no-print text-center whitespace-nowrap">
                                <button onclick='openModalEdit(@json($item))' class="text-blue-600 hover:text-blue-800 font-bold text-xs mr-2 px-1 py-0.5 rounded hover:bg-blue-50" title="Edit Baris">
                                    Edit
                                </button>
                                <a href="{{ url('/absensi-avalis/hapus/'.$item->id) }}" onclick="return confirm('Hapus baris absensi Avalis: {{ $item->nama }}?')" class="text-rose-600 hover:text-rose-800 font-bold text-xs px-1 py-0.5 rounded hover:bg-rose-50" title="Hapus Baris">
                                    Hapus
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-8 text-slate-400 italic">
                                Belum ada data absensi Avalis yang sesuai dengan filter. Klik <strong>+ Input Baris Avalis</strong> untuk menambahkan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- EXCEL SHEET TABS (SHEET1, REKAP) -->
            <div class="bg-slate-100 border-t border-slate-300 px-4 py-1.5 flex items-center justify-between text-xs text-slate-500 no-print">
                <div class="flex items-center space-x-1">
                    <button class="bg-white text-emerald-800 font-bold px-4 py-1 rounded-t border-t-2 border-emerald-600 shadow-sm flex items-center">
                        <span class="w-2 h-2 rounded-full bg-emerald-600 mr-2"></span>
                        Sheet1 - Absensi Harian Avalis
                    </button>
                    <span class="text-slate-400 text-[11px] ml-2">Total {{ count($absensi) }} Baris Data Dimuat</span>
                </div>
                <div class="text-[11px] text-slate-400 font-mono">
                    Format: XLSX / CSV (Kompatibel 100% Microsoft Excel)
                </div>
            </div>

        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: TAMBAH BARIS EXCEL AVALIS BARU -->
    <!-- ========================================================================= -->
    <div id="modalTambah" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="bg-emerald-800 text-white px-5 py-3.5 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-sm">Input Baris Absensi Avalis Baru</span>
                </div>
                <button onclick="closeModalTambah()" class="text-emerald-200 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form action="{{ url('/absensi-avalis/simpan') }}" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <!-- Tanggal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal *</label>
                        <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Shift Jaga -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Shift Jaga *</label>
                        <select name="shift_jaga" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                            <option value="Shift Pagi (07.30 - 11.30)">Shift Pagi (07.30 - 11.30)</option>
                            <option value="Shift Siang (11.30 - 15.30)">Shift Siang (11.30 - 15.30)</option>
                            <option value="Shift Penuh (07.30 - 15.30)">Shift Penuh (07.30 - 15.30)</option>
                        </select>
                    </div>

                    <!-- Nama Avalis -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Avalis / Petugas Jaga *</label>
                        <input type="text" name="nama" placeholder="Contoh: Siti Nurhaliza" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kelas *</label>
                        <input type="text" name="kelas" placeholder="Contoh: XII AKL 1" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- No WhatsApp yang bisa dihubungi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. WhatsApp *</label>
                        <input type="text" name="no_wa" placeholder="Contoh: 081234567890" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
                    </div>

                    <!-- Status Kehadiran -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Kehadiran *</label>
                        <select name="status" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white font-medium">
                            <option value="Hadir" class="text-emerald-700 font-bold">Hadir</option>
                            <option value="Izin" class="text-amber-700 font-bold">Izin</option>
                            <option value="Sakit" class="text-blue-700 font-bold">Sakit</option>
                            <option value="Alpha" class="text-rose-700 font-bold">Alpha</option>
                        </select>
                    </div>

                    <!-- Keterangan / Tugas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Pos Tugas</label>
                        <input type="text" name="keterangan" placeholder="Contoh: Standby Loket 1" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                </div>

                <div class="mt-6 flex justify-end space-x-2 border-t border-slate-200 pt-4">
                    <button type="button" onclick="closeModalTambah()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs shadow transition flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan ke Lembar Excel
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL POPUP: EDIT BARIS EXCEL AVALIS -->
    <!-- ========================================================================= -->
    <div id="modalEdit" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 max-w-lg w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <div class="bg-slate-800 text-white px-5 py-3.5 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-sm">Perbarui Data Absensi Avalis</span>
                </div>
                <button onclick="closeModalEdit()" class="text-slate-300 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form id="formEdit" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <!-- Tanggal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal *</label>
                        <input type="date" id="edit_tanggal" name="tanggal" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:outline-none">
                    </div>

                    <!-- Shift Jaga -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Shift Jaga *</label>
                        <select id="edit_shift_jaga" name="shift_jaga" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:outline-none bg-white">
                            <option value="Shift Pagi (07.30 - 11.30)">Shift Pagi (07.30 - 11.30)</option>
                            <option value="Shift Siang (11.30 - 15.30)">Shift Siang (11.30 - 15.30)</option>
                            <option value="Shift Penuh (07.30 - 15.30)">Shift Penuh (07.30 - 15.30)</option>
                        </select>
                    </div>

                    <!-- Nama Avalis -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Avalis / Petugas Jaga *</label>
                        <input type="text" id="edit_nama" name="nama" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:outline-none">
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kelas *</label>
                        <input type="text" id="edit_kelas" name="kelas" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:outline-none">
                    </div>

                    <!-- No WhatsApp yang bisa dihubungi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. WhatsApp *</label>
                        <input type="text" id="edit_no_wa" name="no_wa" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:outline-none font-mono">
                    </div>

                    <!-- Status Kehadiran -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Kehadiran *</label>
                        <select id="edit_status" name="status" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:outline-none bg-white font-medium">
                            <option value="Hadir">Hadir</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                    </div>

                    <!-- Keterangan / Tugas -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan / Pos Tugas</label>
                        <input type="text" id="edit_keterangan" name="keterangan" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-500 focus:outline-none">
                    </div>

                </div>

                <div class="mt-6 flex justify-end space-x-2 border-t border-slate-200 pt-4">
                    <button type="button" onclick="closeModalEdit()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-lg text-xs shadow transition flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <script>
        // Dropdown Profil
        function toggleDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
        }

        window.onclick = function(event) {
            const btn = document.getElementById('profile-btn');
            const dropdown = document.getElementById('profile-dropdown');
            if (btn && dropdown && !btn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        }

        // Modal Tambah
        function openModalTambah() {
            document.getElementById('modalTambah').classList.remove('hidden');
        }
        function closeModalTambah() {
            document.getElementById('modalTambah').classList.add('hidden');
        }

        // Modal Edit
        function openModalEdit(item) {
            document.getElementById('formEdit').action = "{{ url('/absensi-avalis/update') }}/" + item.id;
            document.getElementById('edit_tanggal').value = item.tanggal;
            document.getElementById('edit_nama').value = item.nama;
            document.getElementById('edit_kelas').value = item.kelas;
            document.getElementById('edit_no_wa').value = item.no_wa;
            document.getElementById('edit_shift_jaga').value = item.shift_jaga;
            document.getElementById('edit_status').value = item.status;
            document.getElementById('edit_keterangan').value = item.keterangan || '';
            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function closeModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
        }
    </script>
</body>
</html>
