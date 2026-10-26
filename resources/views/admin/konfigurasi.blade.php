<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfigurasi Sistem Global - Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR ADMIN KONSISTEN -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>
                <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                    <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Manajemen Nasabah</a>
                    <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Petugas Avalis</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                    <a href="{{ url('/admin/audit') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jejak Audit</a>
                    <a href="{{ url('/admin/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                        <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                        Absensi
                    </a>
                    <a href="{{ url('/admin/konfigurasi') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Konfigurasi</a>

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
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
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

    <main class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <!-- HEADER HALAMAN -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span>Wewenang Administrator (Sistem Utama & Konfigurasi)</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Konfigurasi Sistem Global</h1>
                <p class="text-slate-600 text-sm mt-1">
                    Atur parameter operasional perbankan mini sekolah, batasan saldo mengendap, algoritma nomor rekening, dan perangkat pemindai.
                </p>
            </div>
            <a href="{{ url('/admin/dashboard') }}" class="hidden sm:inline-flex items-center text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-300 px-3.5 py-2 rounded-xl transition">
                &larr; Kembali
            </a>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-xl shadow-sm flex items-start space-x-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div class="text-sm font-bold">{{ session('success') }}</div>
            </div>
        @endif

        <form action="{{ url('/admin/konfigurasi') }}" method="POST" class="space-y-6">
            @csrf

            <!-- SECTION 1: PARAMETER INTI PERBANKAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-emerald-400 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Parameter Inti Perbankan Mini</h2>
                        <p class="text-xs text-slate-500">Ketentuan batas saldo mengendap dan proteksi penarikan dana loket</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- SALDO MENGENDAP MINIMUM -->
                    <div>
                        <label for="saldo_mengendap_min" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Batas Saldo Mengendap Minimum (Rp) *
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-slate-400 text-sm">Rp</span>
                            <input 
                                type="number" 
                                name="saldo_mengendap_min" 
                                id="saldo_mengendap_min" 
                                value="{{ old('saldo_mengendap_min', $setting->saldo_mengendap_min ?? 10000) }}" 
                                min="0" 
                                step="1000" 
                                required
                                class="w-full pl-12 pr-4 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-base font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800"
                            >
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Standar sistem: <strong>Rp 10.000</strong>. Nasabah tidak dapat menarik dana jika sisa saldo setelah penarikan berada di bawah nilai batas ini.
                        </p>
                    </div>

                    <!-- BIAYA ADMINISTRASI BUKU TABUNGAN -->
                    <div>
                        <label for="biaya_administrasi_buku" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Biaya Administrasi Buku / Transaksi (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-slate-400 text-sm">Rp</span>
                            <input 
                                type="number" 
                                name="biaya_administrasi_buku" 
                                id="biaya_administrasi_buku" 
                                value="{{ old('biaya_administrasi_buku', $setting->biaya_administrasi_buku ?? 5000) }}" 
                                min="0" 
                                step="500" 
                                class="w-full pl-12 pr-4 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-base font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800"
                            >
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Besaran tarif potongan biaya administrasi buku atau pembuatan rekening baru (Kode Sandi [301]).
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: ALGORITMA NOMOR REKENING & TAHUN AJARAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-indigo-400 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Algoritma Master Data Buku Rekening</h2>
                        <p class="text-xs text-slate-500">Pemberian nomor rekening otomatis berdasarkan tahun ajaran dan urutan siswa</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- TAHUN AJARAN AKTIF -->
                    <div>
                        <label for="tahun_ajaran_aktif" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Tahun Ajaran Aktif *
                        </label>
                        <input 
                            type="text" 
                            name="tahun_ajaran_aktif" 
                            id="tahun_ajaran_aktif" 
                            value="{{ old('tahun_ajaran_aktif', $setting->tahun_ajaran_aktif ?? '2026/2027') }}" 
                            required
                            placeholder="Contoh: 2026/2027" 
                            class="w-full px-4 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800"
                        >
                        <p class="text-[11px] text-slate-500 mt-1">
                            Tahun ajaran berjalan untuk penomoran rekening angkatan siswa baru.
                        </p>
                    </div>

                    <!-- FORMAT NOMOR REKENING -->
                    <div>
                        <label for="format_no_rekening" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pola Format Nomor Rekening
                        </label>
                        <select 
                            name="format_no_rekening" 
                            id="format_no_rekening" 
                            class="w-full px-4 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800"
                        >
                            <option value="TA-JURUSAN-URUT" {{ ($setting->format_no_rekening ?? '') == 'TA-JURUSAN-URUT' ? 'selected' : '' }}>
                                TA - KODE JURUSAN - URUT (Contoh: 26-RPL-001)
                            </option>
                            <option value="TAHUN/JURUSAN/URUT" {{ ($setting->format_no_rekening ?? '') == 'TAHUN/JURUSAN/URUT' ? 'selected' : '' }}>
                                TAHUN / KODE JURUSAN / URUT (Contoh: 2026/RPL/001)
                            </option>
                            <option value="JURUSAN-URUT" {{ ($setting->format_no_rekening ?? '') == 'JURUSAN-URUT' ? 'selected' : '' }}>
                                KODE JURUSAN - URUT (Contoh: RPL-001)
                            </option>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Nomor urut siswa berlanjut secara otomatis setiap kali nasabah baru didaftarkan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: KONEKTIVITAS PEMINDAI QR CODE & IDENTITAS -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <div class="flex items-center space-x-3 border-b border-slate-100 pb-4 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-slate-800 text-teal-400 flex items-center justify-center font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Konektivitas Perangkat Pemindai QR & Branding</h2>
                        <p class="text-xs text-slate-500">Konfigurasi modul kamera scanner loket dan identitas instansi</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- TOGGLE SCANNER KAMERA -->
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
                        <div>
                            <span class="text-sm font-bold text-slate-900">Perangkat Pemindai QR Code (Webcam / Scanner)</span>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Mengaktifkan fitur modul kamera HTML5 QR Code pada loket Teller untuk pencarian instan profil nasabah via kartu pelajar.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="scanner_kamera_aktif" value="1" {{ ($setting->scanner_kamera_aktif ?? true) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                    </div>

                    <!-- NAMA RESMI INSTANSI -->
                    <div>
                        <label for="nama_bank" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Resmi Bank Mini Sekolah *
                        </label>
                        <input 
                            type="text" 
                            name="nama_bank" 
                            id="nama_bank" 
                            value="{{ old('nama_bank', $setting->nama_bank ?? 'LKM Mitra Siswa Abadi') }}" 
                            required
                            class="w-full px-4 py-2.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-800"
                        >
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN -->
            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ url('/admin/dashboard') }}" class="px-5 py-3 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 text-sm font-bold transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold shadow-lg transition flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Pengaturan Global</span>
                </button>
            </div>
        </form>

    </main>

    <script>
        function toggleDropdown() { document.getElementById('profile-dropdown').classList.toggle('hidden'); }
        window.addEventListener('click', function(e) {
            if (!document.getElementById('profile-btn').contains(e.target) && !document.getElementById('profile-dropdown').contains(e.target)) {
                document.getElementById('profile-dropdown').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
