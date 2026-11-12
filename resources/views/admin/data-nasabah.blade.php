<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Favicon / Logo Web -->
    <link rel="icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Nasabah & Rekening - Administrator</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">
    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 mb-6 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 mb-6 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center text-rose-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 mb-6 rounded-xl shadow-sm">
            <ul class="list-disc list-inside text-xs font-semibold space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @php
            $currentUserRoles = array_map('trim', explode(',', strtolower(Auth::user()->role ?? '')));
            $isAdminRole = in_array('admin', $currentUserRoles, true);
        @endphp

        <!-- HEADER HALAMAN -->
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Data Nasabah & Rekening</h1>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">Kelola data identitas penabung, status rekening operasional, serta pemantauan saldo riil.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($isAdminRole)
                    <button type="button"
                            onclick="konfirmasiNonaktifkanSemuaNasabah({{ $totalAktif }})"
                            @disabled($totalAktif === 0)
                            class="bg-amber-50 border border-amber-300 text-amber-800 hover:bg-amber-100 disabled:opacity-50 disabled:cursor-not-allowed py-2 px-3.5 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span>Nonaktifkan Semua Nasabah</span>
                    </button>
                    <button type="button"
                            onclick="konfirmasiHapusSemuaNasabah({{ $totalSemua }}, {{ $totalAktif }}, {{ $totalNonaktif }}, {{ $totalAlumni }}, {{ $totalSaldoSemua ?? 0 }})"
                            @disabled($totalSemua === 0)
                            class="bg-rose-50 border border-rose-300 text-rose-700 hover:bg-rose-100 disabled:opacity-50 disabled:cursor-not-allowed py-2 px-3.5 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Hapus Semua Nasabah</span>
                    </button>
                @endif
                <button onclick="bukaModalImport()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 py-2 px-3.5 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import Excel / CSV
                </button>
                <a href="{{ url('/admin/tambah-nasabah') }}" class="bg-slate-900 hover:bg-slate-800 text-white py-2 px-4 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Nasabah
                </a>
            </div>
        </div>

        <!-- STATS BADGES RINGKASAN -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <a href="{{ url('/admin/data-nasabah?status=all') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-slate-400 transition flex items-center justify-between {{ (!request('status') || request('status') === 'all') ? 'ring-2 ring-slate-900/10' : '' }}">
                <div>
                    <span class="text-[11px] text-slate-500 font-medium block">Total Seluruh Nasabah</span>
                    <span class="text-lg font-bold text-slate-900">{{ number_format($totalSemua, 0, ',', '.') }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-xs">
                    ALL
                </div>
            </a>

            <a href="{{ url('/admin/data-nasabah?status=aktif') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-emerald-300 transition flex items-center justify-between {{ request('status') === 'aktif' ? 'ring-2 ring-emerald-500/20 border-emerald-300' : '' }}">
                <div>
                    <span class="text-[11px] text-emerald-600 font-medium block">Rekening Aktif</span>
                    <span class="text-lg font-bold text-emerald-700">{{ number_format($totalAktif, 0, ',', '.') }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </a>

            <a href="{{ url('/admin/data-nasabah?status=nonaktif') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-rose-300 transition flex items-center justify-between {{ request('status') === 'nonaktif' ? 'ring-2 ring-rose-500/20 border-rose-300' : '' }}">
                <div>
                    <span class="text-[11px] text-rose-600 font-medium block">Rekening Nonaktif</span>
                    <span class="text-lg font-bold text-rose-700">{{ number_format($totalNonaktif, 0, ',', '.') }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
            </a>

            <a href="{{ url('/admin/data-nasabah?status=alumni') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-amber-300 transition flex items-center justify-between {{ request('status') === 'alumni' ? 'ring-2 ring-amber-500/20 border-amber-300' : '' }}">
                <div>
                    <span class="text-[11px] text-amber-600 font-medium block">Arsip Alumni</span>
                    <span class="text-lg font-bold text-amber-700">{{ number_format($totalAlumni, 0, ',', '.') }}</span>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
            </a>
        </div>

        <!-- ========================================================================= -->
        <!-- DAFTAR PERMOHONAN RESET PIN NASABAH (ADMIN APPROVE, NASABAH SET PIN MANDIRI) -->
        <!-- ========================================================================= -->
        @if(isset($pinResetRequests) && $pinResetRequests->count() > 0)
        <div class="bg-amber-50/80 border border-amber-300 rounded-2xl p-5 mb-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-3 border-b border-amber-200">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ $pinResetRequests->count() }}
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-amber-950">Permohonan Reset PIN Nasabah (Menunggu Persetujuan Admin)</h2>
                        <p class="text-xs text-amber-800">Setujui permohonan setelah memverifikasi siswa. PIN baru tetap akan dibuat oleh nasabah secara mandiri.</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto bg-white rounded-xl border border-amber-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-amber-100/60 text-amber-950 font-semibold border-b border-amber-200">
                        <tr>
                            <th class="py-2.5 px-4">Waktu Ajuan</th>
                            <th class="py-2.5 px-4">Nasabah</th>
                            <th class="py-2.5 px-4">Rekening / NIS</th>
                            <th class="py-2.5 px-4">Alasan Lupa PIN</th>
                            <th class="py-2.5 px-4 text-center">Tindakan Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100 text-slate-700">
                        @foreach($pinResetRequests as $reqPin)
                        <tr>
                            <td class="py-2.5 px-4 tabular-nums text-slate-500">{{ $reqPin->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900">{{ $reqPin->nama_siswa }}</td>
                            <td class="py-2.5 px-4 font-mono text-slate-700">{{ $reqPin->no_rekening }} (NIS: {{ $reqPin->nis }})</td>
                            <td class="py-2.5 px-4 italic text-slate-600">"{{ $reqPin->alasan }}"</td>
                            <td class="py-2.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1.5">
                                    <form action="{{ route('admin.pin-reset.approve', $reqPin->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-1 px-3 rounded-lg text-[11px] transition shadow-sm">
                                            Setujui Reset PIN
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.pin-reset.reject', $reqPin->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold py-1 px-2.5 rounded-lg text-[11px] transition">
                                            Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- ========================================================================= -->
        <!-- FORM FILTER & PENCARIAN TERPADU (FEATURE 1) -->
        <!-- ========================================================================= -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form method="GET" action="{{ url('/admin/data-nasabah') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">

                    <!-- 1. Pencarian Bebas -->
                    <div class="lg:col-span-1">
                        <label class="block font-semibold text-slate-700 mb-1.5">Kata Kunci Pencarian</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <input
                                type="text"
                                name="cari"
                                value="{{ request('cari') }}"
                                placeholder="Nama, No Rekening, NIS..."
                                class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs">
                        </div>
                    </div>

                    <!-- 2. Filter Kelas / Rombel -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Kelas / Rombongan Belajar</label>
                        <select name="kelas" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all">Semua Kelas</option>
                            @foreach($daftarKelas as $kls)
                                <option value="{{ $kls }}" {{ request('kelas') === $kls ? 'selected' : '' }}>
                                    {{ $kls }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Filter Status Rekening -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Status Rekening</label>
                        <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="aktif" {{ request('status', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif / Siswa Baru Kelas 10</option>
                            <option value="alumni" {{ request('status') === 'alumni' ? 'selected' : '' }}>Alumni (Lulus Kelas 12)</option>
                            <option value="terhapus" {{ request('status') === 'terhapus' ? 'selected' : '' }}>Arsip Alumni Dihapus ({{ $totalTerhapus ?? 0 }})</option>
                        </select>
                    </div>

                    <!-- 4. Filter Kategori Nasabah -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Kategori Nasabah</label>
                        <select name="kategori" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all" {{ (!request('kategori') || request('kategori') === 'all') ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="siswa" {{ request('kategori') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="alumni" {{ request('kategori') === 'alumni' ? 'selected' : '' }}>Alumni</option>
                        </select>
                    </div>
                </div>

                <!-- Bar Tombol Aksi Filter & Reset -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                    <div class="text-slate-600 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Ditemukan <strong>{{ number_format($totalHasil, 0, ',', '.') }}</strong> nasabah</span>
                        @if(request()->hasAny(['cari', 'kelas', 'status', 'kategori']))
                            <span class="text-slate-400">&bull; Filter aktif diterapkan</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ url('/admin/data-nasabah') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Reset Filter
                        </a>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg transition shadow-sm inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TABEL DATA NASABAH (PAGINATED & DATABASE DRIVEN) -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left text-xs">
                    <thead class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4">No. Rekening</th>
                            <th class="py-3.5 px-4">NIS / NIP & Nama Lengkap</th>
                            <th class="py-3.5 px-4">Kelas & Jurusan</th>
                            <th class="py-3.5 px-4">Kategori</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Saldo Tabungan</th>
                            <th class="py-3.5 px-4 text-center">Aksi Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($nasabah as $n)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- No Rekening -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ url('/admin/data-nasabah/'.$n->id) }}" class="font-mono font-bold text-slate-900 bg-slate-100 hover:bg-slate-200 px-2 py-1 rounded border border-slate-200 transition inline-block">
                                    {{ $n->no_rekening ?? $n->nis }}
                                </a>
                            </td>

                            <!-- NIS & Nama -->
                            <td class="py-3.5 px-4">
                                <a href="{{ url('/admin/data-nasabah/'.$n->id) }}" class="font-bold text-slate-900 hover:text-teal-600 transition block text-sm">
                                    {{ $n->nama_siswa }}
                                </a>
                                <span class="text-slate-500 font-mono text-[11px]">NIS: {{ $n->nis }}</span>
                            </td>

                            <!-- Kelas & Jurusan -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-800">{{ $n->kelas }}</span>
                                <span class="text-slate-500 block text-[11px]">{{ $n->jurusan }}</span>
                            </td>

                            <!-- Kategori -->
                            <td class="py-3.5 px-4 whitespace-nowrap capitalize text-xs font-medium text-slate-700">
                                @if(in_array($n->kategori, ['guru', 'guru_karyawan']))
                                    Guru / Karyawan
                                @elseif($n->kategori === 'alumni')
                                    Alumni
                                @else
                                    Siswa
                                @endif
                            </td>

                            <!-- Status Rekening -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if(($n->status ?? 'aktif') === 'aktif')
                                    <span class="text-xs font-semibold text-emerald-700">Aktif</span>
                                @elseif(($n->status ?? '') === 'nonaktif')
                                    <span class="text-xs font-semibold text-rose-700">Nonaktif</span>
                                @else
                                    <span class="text-xs font-semibold text-amber-700">Alumni</span>
                                @endif
                            </td>

                            <!-- Saldo Tabungan -->
                            <td class="py-3.5 px-4 text-right font-bold text-sm text-emerald-700 font-mono whitespace-nowrap">
                                Rp {{ number_format($n->saldo, 0, ',', '.') }}
                            </td>

                            <!-- Aksi Lengkap -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap space-x-1">
                                <!-- Tombol Detail Nasabah (Feature 2) -->
                                <a href="{{ url('/admin/data-nasabah/'.$n->id) }}" class="inline-block bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold py-1 px-2.5 rounded text-[11px] transition shadow-sm" title="Buka Detail Nasabah">
                                    Detail
                                </a>

                                <!-- Tombol Buku Tabungan -->
                                <a href="{{ url('/buku-tabungan/'.$n->id) }}" class="inline-block bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-1 px-2.5 rounded text-[11px] transition shadow-sm" title="Buku Tabungan">
                                    Buku
                                </a>

                                @if(method_exists($n, 'trashed') && $n->trashed())
                                    <form action="{{ route('admin.nasabah.restore', $n->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-block bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-1 px-2.5 rounded text-[11px] transition shadow-sm">
                                            Pulihkan Akun
                                        </button>
                                    </form>
                                @else
                                    <!-- Tombol Ubah Status (Modal Konfirmasi Feature 7) -->
                                    @if(($n->status ?? 'aktif') === 'aktif')
                                        <button
                                            type="button"
                                            onclick="konfirmasiUbahStatus({{ $n->id }}, '{{ addslashes($n->nama_siswa) }}', '{{ $n->no_rekening }}', 'nonaktif')"
                                            class="inline-block bg-amber-50 hover:bg-amber-100 text-amber-800 font-medium py-1 px-2 rounded text-[11px] transition border border-amber-300"
                                            title="Nonaktifkan Rekening">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            onclick="konfirmasiUbahStatus({{ $n->id }}, '{{ addslashes($n->nama_siswa) }}', '{{ $n->no_rekening }}', 'aktif')"
                                            class="inline-block bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-medium py-1 px-2 rounded text-[11px] transition border border-emerald-300"
                                            title="Aktifkan Rekening">
                                            Aktifkan
                                        </button>
                                    @endif

                                    <!-- Edit -->
                                    <a href="{{ url('/admin/edit-nasabah/'.$n->id) }}" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-1 px-2 rounded text-[11px] transition">Edit</a>

                                    @if (($n->status ?? '') === 'nonaktif')
                                        <!-- Hapus (Hanya muncul khusus untuk nasabah berstatus nonaktif) -->
                                        <button
                                            type="button"
                                            onclick="konfirmasiHapusNasabah({{ $n->id }}, '{{ addslashes($n->nama_siswa) }}', '{{ $n->no_rekening }}', {{ $n->saldo }})"
                                            class="inline-block bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium py-1 px-2 rounded text-[11px] transition border border-rose-200"
                                            title="Hapus / Arsipkan Data Nasabah Nonaktif">
                                            Hapus
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <!-- EMPTY STATE (FEATURE 1) -->
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="max-w-md mx-auto">
                                    <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Tidak Ada Data Nasabah Ditemukan</h3>
                                    <p class="text-xs text-slate-500 mt-1 mb-5">
                                        Tidak ada nasabah yang sesuai dengan kriteria filter dan pencarian yang Anda pilih. Coba sesuaikan kata kunci atau bersihkan filter.
                                    </p>
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ url('/admin/data-nasabah') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition shadow-sm inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Reset Seluruh Filter
                                        </a>
                                        <a href="{{ url('/admin/tambah-nasabah') }}" class="px-3.5 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold rounded-lg text-xs transition shadow-sm">
                                            + Tambah Nasabah Baru
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION KONTROL (FEATURE 1) -->
            @if($nasabah->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-slate-500">
                    Menampilkan <strong>{{ $nasabah->firstItem() ?? 0 }}</strong> sampai <strong>{{ $nasabah->lastItem() ?? 0 }}</strong> dari <strong>{{ $nasabah->total() }}</strong> nasabah
                </span>
                <div>
                    {{ $nasabah->links() }}
                </div>
            </div>
            @endif
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI TINDAKAN PENTING (FEATURE 7) -->
    <!-- ========================================================================= -->
    <div id="modal-konfirmasi" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
                <div class="p-6">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-4" id="modal-icon-container">
                        <svg class="w-6 h-6" id="modal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900" id="modal-title">Konfirmasi Tindakan</h3>
                    <p class="text-xs text-slate-600 mt-2" id="modal-message"></p>

                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs font-mono text-slate-700 mt-4 space-y-1">
                        <div><span id="modal-label-1">Nasabah</span>: <strong id="modal-nama">-</strong></div>
                        <div><span id="modal-label-2">No. Rekening</span>: <strong id="modal-rek">-</strong></div>
                        <div id="modal-extra-info"></div>
                    </div>
                </div>

                <form id="form-konfirmasi" method="POST" action="">
                    @csrf
                    <div id="modal-form-inputs" class="px-6 pb-4 space-y-3 empty:hidden"></div>
                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="tutupModalKonfirmasi()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold rounded-lg text-xs transition shadow-sm">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-konfirmasi" class="px-4 py-2 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2">
                            <span id="btn-konfirmasi-text">Konfirmasi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL POP-UP IMPORT EXCEL / CSV -->
    <div id="modal-import" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
                <div class="bg-slate-900 px-6 py-4 border-b border-slate-800">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import Data Nasabah (Excel / CSV)
                    </h3>
                </div>
                <form id="form-import-nasabah" action="{{ url('/admin/import-nasabah') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <p class="text-xs text-slate-600 mb-2">Sistem otomatis mendeteksi file Excel (.xlsx, .xls) maupun CSV (.csv, .txt), membaca kelas & jurusan per rombel, serta menerbitkan Nomor Rekening otomatis.</p>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2.5 mb-2.5 text-[11px] text-emerald-800 flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span><strong>Mendukung Format Daftar Hadir Siswa:</strong> Anda bisa langsung mengunggah dokumen Daftar Hadir / Absensi Siswa resmi sekolah (.xlsx / .xls / .csv) tanpa perlu mengubah format secara manual!</span>
                        </div>
                        <a href="{{ url('/admin/template-nasabah') }}" class="inline-flex items-center text-xs text-teal-600 font-semibold hover:underline">
                            &darr; Unduh Template CSV / Excel Resmi Standar
                        </a>
                    </div>
                    <div class="mb-4">
                        <label class="block text-slate-700 text-xs font-semibold mb-1.5">Status Akun Siswa yang Diimpor:</label>
                        <select name="status_default" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10">
                            <option value="auto">Otomatis (Murid Baru Kelas 10 / X = Belum Aktif, Kelas 11 & 12 = Aktif)</option>
                            <option value="nonaktif">Belum Aktif / Nonaktif (Khusus Import Siswa Baru Kelas 10)</option>
                            <option value="aktif">Langsung Aktif</option>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Pilih <strong>Belum Aktif</strong> atau <strong>Otomatis</strong> untuk mengimpor data murid baru kelas 10 agar akun tidak langsung aktif sebelum diverifikasi.</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-slate-700 text-xs font-semibold mb-2">Pilih Dokumen Excel / CSV (.xlsx, .xls, .csv, .txt):</label>
                        <input type="file" name="file_csv" required accept=".xlsx,.xls,.csv,.txt" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-300 rounded-lg p-2">
                    </div>
                    <div class="flex justify-end space-x-2 border-t border-slate-100 pt-4">
                        <button type="button" id="btn-cancel-import" onclick="tutupModalImport()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold rounded-lg text-xs transition">Batal</button>
                        <button type="submit" id="btn-submit-import" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition shadow-sm inline-flex items-center gap-2">
                            <span id="btn-import-text">Proses Import Data</span>
                            <span id="btn-import-spinner" class="hidden items-center gap-2">
                                <svg class="animate-spin -ml-1 mr-1 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Mengimpor Data...</span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaModalImport() {
            document.getElementById('modal-import').classList.remove('hidden');
        }
        function tutupModalImport() {
            document.getElementById('modal-import').classList.add('hidden');
            const btn = document.getElementById('btn-submit-import');
            const btnCancel = document.getElementById('btn-cancel-import');
            const text = document.getElementById('btn-import-text');
            const spinner = document.getElementById('btn-import-spinner');
            if (btn) {
                btn.removeAttribute('data-submitting');
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
            if (btnCancel) btnCancel.classList.remove('pointer-events-none', 'opacity-50');
            if (text) text.classList.remove('hidden');
            if (spinner) {
                spinner.classList.add('hidden');
                spinner.classList.remove('inline-flex');
            }
        }

        // =====================================================================
        // KONFIRMASI TINDAKAN PENTING (FEATURE 7 & AKSI MASSAL ADMIN)
        // =====================================================================
        function bukaModalKonfirmasi(opts) {
            const modal = document.getElementById('modal-konfirmasi');
            const title = document.getElementById('modal-title');
            const message = document.getElementById('modal-message');
            const label1El = document.getElementById('modal-label-1');
            const label2El = document.getElementById('modal-label-2');
            const namaEl = document.getElementById('modal-nama');
            const rekEl = document.getElementById('modal-rek');
            const extraEl = document.getElementById('modal-extra-info');
            const form = document.getElementById('form-konfirmasi');
            const inputsContainer = document.getElementById('modal-form-inputs');
            const btn = document.getElementById('btn-submit-konfirmasi');
            const btnText = document.getElementById('btn-konfirmasi-text');
            const iconContainer = document.getElementById('modal-icon-container');

            form.action = opts.action;
            inputsContainer.innerHTML = opts.inputsHtml || '';
            label1El.textContent = opts.label1 || 'Nasabah';
            label2El.textContent = opts.label2 || 'No. Rekening';
            namaEl.textContent = opts.val1 || '-';
            rekEl.textContent = opts.val2 || '-';
            extraEl.innerHTML = opts.extraHtml || '';

            title.textContent = opts.title;
            message.textContent = opts.message;
            btn.className = opts.btnClass;
            btn.disabled = Boolean(opts.submitDisabled);
            btnText.textContent = opts.btnText;
            iconContainer.className = opts.iconClass;

            modal.classList.remove('hidden');
        }

        function konfirmasiUbahStatus(id, nama, noRekening, statusTujuan) {
            const isNonaktif = statusTujuan === 'nonaktif';

            bukaModalKonfirmasi({
                action: '{{ url("/admin/nasabah/ubah-status") }}/' + id,
                inputsHtml: '<input type="hidden" name="status" value="' + (isNonaktif ? 'nonaktif' : 'aktif') + '">',
                val1: nama,
                val2: noRekening,
                title: isNonaktif ? 'Nonaktifkan Rekening Nasabah?' : 'Aktifkan Kembali Rekening Nasabah?',
                message: isNonaktif
                    ? 'Apakah Anda yakin ingin menonaktifkan rekening milik ' + nama + ' (' + noRekening + ')? Nasabah tidak dapat melakukan transaksi tarik, setor, maupun mutasi selama status nonaktif.'
                    : 'Apakah Anda yakin ingin mengaktifkan kembali rekening milik ' + nama + ' (' + noRekening + ')? Rekening akan dapat digunakan kembali secara penuh.',
                btnClass: isNonaktif
                    ? 'px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2'
                    : 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2',
                btnText: isNonaktif ? 'Ya, Nonaktifkan Rekening' : 'Ya, Aktifkan Rekening',
                iconClass: isNonaktif
                    ? 'w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4'
                    : 'w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4'
            });
        }

        function konfirmasiHapusNasabah(id, nama, noRekening, saldo) {
            const extraHtml = saldo > 0
                ? '<span class="text-rose-600 font-bold">PERINGATAN: Rekening ini masih memiliki saldo sebesar Rp ' + Number(saldo).toLocaleString('id-ID') + '! Sangat disarankan menonaktifkan rekening daripada menghapus.</span>'
                : '<span class="text-slate-500">Saldo Rekening: Rp 0</span>';

            bukaModalKonfirmasi({
                action: '{{ url("/admin/hapus-nasabah") }}/' + id,
                inputsHtml: `
                    <div class="text-left">
                        <label class="block text-slate-700 text-xs font-semibold mb-1">Alasan Penghapusan / Arsip <span class="text-slate-400 font-normal">(Dicatat di Jejak Audit)</span></label>
                        <input type="text" name="alasan" placeholder="Contoh: Siswa sudah lulus / mutasi sekolah" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none">
                    </div>
                `,
                val1: nama,
                val2: noRekening,
                extraHtml: extraHtml,
                title: 'Hapus / Arsipkan Data Nasabah Nonaktif?',
                message: 'Data nasabah nonaktif ini akan diarsipkan (Soft Delete), namun seluruh riwayat mutasi transaksi di buku tabungan dan kas TETAP AMAN tersimpan utuh di sistem.',
                btnClass: 'px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2',
                btnText: 'Ya, Hapus & Simpan Riwayat',
                iconClass: 'w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4'
            });
        }

        @if($isAdminRole)
        function konfirmasiNonaktifkanSemuaNasabah(totalAktif) {
            bukaModalKonfirmasi({
                action: '{{ route("admin.nasabah.deactivate-all") }}',
                inputsHtml: `
                    <div class="text-left">
                        <label class="block text-slate-700 text-xs font-semibold mb-1">Alasan Penonaktifan Massal <span class="text-slate-400 font-normal">(Opsional, dicatat di Jejak Audit)</span></label>
                        <input type="text" name="alasan" placeholder="Contoh: Evaluasi tahun ajaran / persiapan arsip massal" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                    </div>
                `,
                label1: 'Cakupan Data',
                val1: 'Seluruh Rekening Berstatus Aktif',
                label2: 'Jumlah Terdampak',
                val2: Number(totalAktif).toLocaleString('id-ID') + ' Nasabah Aktif',
                extraHtml: '<span class="text-amber-700 font-semibold">Rekening yang dinonaktifkan tetap tersimpan beserta saldonya dan tidak dapat bertransaksi sampai diaktifkan kembali.</span>',
                title: 'Nonaktifkan Semua Nasabah Aktif?',
                message: 'Seluruh nasabah berstatus aktif akan diubah menjadi Nonaktif. Langkah ini juga diperlukan sebelum Anda dapat menghapus/mengarsipkan seluruh data nasabah.',
                btnClass: 'px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2',
                btnText: 'Ya, Nonaktifkan Semua',
                iconClass: 'w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mb-4',
                submitDisabled: totalAktif <= 0
            });
        }

        function konfirmasiHapusSemuaNasabah(totalSemua, totalAktif, totalNonaktif, totalAlumni, totalSaldo) {
            const hasAktif = Number(totalAktif) > 0;
            const rincian = Number(totalNonaktif).toLocaleString('id-ID') + ' Nonaktif · ' + Number(totalAlumni).toLocaleString('id-ID') + ' Alumni';

            let extraHtml = '<div class="text-slate-600">Rincian: ' + rincian + '</div>';
            if (hasAktif) {
                extraHtml += '<div class="mt-2 p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 font-semibold">Masih terdapat ' + Number(totalAktif).toLocaleString('id-ID') + ' nasabah berstatus Aktif. Klik tombol "Nonaktifkan Semua Nasabah" terlebih dahulu sebelum menghapus seluruh data nasabah.</div>';
            } else if (Number(totalSaldo) > 0) {
                extraHtml += '<div class="mt-2 p-2 rounded bg-amber-50 border border-amber-200 text-amber-800 font-semibold">PERINGATAN: Total saldo tabungan seluruh nasabah saat ini sebesar Rp ' + Number(totalSaldo).toLocaleString('id-ID') + '.</div>';
            }

            const inputsHtml = hasAktif
                ? ''
                : `
                    <div class="text-left">
                        <label class="block text-slate-700 text-xs font-semibold mb-1">Alasan Penghapusan Massal <span class="text-slate-400 font-normal">(Dicatat di Jejak Audit)</span></label>
                        <input type="text" name="alasan" placeholder="Contoh: Pembersihan data nasabah sebelum import baru" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none">
                    </div>
                    <div class="text-left">
                        <label class="block text-slate-700 text-xs font-semibold mb-1">Ketik <span class="font-mono text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded">HAPUS SEMUA</span> untuk konfirmasi:</label>
                        <input type="text" id="input-konfirmasi-hapus-semua" name="konfirmasi" required autocomplete="off" placeholder="Ketik HAPUS SEMUA" oninput="toggleTombolHapusSemua(this.value)" class="w-full px-3 py-2 border border-rose-300 rounded-lg text-xs font-mono focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:outline-none">
                    </div>
                `;

            bukaModalKonfirmasi({
                action: '{{ route("admin.nasabah.destroy-all") }}',
                inputsHtml: inputsHtml,
                label1: 'Cakupan Data',
                val1: 'Seluruh Nasabah Nonaktif / Alumni',
                label2: 'Total Nasabah',
                val2: Number(totalSemua).toLocaleString('id-ID') + ' Nasabah (' + Number(totalAktif).toLocaleString('id-ID') + ' Aktif)',
                extraHtml: extraHtml,
                title: 'Hapus / Arsipkan Semua Nasabah?',
                message: 'Seluruh data nasabah berstatus nonaktif/alumni akan diarsipkan (Soft Delete) agar riwayat mutasi transaksi tetap aman. Anda dapat memulihkannya lewat filter Arsip Alumni Dihapus.',
                btnClass: 'px-4 py-2 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2',
                btnText: 'Ya, Hapus Semua Nasabah',
                iconClass: 'w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4',
                submitDisabled: true
            });
        }

        function toggleTombolHapusSemua(nilai) {
            const btn = document.getElementById('btn-submit-konfirmasi');
            if (btn) {
                btn.disabled = nilai.trim() !== 'HAPUS SEMUA';
            }
        }
        @endif

        function tutupModalKonfirmasi() {
            document.getElementById('modal-konfirmasi').classList.add('hidden');
        }

        // Cegah Double Submit
        document.getElementById('form-konfirmasi').addEventListener('submit', function () {
            const btn = document.getElementById('btn-submit-konfirmasi');
            const btnText = document.getElementById('btn-konfirmasi-text');
            btn.disabled = true;
            btnText.textContent = 'Memproses...';
        });

        // Cegah Double Submit & Tampilkan Spinner pada Form Import
        const formImport = document.getElementById('form-import-nasabah');
        if (formImport) {
            formImport.addEventListener('submit', function (e) {
                const btn = document.getElementById('btn-submit-import');
                const btnCancel = document.getElementById('btn-cancel-import');
                const text = document.getElementById('btn-import-text');
                const spinner = document.getElementById('btn-import-spinner');

                if (btn && btn.dataset.submitting === 'true') {
                    e.preventDefault();
                    return false;
                }

                if (btn) {
                    btn.dataset.submitting = 'true';
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                }
                if (btnCancel) btnCancel.classList.add('pointer-events-none', 'opacity-50');
                if (text) text.classList.add('hidden');
                if (spinner) {
                    spinner.classList.remove('hidden');
                    spinner.classList.add('inline-flex');
                }
            });
        }
    </script>
</body>
</html>

