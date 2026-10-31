<!DOCTYPE html>
<html lang="id">
<head>
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

        <!-- HEADER HALAMAN -->
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Data Nasabah & Rekening</h1>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">Kelola data identitas penabung, status rekening operasional, serta pemantauan saldo riil.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="bukaModalImport()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 py-2 px-3.5 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import CSV
                </button>
                <a href="{{ url('/admin/tambah-nasabah') }}" class="bg-slate-900 hover:bg-slate-800 text-white py-2 px-4 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
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
                            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            <option value="alumni" {{ request('status') === 'alumni' ? 'selected' : '' }}>Alumni</option>
                        </select>
                    </div>

                    <!-- 4. Filter Kategori Nasabah -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Kategori Nasabah</label>
                        <select name="kategori" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all" {{ (!request('kategori') || request('kategori') === 'all') ? 'selected' : '' }}>Semua Kategori</option>
                            <option value="siswa" {{ request('kategori') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ in_array(request('kategori'), ['guru', 'guru_karyawan']) ? 'selected' : '' }}>Guru & Karyawan</option>
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
                                <a href="{{ url('/admin/data-nasabah/'.$n->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition block text-sm">
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
                            <td class="py-3.5 px-4 whitespace-nowrap capitalize font-medium text-slate-700">
                                @if(in_array($n->kategori, ['guru', 'guru_karyawan']))
                                    <span class="bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded text-[10px] font-semibold">
                                        Guru / Karyawan
                                    </span>
                                @elseif($n->kategori === 'alumni')
                                    <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded text-[10px] font-semibold">
                                        Alumni
                                    </span>
                                @else
                                    <span class="bg-slate-100 text-slate-700 border border-slate-200 px-2 py-0.5 rounded text-[10px] font-semibold">
                                        Siswa
                                    </span>
                                @endif
                            </td>

                            <!-- Status Rekening -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if(($n->status ?? 'aktif') === 'aktif')
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold px-2 py-0.5 rounded-full text-[10px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @elseif(($n->status ?? '') === 'nonaktif')
                                    <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200 font-semibold px-2 py-0.5 rounded-full text-[10px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Nonaktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 border border-amber-200 font-semibold px-2 py-0.5 rounded-full text-[10px]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Alumni
                                    </span>
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

                                <!-- Hapus (Modal Konfirmasi Feature 7) -->
                                <button
                                    type="button"
                                    onclick="konfirmasiHapusNasabah({{ $n->id }}, '{{ addslashes($n->nama_siswa) }}', '{{ $n->no_rekening }}', {{ $n->saldo }})"
                                    class="inline-block bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium py-1 px-2 rounded text-[11px] transition border border-rose-200">
                                    Hapus
                                </button>
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
    <div id="modal-konfirmasi" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="p-6">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mb-4" id="modal-icon-container">
                    <svg class="w-6 h-6" id="modal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-900" id="modal-title">Konfirmasi Tindakan</h3>
                <p class="text-xs text-slate-600 mt-2" id="modal-message"></p>

                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs font-mono text-slate-700 mt-4 space-y-1">
                    <div>Nasabah: <strong id="modal-nama">-</strong></div>
                    <div>No. Rekening: <strong id="modal-rek">-</strong></div>
                    <div id="modal-extra-info"></div>
                </div>
            </div>

            <form id="form-konfirmasi" method="POST" action="" class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-2">
                @csrf
                <div id="modal-form-inputs"></div>
                <button type="button" onclick="tutupModalKonfirmasi()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold rounded-lg text-xs transition shadow-sm">
                    Batal
                </button>
                <button type="submit" id="btn-submit-konfirmasi" class="px-4 py-2 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2">
                    <span id="btn-konfirmasi-text">Konfirmasi</span>
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL POP-UP IMPORT CSV -->
    <div id="modal-import" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
            <div class="bg-slate-900 px-6 py-4 border-b border-slate-800">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Data Nasabah (CSV)
                </h3>
            </div>
            <form action="{{ url('/admin/import-nasabah') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="mb-4">
                    <p class="text-xs text-slate-600 mb-2">Sistem akan secara otomatis menghasilkan Nomor Rekening terstandar untuk setiap siswa yang di-import.</p>
                    <a href="{{ url('/admin/template-nasabah') }}" class="inline-flex items-center text-xs text-indigo-600 font-semibold hover:underline">
                        &darr; Unduh Template CSV Resmi
                    </a>
                </div>
                <div class="mb-6">
                    <label class="block text-slate-700 text-xs font-semibold mb-2">Pilih File CSV (.csv / .txt):</label>
                    <input type="file" name="file_csv" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-300 rounded-lg p-2">
                </div>
                <div class="flex justify-end space-x-2 border-t border-slate-100 pt-4">
                    <button type="button" onclick="tutupModalImport()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold rounded-lg text-xs transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition shadow-sm">Proses Import Data</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function bukaModalImport() {
            document.getElementById('modal-import').classList.remove('hidden');
        }
        function tutupModalImport() {
            document.getElementById('modal-import').classList.add('hidden');
        }

        // =====================================================================
        // KONFIRMASI TINDAKAN PENTING (FEATURE 7)
        // =====================================================================
        function konfirmasiUbahStatus(id, nama, noRekening, statusTujuan) {
            const modal = document.getElementById('modal-konfirmasi');
            const title = document.getElementById('modal-title');
            const message = document.getElementById('modal-message');
            const namaEl = document.getElementById('modal-nama');
            const rekEl = document.getElementById('modal-rek');
            const extraEl = document.getElementById('modal-extra-info');
            const form = document.getElementById('form-konfirmasi');
            const inputsContainer = document.getElementById('modal-form-inputs');
            const btn = document.getElementById('btn-submit-konfirmasi');
            const btnText = document.getElementById('btn-konfirmasi-text');
            const iconContainer = document.getElementById('modal-icon-container');

            form.action = '{{ url("/admin/nasabah/ubah-status") }}/' + id;
            inputsContainer.innerHTML = '<input type="hidden" name="status" value="' + statusTujuan + '">';
            namaEl.textContent = nama;
            rekEl.textContent = noRekening;
            extraEl.innerHTML = '';

            if (statusTujuan === 'nonaktif') {
                title.textContent = 'Nonaktifkan Rekening Nasabah?';
                message.textContent = 'Apakah Anda yakin ingin menonaktifkan rekening milik ' + nama + ' (' + noRekening + ')? Nasabah tidak dapat melakukan transaksi tarik, setor, maupun mutasi selama status nonaktif.';
                btn.className = 'px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2';
                btnText.textContent = 'Ya, Nonaktifkan Rekening';
                iconContainer.className = 'w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4';
            } else {
                title.textContent = 'Aktifkan Kembali Rekening Nasabah?';
                message.textContent = 'Apakah Anda yakin ingin mengaktifkan kembali rekening milik ' + nama + ' (' + noRekening + ')? Rekening akan dapat digunakan kembali secara penuh.';
                btn.className = 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2';
                btnText.textContent = 'Ya, Aktifkan Rekening';
                iconContainer.className = 'w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4';
            }

            modal.classList.remove('hidden');
        }

        function konfirmasiHapusNasabah(id, nama, noRekening, saldo) {
            const modal = document.getElementById('modal-konfirmasi');
            const title = document.getElementById('modal-title');
            const message = document.getElementById('modal-message');
            const namaEl = document.getElementById('modal-nama');
            const rekEl = document.getElementById('modal-rek');
            const extraEl = document.getElementById('modal-extra-info');
            const form = document.getElementById('form-konfirmasi');
            const inputsContainer = document.getElementById('modal-form-inputs');
            const btn = document.getElementById('btn-submit-konfirmasi');
            const btnText = document.getElementById('btn-konfirmasi-text');
            const iconContainer = document.getElementById('modal-icon-container');

            form.action = '{{ url("/admin/hapus-nasabah") }}/' + id;
            inputsContainer.innerHTML = '';
            namaEl.textContent = nama;
            rekEl.textContent = noRekening;

            if (saldo > 0) {
                extraEl.innerHTML = '<span class="text-rose-600 font-bold">PERINGATAN: Rekening ini masih memiliki saldo sebesar Rp ' + Number(saldo).toLocaleString('id-ID') + '! Sangat disarankan menonaktifkan rekening daripada menghapus.</span>';
            } else {
                extraEl.innerHTML = '<span class="text-slate-500">Saldo Rekening: Rp 0</span>';
            }

            title.textContent = 'Hapus Data Nasabah?';
            message.textContent = 'Tindakan ini akan menghapus data nasabah secara permanen. Pastikan tidak ada transaksi aktif yang bergantung pada data nasabah ini.';
            btn.className = 'px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2';
            btnText.textContent = 'Ya, Hapus Nasabah';
            iconContainer.className = 'w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4';

            modal.classList.remove('hidden');
        }

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
    </script>
</body>
</html>
