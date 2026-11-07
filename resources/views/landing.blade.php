<!-- resources/views/landing.blade.php -->
<!-- Public Landing Page for Bank Mini Sekolah -->
@extends('layouts.app')

@section('header')
    @include('components.public_navbar')
@endsection

@section('content')
<<<<<<< HEAD
<div class="bg-white">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-slate-800 to-slate-900 text-white py-16 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
                Mini Banking for Schools
            </h1>
            <p class="mt-4 text-lg sm:text-xl text-slate-200 max-w-2xl mx-auto">
                Solusi perbankan sederhana dan aman untuk institusi pendidikan Anda.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('login') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400 focus:outline-none rounded-md text-white transition">
                    Masuk Dashboard
                </a>
                <a href="{{ route('download') }}" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 focus:ring-2 focus:ring-gray-400 focus:outline-none rounded-md text-gray-800 transition">
                    Unduh Aplikasi Mobile
                </a>
=======
<div class="bg-transparent">
    {{-- Hero Section - navy gradient background --}}
    <section id="hero" class="relative bg-gradient-to-r from-[#1e293b] to-[#0f172a] text-white py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center gap-8">
            {{-- Left column: text and CTA --}}
            <div class="md:w-1/2">
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-4 text-white">
                    Solusi Perbankan Mini Sekolah yang Modern, Terintegrasi, dan Mudah Digunakan.
                </h1>
                <p class="mt-4 text-lg sm:text-xl text-white max-w-2xl mx-auto md:mx-0">
                    Platform digital untuk mendukung pengelolaan nasabah, aktivitas transaksi, dan operasional perbankan sekolah.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('login') }}" class="px-6 py-3 text-white rounded-md transition focus:outline-none focus:ring-2 focus:ring-teal-400 bg-[#19C9A3] hover:bg-[#17b99c]">
                        Masuk Dashboard
                    </a>
                    <button disabled class="px-6 py-3 bg-gray-400 text-white opacity-50 rounded-md cursor-not-allowed">
                        APK Segera Tersedia
                    </button>
                </div>
            </div>
            {{-- Right column: dashboard mockup card with label --}}
            <div class="md:w-1/2 flex justify-center">
                <div class="bg-white bg-opacity-10 rounded-lg p-4 shadow-lg">
                    <img src="{{ asset('images/daily_report_closing.jpg') }}" alt="Daily Report Closing" class="max-w-full h-auto object-contain rounded-lg shadow-md" />
                    <p class="text-center text-sm text-gray-300 mt-2">Ilustrasi Dashboard</p>
                </div>
>>>>>>> 5272b57 (landing page)
            </div>
        </div>

    </section>

    {{-- Tentang Bank Mini Sekolah --}}
    <section id="tentang" class="max-w-5xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center">
        <div class="md:w-1/2">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Tentang Bank Mini Sekolah</h2>
            <p class="text-gray-700 leading-relaxed mb-4">
                Bank Mini Sekolah adalah platform perbankan khusus yang diintegrasikan dengan sistem manajemen sekolah. Memungkinkan siswa, orang tua, dan staf mengelola transaksi keuangan secara digital dengan mudah.
            </p>
        </div>
        <div class="md:w-1/2 mt-4 md:mt-0 flex justify-center">
            <img src="{{ asset('images/about_illustration.svg') }}" alt="Tentang Bank Mini Sekolah" class="max-w-xs" />
        </div>
    </section>

<<<<<<< HEAD
    <!-- Introduction -->
    <section class="max-w-5xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Tentang Bank Mini Sekolah</h2>
        <p class="text-gray-700 leading-relaxed">
            Bank Mini Sekolah adalah platform perbankan khusus yang diintegrasikan dengan sistem manajemen sekolah. Memungkinkan siswa, orang tua, dan staf mengelola transaksi keuangan secara digital dengan mudah.
        </p>
    </section>

    <!-- Services Overview -->
    <section class="bg-gray-50 py-10 sm:py-12">
=======
    {{-- Layanan Utama --}}
    <section id="layanan" class="bg-gray-50 py-10 sm:py-12">
>>>>>>> 5272b57 (landing page)
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Layanan Utama</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold mb-2">Tabungan Siswa</h3>
                    <p class="text-gray-600">Kelola tabungan, setoran, dan penarikan dengan keamanan penuh.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold mb-2">Pembayaran Biaya</h3>
                    <p class="text-gray-600">Pembayaran uang sekolah, kegiatan, dan lainnya secara online.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold mb-2">Laporan Keuangan</h3>
                    <p class="text-gray-600">Akses laporan keuangan real-time untuk admin dan akuntansi.</p>
                </div>
            </div>
        </div>
    </section>

<<<<<<< HEAD
    <!-- Mobile App Promotion --> 
    <section class="max-w-5xl mx-auto py-10 sm:py-14 px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">Aplikasi Mobile</h2>
        <p class="text-gray-600 mb-8 max-w-xl mx-auto">
            Gunakan aplikasi Android Bank Mini untuk mengecek saldo, melihat riwayat mutasi, dan memverifikasi setoran langsung dari ponsel Anda.
        </p>

        <!-- Smartphone Frame Preview -->
        <div class="mx-auto mb-8 max-w-[270px]">
            <div class="relative mx-auto bg-slate-900 border-[8px] border-slate-800 rounded-[2.5rem] shadow-2xl p-4 pt-6 text-left overflow-hidden">
                <!-- Speaker / Notch -->
                <div class="w-20 h-4 bg-slate-800 rounded-b-xl mx-auto -mt-6 mb-4 flex items-center justify-center">
                    <div class="w-8 h-1 bg-slate-700 rounded-full"></div>
                </div>
                <!-- App Screen Header -->
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}" class="w-6 h-6 object-contain" alt="Logo">
                        <span class="text-xs font-bold text-white tracking-tight">HanBank App</span>
                    </div>
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                </div>
                <!-- Virtual Card -->
                <div class="mt-4 p-4 rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-white shadow-lg">
                    <p class="text-[10px] text-indigo-200 font-medium">TABUNGAN SISWA</p>
                    <p class="text-lg font-bold mt-1">Rp 1.450.000</p>
                    <div class="mt-3 flex justify-between items-center text-[10px] text-indigo-200">
                        <span>Aktif & Terverifikasi</span>
                        <span>SMKN 1 Bantul</span>
                    </div>
                </div>
                <!-- Quick Menu -->
                <div class="grid grid-cols-3 gap-2 mt-4 text-center text-[11px] font-semibold text-slate-300">
                    <div class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition">
                        <div class="text-indigo-400 font-bold mb-0.5">💰</div>
                        Setor
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition">
                        <div class="text-emerald-400 font-bold mb-0.5">💳</div>
                        Tarik
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition">
                        <div class="text-amber-400 font-bold mb-0.5">📜</div>
                        Riwayat
                    </div>
                </div>
                <!-- App Status -->
                <div class="mt-4 py-2 text-center border-t border-slate-800/60">
                    <span class="text-[10px] text-slate-400">LKM Mitra Siswa Abadi</span>
                </div>
            </div>
        </div>

        <a href="{{ route('download') }}" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition">
            Lihat Detail Unduhan
        </a>
    </section>

    <!-- FAQ --> 
    <section class="bg-gray-50 py-10 sm:py-12">
=======
    {{-- Peran dan Hak Akses (Fitur) --}}
    <section id="fitur" class="max-w-5xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Peran dan Hak Akses</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition flex flex-col items-center">
                <img src="{{ asset('images/role_admin.svg') }}" alt="Admin" class="h-24 w-24 mb-4" />
                <h3 class="text-xl font-semibold mb-2">Admin</h3>
                <p class="text-gray-600 text-center">Manajemen penuh, laporan keuangan, dan kontrol akses.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition flex flex-col items-center">
                <img src="{{ asset('images/role_teller.svg') }}" alt="Teller" class="h-24 w-24 mb-4" />
                <h3 class="text-xl font-semibold mb-2">Teller</h3>
                <p class="text-gray-600 text-center">Pengelolaan transaksi harian, verifikasi setoran, dan penarikan.</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition flex flex-col items-center">
                <img src="{{ asset('images/role_supervisor.svg') }}" alt="Supervisor" class="h-24 w-24 mb-4" />
                <h3 class="text-xl font-semibold mb-2">Supervisor</h3>
                <p class="text-gray-600 text-center">Pengawasan operasional, persetujuan, dan monitoring.</p>
            </div>
        </div>
    </section>

    {{-- Aplikasi Mobile Nasabah --}}
    <section id="aplikasi-mobile" class="max-w-5xl mx-auto py-10 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center gap-8">
            <!-- Left column: info and features -->
            <div class="md:w-1/2">
                <p class="text-sm text-teal-600 font-semibold uppercase mb-2">Aplikasi Mobile Nasabah</p>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Perbankan Sekolah dalam Genggaman.</h2>
                <p class="text-gray-700 mb-6">Aplikasi mobile menjadi kanal layanan digital bagi nasabah Bank Mini Sekolah, memudahkan akses saldo, transaksi, dan fitur perbankan lainnya langsung dari ponsel.</p>
                <ul class="space-y-3">
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Informasi saldo tabungan</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">QR rekening</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Buku mutasi</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Target tabungan</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                        <span class="text-gray-700">Layanan perbankan siswa</span>
                    </li>
                </ul>
            </div>
            <!-- Right column: mobile screenshot in phone frame -->
            <div class="md:w-1/2 flex justify-center">
                <div class="bg-white bg-opacity-10 rounded-xl p-4 shadow-lg max-w-xs">
                    <img src="{{ asset('images/mobile_screenshot.jpg') }}" alt="Screenshot Aplikasi Mobile" class="w-full h-auto object-contain rounded-lg" />
                </div>
            </div>
        </div>
    </section>



    {{-- Keunggulan Sistem --}}
    <section id="keunggulan" class="bg-gray-50 py-10 sm:py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Keunggulan Sistem</h2>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-6 text-gray-700">
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414L9 13.414l4.707-4.707z" clip-rule="evenodd"/></svg>
                    <span>Pengelolaan operasional dalam satu sistem terintegrasi.</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z"/></svg>
                    <span>Pembagian hak akses berdasarkan peran (Admin, Teller, Supervisor, Nasabah).</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9 12a3 3 0 100-6 3 3 0 000 6z"/></svg>
                    <span>Pencatatan aktivitas operasional yang dapat diaudit.</span>
                </li>
                <li class="flex items-start">
                    <svg class="h-6 w-6 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V4a1 1 0 00-1-1H4z"/></svg>
                    <span>Integrasi portal web dan aplikasi mobile melalui REST API.</span>
                </li>
            </ul>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="bg-gray-50 py-10 sm:py-12">
>>>>>>> 5272b57 (landing page)
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Pertanyaan Umum</h2>
            <div class="space-y-4">
                <details class="bg-white p-4 rounded shadow hover:shadow-md transition">
                    <summary class="font-medium text-gray-800 cursor-pointer">Bagaimana cara mengakses aplikasi?</summary>
                    <p class="mt-2 text-gray-600">Unduh aplikasi Android di halaman <a href="{{ route('download') }}" class="text-teal-600 underline">Unduh</a> dan login menggunakan kredensial yang sama dengan dashboard.</p>
                </details>
                <details class="bg-white p-4 rounded shadow hover:shadow-md transition">
                    <summary class="font-medium text-gray-800 cursor-pointer">Apakah aplikasi tersedia untuk iOS?</summary>
                    <p class="mt-2 text-gray-600">Versi iOS sedang dalam pengembangan. Ikuti update di halaman utama.</p>
                </details>
                <details class="bg-white p-4 rounded shadow hover:shadow-md transition">
                    <summary class="font-medium text-gray-800 cursor-pointer">Apakah data saya aman?</summary>
                    <p class="mt-2 text-gray-600">Semua data disimpan dengan enkripsi standar industri dan hanya dapat diakses oleh pengguna yang berwenang.</p>
                </details>
            </div>
        </div>
    </section>

<<<<<<< HEAD
    <!-- Contact --> 
    <section class="max-w-5xl mx-auto py-10 sm:py-12 px-4 sm:px-6 lg:px-8 text-center">
=======
    {{-- Kontak --}}
    <section id="kontak" class="max-w-5xl mx-auto py-10 sm:py-12 px-4 sm:px-6 lg:px-8 text-center">
>>>>>>> 5272b57 (landing page)
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Hubungi Kami</h2>
        <!-- Placeholder contact info; replace with verified details when available -->
    </section>

    {{-- Footer (must be last) --}}
    <footer class="bg-slate-800 text-gray-200 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0 text-center md:text-left">
                <h3 class="text-xl font-semibold">Bank Mini Sekolah</h3>
                <p class="text-sm">Sistem perbankan mini untuk pendidikan.</p>
            </div>
            <div class="flex space-x-4 mb-4 md:mb-0">
                <a href="#hero" class="hover:underline">Beranda</a>
                <a href="#tentang" class="hover:underline">Tentang</a>
                <a href="#layanan" class="hover:underline">Layanan</a>
                <a href="#fitur" class="hover:underline">Fitur</a>

                <a href="#faq" class="hover:underline">FAQ</a>
                <a href="#kontak" class="hover:underline">Kontak</a>
            </div>
            <div class="text-center md:text-right">
                <a href="{{ route('login') }}" class="text-teal-400 hover:underline">Masuk Portal</a>
            </div>
        </div>
        <div class="text-center mt-4 text-xs">&copy; {{ date('Y') }} Bank Mini Sekolah. All rights reserved.</div>
    </footer>
</div>
@endsection