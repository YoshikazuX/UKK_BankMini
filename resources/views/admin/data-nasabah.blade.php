<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Nasabah - Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans">
    <!-- NAVBAR ADMIN KONSISTEN -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>
                <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                    <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Manajemen Nasabah</a>
                    <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Petugas Avalis</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                    <a href="{{ url('/admin/audit') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jejak Audit</a>
                    <a href="{{ url('/admin/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                        <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                        Absensi
                    </a>
                    <a href="{{ url('/admin/konfigurasi') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Konfigurasi</a>

                    <div class="relative ml-2 shrink-0">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600 whitespace-nowrap">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline whitespace-nowrap">{{ Auth::user()->nama_petugas }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">ADMINISTRATOR</span>
                            </div>
                            <a href="{{ url('/admin/profil') }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat & Edit Profil
                            </a>
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

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded shadow-sm font-medium flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded shadow-sm font-bold">
            {{ session('error') }}
        </div>
        @endif

        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Nasabah & Rekening</h1>
                <p class="text-slate-600 text-sm mt-1">Format Penomoran Rekening Armadillo: <strong>(Kode Jurusan - No Urut)</strong></p>
            </div>
            <div class="flex flex-wrap space-x-2">
                <button onclick="bukaModalImport()" class="bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-3.5 rounded-lg text-xs font-bold shadow transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import CSV (Auto-Rekening)
                </button>
                <a href="{{ url('/admin/tambah-nasabah') }}" class="bg-slate-800 hover:bg-slate-900 text-white py-2 px-4 rounded-lg text-xs font-bold shadow transition flex items-center">
                    + Tambah Nasabah Manual
                </a>
            </div>
        </div>

        <!-- FILTER TAB STATUS SISWA (SOLUSI DATABASE BLOAT ALUMNI) -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-wrap justify-between items-center gap-4">
            <!-- Tabs Filter Status -->
            <div class="flex space-x-2">
                <a href="{{ url('/admin/data-nasabah?status=aktif') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ (request('status', 'aktif') === 'aktif') ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Siswa Aktif ({{ $totalAktif }})
                </a>
                <a href="{{ url('/admin/data-nasabah?status=alumni') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ (request('status') === 'alumni') ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Arsip Alumni / Lulusan ({{ $totalAlumni }})
                </a>
                <a href="{{ url('/admin/data-nasabah?status=all') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ (request('status') === 'all') ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Data Nasabah
                </a>
            </div>

            <!-- Form Cari Cepat -->
            <form method="GET" action="{{ url('/admin/data-nasabah') }}" class="flex items-center space-x-2 text-xs">
                <input type="hidden" name="status" value="{{ request('status', 'aktif') }}">
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari NIS / Nama / No Rek..." class="border border-slate-300 rounded-lg px-3 py-1.5 w-52 focus:outline-none focus:ring-1 focus:ring-slate-500">
                <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white font-semibold px-3 py-1.5 rounded-lg">Cari</button>
            </form>
        </div>

        <!-- TABEL DATA NASABAH -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-slate-100 text-slate-700 text-xs uppercase font-bold">
                        <tr>
                            <th class="py-3 px-4 border-b border-slate-200">No. Rekening</th>
                            <th class="py-3 px-4 border-b border-slate-200">NIS & Nama Siswa</th>
                            <th class="py-3 px-4 border-b border-slate-200">Kelas & Jurusan</th>
                            <th class="py-3 px-4 border-b border-slate-200">Kategori</th>
                            <th class="py-3 px-4 border-b border-slate-200">Status</th>
                            <th class="py-3 px-4 border-b border-slate-200 text-right">Saldo</th>
                            <th class="py-3 px-4 border-b border-slate-200 text-center">Aksi & Buku Tabungan</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 text-xs">
                        @forelse ($nasabah as $n)
                        <tr class="hover:bg-slate-50 border-b border-slate-100 transition">
                            <!-- No Rekening Armadillo -->
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $n->no_rekening ?? $n->nis }}
                                </span>
                            </td>

                            <!-- NIS & Nama -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-sm text-slate-900 block">{{ $n->nama_siswa }}</span>
                                <span class="text-slate-500 font-mono text-[11px]">NIS: {{ $n->nis }}</span>
                            </td>

                            <!-- Kelas & Jurusan -->
                            <td class="py-3 px-4 font-medium">{{ $n->kelas }} - {{ $n->jurusan }}</td>

                            <!-- Kategori -->
                            <td class="py-3 px-4 uppercase text-[11px] font-bold text-slate-600">
                                {{ $n->kategori ?? 'Siswa' }}
                            </td>

                            <!-- Status (Aktif vs Alumni) -->
                            <td class="py-3 px-4">
                                @if(($n->status ?? 'aktif') === 'aktif')
                                    <span class="bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded text-[10px] uppercase">
                                        Aktif
                                    </span>
                                @else
                                    <span class="bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded text-[10px] uppercase">
                                        Alumni
                                    </span>
                                @endif
                            </td>

                            <!-- Saldo -->
                            <td class="py-3 px-4 text-right font-bold text-sm text-emerald-700 font-mono">
                                Rp {{ number_format($n->saldo, 0, ',', '.') }}
                            </td>

                            <!-- Aksi Lengkap -->
                            <td class="py-3 px-4 text-center whitespace-nowrap space-x-1">
                                <!-- Tombol Buka Buku Tabungan -->
                                <a href="{{ url('/buku-tabungan/'.$n->id) }}" class="inline-block bg-blue-800 hover:bg-blue-900 text-white font-bold py-1 px-2.5 rounded text-[11px] transition shadow-sm" title="Buku Tabungan / Buku Besar Nasabah">
                                    Buku Tabungan
                                </a>

                                <!-- Tombol Ubah Status Alumni -->
                                <form action="{{ url('/admin/nasabah/ubah-status/'.$n->id) }}" method="POST" class="inline">
                                    @csrf
                                    @if(($n->status ?? 'aktif') === 'aktif')
                                        <input type="hidden" name="status" value="alumni">
                                        <button type="submit" onclick="return confirm('Pindahkan {{ $n->nama_siswa }} ke arsip Alumni?')" class="inline-block bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold py-1 px-2 rounded text-[11px] transition border border-amber-300" title="Arsipkan sebagai Alumni">
                                            Alumni
                                        </button>
                                    @else
                                        <input type="hidden" name="status" value="aktif">
                                        <button type="submit" onclick="return confirm('Aktifkan kembali status nasabah {{ $n->nama_siswa }}?')" class="inline-block bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold py-1 px-2 rounded text-[11px] transition border border-emerald-300" title="Aktifkan Nasabah">
                                            Aktifkan
                                        </button>
                                    @endif
                                </form>

                                <a href="{{ url('/admin/edit-nasabah/'.$n->id) }}" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-1 px-2 rounded text-[11px] transition">Edit</a>
                                <a href="{{ url('/admin/hapus-nasabah/'.$n->id) }}" onclick="return confirm('Yakin ingin menghapus nasabah ini?')" class="inline-block bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold py-1 px-2 rounded text-[11px] transition">Hapus</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-500 italic">
                                Belum ada data nasabah pada kategori ini. Silakan tambahkan nasabah atau import dari file CSV.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- MODAL POP-UP IMPORT EXCEL -->
    <div id="modal-import" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="bg-slate-800 px-6 py-4 border-b border-slate-700">
                    <h3 class="text-lg font-bold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import Data Nasabah (CSV)
                    </h3>
                </div>
                <form action="{{ url('/admin/import-nasabah') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <p class="text-xs text-slate-600 mb-2">Sistem akan secara otomatis menghasilkan <strong>No. Rekening Armadillo (Kode Jurusan - No Urut)</strong> untuk setiap siswa yang di-import.</p>
                        <a href="{{ url('/admin/template-nasabah') }}" class="inline-flex items-center text-xs text-blue-600 font-bold hover:underline mb-4">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh Template CSV Contoh
                        </a>
                    </div>
                    <div class="mb-6">
                        <label class="block text-slate-700 text-xs font-bold mb-2">Pilih File CSV (.csv / .txt):</label>
                        <input type="file" name="file_csv" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-300 rounded-lg p-2">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="tutupModalImport()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-lg text-xs transition">Proses Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
        }
        function bukaModalImport() {
            document.getElementById('modal-import').classList.remove('hidden');
        }
        function tutupModalImport() {
            document.getElementById('modal-import').classList.add('hidden');
        }
        window.onclick = function(event) {
            const btn = document.getElementById('profile-btn');
            const dropdown = document.getElementById('profile-dropdown');
            if (btn && dropdown && !btn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
