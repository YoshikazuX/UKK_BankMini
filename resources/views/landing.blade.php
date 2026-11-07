<!-- resources/views/landing.blade.php -->
<!-- Public Landing Page for Bank Mini Sekolah -->
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Bank Mini Sekolah') }}
    </h2>
@endsection

@section('content')
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
            </div>
        </div>
        <!-- subtle background accent -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <svg class="absolute top-0 left-1/2 -translate-x-1/2" width="800" height="800" fill="none" viewBox="0 0 800 800" aria-hidden="true">
                <circle cx="400" cy="400" r="400" fill="rgba(255,255,255,0.05)" />
            </svg>
        </div>
    </section>

    <!-- Introduction -->
    <section class="max-w-5xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Tentang Bank Mini Sekolah</h2>
        <p class="text-gray-700 leading-relaxed">
            Bank Mini Sekolah adalah platform perbankan khusus yang diintegrasikan dengan sistem manajemen sekolah. Memungkinkan siswa, orang tua, dan staf mengelola transaksi keuangan secara digital dengan mudah.
        </p>
    </section>

    <!-- Services Overview -->
    <section class="bg-gray-50 py-10 sm:py-12">
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
                    <p class="text-gray-600">Akses laporan keuangan real‑time untuk admin dan akuntansi.</p>
                </div>
            </div>
        </div>
    </section>

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
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-800 text-center mb-8">Pertanyaan Umum</h2>
            <div class="space-y-4">
                <details class="bg-white p-4 rounded shadow hover:shadow-md transition">
                    <summary class="font-medium text-gray-800 cursor-pointer">Bagaimana cara mengakses aplikasi?</summary>
                    <p class="mt-2 text-gray-600">Unduh aplikasi Android di halaman <a href="{{ route('download') }}" class="text-indigo-600 underline">Unduh</a> dan login menggunakan kredensial yang sama dengan dashboard.</p>
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

    <!-- Contact --> 
    <section class="max-w-5xl mx-auto py-10 sm:py-12 px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Hubungi Kami</h2>
        <p class="text-gray-600 mb-2">Email: <a href="mailto:info@hanbank.my.id" class="text-indigo-600 underline">info@hanbank.my.id</a></p>
        <p class="text-gray-600">Telepon: +62 812‑3456‑7890</p>
    </section>
</div>
@endsection