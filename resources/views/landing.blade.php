<!-- resources/views/landing.blade.php -->
{{-- Public Landing Page for Bank Mini Sekolah --}}
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Bank Mini Sekolah') }}
    </h2>
@endsection

@section('content')
<div class="bg-white">
    {{-- Hero Section – dark navy gradient, strong visual --}}
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

    {{-- Introduction - tighter spacing -}}
    <section class="max-w-5xl mx-auto py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Tentang Bank Mini Sekolah</h2>
        <p class="text-gray-700 leading-relaxed">
            Bank Mini Sekolah adalah platform perbankan khusus yang diintegrasikan dengan sistem manajemen sekolah. Memungkinkan siswa, orang tua, dan staf mengelola transaksi keuangan secara digital dengan mudah.
        </p>
    </section>

    {{-- Services Overview – cards with hover effect –}}
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

    {{-- Mobile App Promotion – include mockup placeholder –}} 
    <section class="max-w-5xl mx-auto py-10 sm:py-12 px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Aplikasi Mobile</h2>
        <p class="text-gray-600 mb-6">
            Gunakan aplikasi Android untuk mengakses semua fitur dari ponsel Anda.
        </p>
        <img src="{{ asset('images/mobile_mockup.png') }}" alt="Mobile app preview" class="mx-auto mb-6 max-w-xs hidden md:block" />
        <a href="{{ route('download') }}" class="inline-block px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition">
            Lihat Detail Unduhan
        </a>
    </section>

    {{-- FAQ – compact accordion style –}} 
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

    {{-- Contact – concise –}} 
    <section class="max-w-5xl mx-auto py-10 sm:py-12 px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Hubungi Kami</h2>
        <p class="text-gray-600 mb-2">Email: <a href="mailto:info@hanbank.my.id" class="text-indigo-600 underline">info@hanbank.my.id</a></p>
        <p class="text-gray-600">Telepon: +62 812‑3456‑7890</p>
    </section>
</div>
@endsection