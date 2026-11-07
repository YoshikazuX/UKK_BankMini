{{-- resources/views/download.blade.php --}}
{{-- Redesigned Public Download Page for HanBank Mobile --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] text-slate-800 antialiased selection:bg-slate-900 selection:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    {{-- 1. NAVIGATION BAR --}}
    @include('components.public_navbar')

    @php
        $apkPath = public_path('downloads/hanbank-mobile.apk');
        $hasApk = file_exists($apkPath);
    @endphp

    {{-- 2. HERO SECTION — DOWNLOAD HANBANK MOBILE --}}
    <section class="relative overflow-hidden pt-10 pb-16 lg:pt-16 lg:pb-24 border-b border-slate-200/70 bg-gradient-to-b from-white via-[#F8FAFC] to-[#F1F5F9]">
        
        {{-- Subtle Grid Background Pattern (identical to landing page) --}}
        <div class="absolute inset-0 opacity-[0.035] pointer-events-none" 
             style="background-image: linear-gradient(to right, #0F172A 1px, transparent 1px), linear-gradient(to bottom, #0F172A 1px, transparent 1px); background-size: 32px 32px;"></div>
        
        {{-- Soft Ambient Glow --}}
        <div class="absolute top-12 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-gradient-to-tr from-emerald-100/30 via-slate-100/40 to-slate-200/20 blur-3xl rounded-full pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                {{-- LEFT COLUMN: Application Info & Download Actions --}}
                <div class="lg:col-span-7 xl:col-span-7 text-center lg:text-left">
                    
                    {{-- Eyebrow Label (No dots) --}}
                    <div class="inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-900 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-5 shadow-sm">
                        HanBank Mobile Application
                    </div>

                    {{-- Headline --}}
                    <h1 class="text-3xl sm:text-4xl md:text-5xl xl:text-[3.25rem] font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                        Perbankan Sekolah, Kini dalam Genggaman.
                    </h1>

                    {{-- Description --}}
                    <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Pantau tabungan, lihat riwayat transaksi, kelola target menabung, dan akses layanan Bank Mini Sekolah langsung melalui aplikasi HanBank Mobile.
                    </p>

                    {{-- Release Information Card --}}
                    <div class="mt-8 p-5 sm:p-6 bg-white rounded-2xl border border-slate-200/90 shadow-sm max-w-xl mx-auto lg:mx-0 text-left">
                        <div class="flex items-center justify-between pb-3.5 border-b border-slate-100">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aplikasi Resmi</span>
                                <h3 class="text-base font-bold text-slate-900">HanBank Mobile Siswa</h3>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $hasApk ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                {{ $hasApk ? 'Siap Diunduh' : 'v1.0.0 — Rilis November 2026' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3.5 text-xs">
                            <div>
                                <span class="text-slate-400 block font-medium">Platform</span>
                                <span class="font-bold text-slate-800">Android APK</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Versi</span>
                                <span class="font-bold text-slate-800">v1.0.0</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Ukuran</span>
                                <span class="font-bold text-slate-800">~24.8 MB</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block font-medium">Kompatibilitas</span>
                                <span class="font-bold text-slate-800">Android 7.0+</span>
                            </div>
                        </div>
                    </div>

                    {{-- Download & Return Action Buttons --}}
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        @if($hasApk)
                            <a href="{{ asset('downloads/hanbank-mobile.apk') }}" download
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 shadow-lg shadow-emerald-500/20 transition active:scale-[0.99] text-sm sm:text-base">
                                <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Unduh APK Android</span>
                            </a>
                        @else
                            <button type="button" 
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl font-bold text-white bg-slate-900 shadow-md transition text-sm sm:text-base cursor-default">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Unduh APK Android</span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-800 text-emerald-300 border border-slate-700">
                                    v1.0.0
                                </span>
                            </button>
                        @endif

                        <a href="{{ route('landing') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-slate-800 bg-white hover:bg-slate-50 border border-slate-300 shadow-sm transition text-sm sm:text-base">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Kembali ke Beranda</span>
                        </a>
                    </div>

                    <p class="mt-4 text-xs text-slate-500">
                        {{ $hasApk ? 'Paket APK resmi diverifikasi dan bebas dari malware.' : 'Paket APK resmi HanBank Mobile diverifikasi dan siap digunakan di laboratorium sekolah.' }}
                    </p>

                </div>

                {{-- RIGHT COLUMN: Real Student Smartphone Showcase Mockup --}}
                <div class="lg:col-span-5 xl:col-span-5 flex justify-center">
                    <div class="relative max-w-[280px] sm:max-w-[310px] w-full transition transform hover:-translate-y-1 duration-300">
                        
                        {{-- Phone Chassis --}}
                        <div class="relative bg-slate-950 rounded-[2.8rem] p-2.5 sm:p-3 shadow-2xl border-4 border-slate-800 ring-1 ring-slate-700/80">
                            
                            {{-- Phone Speaker Notch (Clean slit, no dots) --}}
                            <div class="absolute top-3.5 left-1/2 -translate-x-1/2 w-16 h-1 bg-slate-800 rounded-full z-30"></div>

                            {{-- Real Student Mobile Banking Screen --}}
                            <div class="overflow-hidden rounded-[2.4rem] aspect-[502/1024] bg-white border border-slate-900">
                                <img src="{{ asset('images/showcase_mobile_siswa.jpg') }}" 
                                     alt="Screenshot HanBank Mobile Nasabah Siswa" 
                                     class="w-full h-full object-cover select-none" />
                            </div>
                        </div>

                        {{-- Ambient Shadow --}}
                        <div class="h-8 w-4/5 mx-auto bg-slate-950/40 blur-2xl rounded-full -mt-3"></div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- 3. MOBILE APP HIGHLIGHTS --}}
    <section class="py-16 sm:py-20 bg-white border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider mb-3">
                    Fitur Unggulan Mobile
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Segala Kemudahan Tabungan Siswa
                </h2>
                <p class="mt-3 text-sm sm:text-base text-slate-600">
                    Aplikasi dirancang untuk kemudahan transaksi, akuntabilitas saldo, dan pembiasaan literasi keuangan sejak dini.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                {{-- Feature 1 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Monitoring Saldo Tabungan</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Pemisahan jelas antara saldo aktif yang dapat ditarik dengan saldo mengendap minimal Rp 10.000 untuk keamanan rekening.
                    </p>
                </div>

                {{-- Feature 2 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Kartu Digital QRIS Rekening</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Identitas nasabah berbasis kode QR instan untuk mempercepat proses identifikasi di loket kasir tanpa perlu membawa buku tabungan fisik.
                    </p>
                </div>

                {{-- Feature 3 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Buku Mutasi &amp; Grafik Arus Kas</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Riwayat mutasi setoran dan penarikan secara real-time, dilengkapi grafik analitik keuangan untuk mengevaluasi kebiasaan menabung.
                    </p>
                </div>

                {{-- Feature 4 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Target Menabung (Saving Goals)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Tetapkan impian menabung untuk kebutuhan studi atau peralatan sekolah dengan visualisasi persentase progres yang jelas.
                    </p>
                </div>

                {{-- Feature 5 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Otorisasi PIN 6 Digit</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Keamanan penuh setiap transaksi penarikan dana di loket dengan otorisasi kode PIN rahasia 6 digit yang dikelola mandiri oleh siswa.
                    </p>
                </div>

                {{-- Feature 6 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Emergency Freeze Mandiri</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Kemudahan mengunci status rekening secara mandiri jika kartu siswa atau ponsel hilang, guna mencegah penyalahgunaan dana.
                    </p>
                </div>

            </div>

        </div>
    </section>

    {{-- 4. PANDUAN PEMASANGAN (HOW TO INSTALL APK) --}}
    <section class="py-16 sm:py-24 bg-[#F8FAFC] border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider mb-3">
                    Panduan Instalasi
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Cara Memasang Aplikasi di Android
                </h2>
                <p class="mt-3 text-sm sm:text-base text-slate-600">
                    Ikuti empat langkah mudah berikut untuk mulai menggunakan HanBank Mobile di ponsel pintar Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- Step 1 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm relative">
                    <span class="text-3xl font-black text-slate-200 absolute top-4 right-5">01</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-800 font-bold flex items-center justify-center mb-4">
                        1
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Unduh Berkas APK</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Tekan tombol Unduh APK Android di atas untuk mengunduh paket pemasangan resmi HanBank Mobile.
                    </p>
                </div>

                {{-- Step 2 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm relative">
                    <span class="text-3xl font-black text-slate-200 absolute top-4 right-5">02</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-800 font-bold flex items-center justify-center mb-4">
                        2
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Izinkan Sumber Dikenal</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Bila muncul dialog keamanan, aktifkan izin pemasangan aplikasi dari browser atau pengelola berkas pada pengaturan perangkat Anda.
                    </p>
                </div>

                {{-- Step 3 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm relative">
                    <span class="text-3xl font-black text-slate-200 absolute top-4 right-5">03</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-800 font-bold flex items-center justify-center mb-4">
                        3
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Pasang Aplikasi</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Buka berkas unduhan di notifikasi atau folder Download, lalu tekan tombol Pasang (Install) dan tunggu proses selesai.
                    </p>
                </div>

                {{-- Step 4 --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm relative">
                    <span class="text-3xl font-black text-slate-200 absolute top-4 right-5">04</span>
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-800 font-bold flex items-center justify-center mb-4">
                        4
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Masuk Akun Siswa</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Jalankan HanBank Mobile, masukkan username / NIS dan kata sandi yang telah didaftarkan secara resmi oleh administrator sekolah.
                    </p>
                </div>

            </div>

        </div>
    </section>

    {{-- 5. PERSYARATAN SISTEM (SYSTEM REQUIREMENTS) --}}
    <section class="py-16 sm:py-20 bg-white border-b border-slate-200/70">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider mb-3">
                    Spesifikasi Perangkat
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Persyaratan Sistem
                </h2>
                <p class="mt-2 text-sm text-slate-600">
                    Pastikan perangkat Android Anda memenuhi ketentuan minimum untuk pengalaman terbaik.
                </p>
            </div>

            <div class="bg-[#F8FAFC] rounded-2xl border border-slate-200 p-6 sm:p-8">
                <div class="divide-y divide-slate-200">
                    
                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-sm">
                        <span class="font-bold text-slate-800">Sistem Operasi</span>
                        <span class="text-slate-600">Android 7.0 (Nougat) atau versi lebih baru</span>
                    </div>

                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-sm">
                        <span class="font-bold text-slate-800">Ruang Penyimpanan Bebas</span>
                        <span class="text-slate-600">Minimal 50 MB memori internal kosong</span>
                    </div>

                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-sm">
                        <span class="font-bold text-slate-800">Izin Perangkat</span>
                        <span class="text-slate-600">Kamera (Pemindaian QR) &amp; Penyimpanan Dokumen Slip</span>
                    </div>

                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-sm">
                        <span class="font-bold text-slate-800">Konektivitas</span>
                        <span class="text-slate-600">Jaringan Internet / Wi-Fi Intranet Laboratorium Bank Mini</span>
                    </div>

                    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1 text-sm">
                        <span class="font-bold text-slate-800">Keamanan &amp; Privasi</span>
                        <span class="text-emerald-700 font-semibold">100% Bebas Iklan &amp; Komunikasi API Terenkripsi SSL</span>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- 6. FINAL CTA SECTION (Consistent with Landing Page) --}}
    <section class="relative bg-slate-900 text-white py-16 sm:py-20 overflow-hidden">
        
        <div class="absolute inset-0 opacity-[0.05] pointer-events-none" 
             style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>
        
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight max-w-3xl mx-auto">
                Mulai Kelola Tabungan Sekolah Anda
            </h2>

            <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                Akses layanan perbankan mini sekolah yang transparan, aman, dan mudah diakses dari perangkat Android Anda.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('landing') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 shadow-lg shadow-emerald-500/20 transition text-sm sm:text-base">
                    <span>Kembali ke Beranda Utama</span>
                    <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>

                <a href="{{ route('login') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 shadow-sm transition text-sm sm:text-base">
                    <span>Masuk Dashboard Petugas</span>
                </a>
            </div>
        </div>
    </section>

    {{-- 7. FOOTER (Identical to Landing Page) --}}
    <footer class="bg-slate-950 text-slate-400 py-12 sm:py-16 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">
                
                {{-- Column 1: Institutional Info --}}
                <div class="md:col-span-6 lg:col-span-5">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}" 
                             alt="Logo SMK N 1 Bantul" 
                             class="h-10 w-auto object-contain">
                        <div>
                            <span class="text-base font-bold text-white block tracking-tight">LKM Mitra Siswa Abadi</span>
                            <span class="text-xs text-slate-500 block">SMK Negeri 1 Bantul</span>
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-400 leading-relaxed max-w-sm">
                        Platform perbankan mini sekolah terintegrasi untuk pengelolaan tabungan siswa, kas operasional teller, dan pelaporan keuangan sekolah terpercaya.
                    </p>
                    <div class="mt-4 text-[11px] text-slate-600 font-mono">
                        Core Banking System v2.7 — Production Environment
                    </div>
                </div>

                {{-- Column 2: Navigation Links --}}
                <div class="md:col-span-3 lg:col-span-3">
                    <div class="text-xs font-bold uppercase tracking-wider text-white mb-3">Ekosistem Bank Mini</div>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('landing') }}#hero" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="{{ route('landing') }}#layanan" class="hover:text-white transition">Layanan Utama</a></li>
                        <li><a href="{{ route('landing') }}#platform" class="hover:text-white transition">Platform Terintegrasi</a></li>
                        <li><a href="{{ route('landing') }}#fitur" class="hover:text-white transition">Fitur Berdasarkan Peran</a></li>
                    </ul>
                </div>

                {{-- Column 3: Security & Access Links --}}
                <div class="md:col-span-3 lg:col-span-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-white mb-3">Akses &amp; Bantuan</div>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('landing') }}#keamanan" class="hover:text-white transition">Standar Keamanan Transaksi</a></li>
                        <li><a href="{{ route('landing') }}#faq" class="hover:text-white transition">Pertanyaan Umum (FAQ)</a></li>
                        <li><a href="{{ route('download') }}" class="text-emerald-400 hover:text-emerald-300 font-semibold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh Aplikasi Mobile (APK)</span>
                        </a></li>
                        <li><a href="{{ route('login') }}" class="text-white hover:text-emerald-400 font-semibold transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Masuk Dashboard Petugas</span>
                        </a></li>
                    </ul>
                </div>

            </div>

            {{-- Bottom Divider & Copyright --}}
            <div class="mt-12 pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-3">
                <p>&copy; {{ date('Y') }} LKM Mitra Siswa Abadi SMK Negeri 1 Bantul. Hak Cipta Dilindungi.</p>
                <p>Sistem Informasi Bank Mini Sekolah Enterprise</p>
            </div>
        </div>
    </footer>

</div>
@endsection
