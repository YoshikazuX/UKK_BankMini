<!-- resources/views/landing.blade.php -->
<!-- Public Landing Page for Bank Mini Sekolah -->
@extends('layouts.app')

@section('header')
    @include('components.public_navbar')
@endsection

@section('content')
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
                    <a href="{{ route('login') }}" class="px-6 py-3 text-white rounded-md transition focus:outline-none focus:ring-2 focus:ring-teal-400 bg-[#19C9A3] hover:bg-[#17b99c] font-medium shadow">
                        Masuk Dashboard
                    </a>
                    <a href="{{ route('download') }}" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 text-white rounded-md transition font-medium">
                        Unduh Aplikasi Mobile
                    </a>
                </div>
            </div>
            {{-- Right column: dashboard mockup card with label --}}
            <div class="md:w-1/2 flex justify-center">
                <div class="bg-white bg-opacity-10 rounded-lg p-4 shadow-lg">
                    <img src="{{ asset('images/daily_report_closing.jpg') }}" alt="Daily Report Closing" class="max-w-full h-auto object-contain rounded-lg shadow-md" />
                    <p class="text-center text-sm text-gray-300 mt-2">Ilustrasi Dashboard</p>
                </div>
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

    {{-- Layanan Utama --}}
    <section id="layanan" class="bg-gray-50 py-10 sm:py-12">
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
                    <svg class="h-6 w-6 text-teal-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l9 13.414l4.707-4.707z" clip-rule="evenodd"/></svg>
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

    {{-- Kontak --}}
    <section id="kontak" class="max-w-5xl mx-auto py-10 sm:py-12 px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Hubungi Kami</h2>
        <p class="text-gray-600 mb-2">Email: <a href="mailto:info@hanbank.my.id" class="text-teal-600 underline">info@hanbank.my.id</a></p>
        <p class="text-gray-600">Telepon / WhatsApp: +62 812-3456-7890</p>
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
