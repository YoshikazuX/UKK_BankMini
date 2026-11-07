{{-- resources/views/landing.blade.php --}}
{{-- Redesigned Public Landing Page for HanBank / Sistem Bank Mini Sekolah --}}
@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] text-slate-800 antialiased selection:bg-slate-900 selection:text-white" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    {{-- 1. NAVIGATION BAR --}}
    @include('components.public_navbar')

    {{-- 2. HERO SECTION — MAIN PRODUCT SHOWCASE --}}
    <section id="hero" class="relative overflow-hidden pt-8 pb-16 lg:pt-16 lg:pb-24 border-b border-slate-200/70 bg-gradient-to-b from-white via-[#F8FAFC] to-[#F1F5F9]">
        
        {{-- Subtle Grid Background Pattern (matching Login Screen reference) --}}
        <div class="absolute inset-0 opacity-[0.035] pointer-events-none" 
             style="background-image: linear-gradient(to right, #0F172A 1px, transparent 1px), linear-gradient(to bottom, #0F172A 1px, transparent 1px); background-size: 32px 32px;"></div>
        
        {{-- Soft Ambient Glows (Navy & Emerald, quiet and subtle) --}}
        <div class="absolute top-12 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-gradient-to-tr from-emerald-100/30 via-slate-100/40 to-slate-200/20 blur-3xl rounded-full pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                {{-- LEFT COLUMN: Headline, Description & Actions --}}
                <div class="lg:col-span-6 xl:col-span-5 text-center lg:text-left">
                    
                    {{-- Main Headline --}}
                    <h1 class="text-3xl sm:text-4xl md:text-5xl xl:text-[3.25rem] font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                        Pengelolaan Bank Mini Sekolah dalam Satu Ekosistem Digital
                    </h1>

                    {{-- Brief Description --}}
                    <p class="mt-5 text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Solusi perbankan sekolah terintegrasi untuk pengelolaan tabungan siswa, pelayanan teller, pengawasan operasional, dan monitoring keuangan secara lebih transparan.
                    </p>

                    {{-- CTA Buttons --}}
                    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5">
                        <a href="{{ route('login') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-md hover:shadow-lg transition active:scale-[0.99] text-sm sm:text-base">
                            <span>Masuk Dashboard</span>
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>

                        <a href="{{ route('download') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-slate-800 bg-white hover:bg-slate-50 border border-slate-300/90 shadow-sm transition active:scale-[0.99] text-sm sm:text-base">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Aplikasi</span>
                        </a>

                        <a href="#fitur" 
                           class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-950 px-3 py-2 rounded-lg transition">
                            <span>Jelajahi Fitur</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>
                    </div>

                </div>

                {{-- RIGHT COLUMN: Realistic Device Composition (Laptop + Smartphone + Floating Badges) --}}
                <div class="lg:col-span-6 xl:col-span-7 relative">
                    
                    {{-- Device Wrapper with spatial depth --}}
                    <div class="relative mx-auto max-w-2xl lg:max-w-none pt-4 pb-8 sm:py-6">
                        
                        {{-- 1. LAPTOP MOCKUP — WEB DASHBOARD TELLER --}}
                        <div class="relative w-full rounded-2xl transition duration-300">
                            
                            {{-- Laptop Top Lid / Screen Frame --}}
                            <div class="relative bg-slate-900 rounded-t-2xl p-2.5 sm:p-3 pb-0 shadow-2xl border border-slate-700/80">
                                
                                {{-- Browser Window Bar --}}
                                <div class="flex items-center justify-between pb-2 px-1 text-slate-400">
                                    <div class="text-[11px] font-semibold text-slate-400">Loket Kasir Web</div>
                                    <div class="flex items-center gap-1.5 bg-slate-800/90 rounded-md px-3 py-1 text-[10px] sm:text-[11px] font-mono text-slate-300 border border-slate-700/60 max-w-[280px] sm:max-w-sm truncate">
                                        <svg class="w-3 h-3 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="truncate">hanbank.my.id/teller/dashboard</span>
                                    </div>
                                    <div class="w-8"></div>
                                </div>

                                {{-- Screen Display Image (Real Web Teller Dashboard) --}}
                                <div class="overflow-hidden rounded-t-md aspect-[1024/507] bg-slate-100 border border-slate-800">
                                    <img src="{{ asset('images/showcase_web_teller.png') }}" 
                                         alt="Web Dashboard Operasional Loket Teller" 
                                         class="w-full h-full object-cover object-top select-none" />
                                </div>
                            </div>

                            {{-- Laptop Aluminum Base Plate --}}
                            <div class="h-3 sm:h-3.5 bg-gradient-to-r from-slate-700 via-slate-600 to-slate-700 rounded-b-xl border-t border-slate-500/80 shadow-2xl relative mx-auto w-[102%] -left-[1%]">
                                {{-- Trackpad Thumb Indent --}}
                                <div class="w-16 sm:w-20 h-1 bg-slate-500/80 rounded-full mx-auto -mt-0.5"></div>
                            </div>

                            {{-- Laptop Drop Shadow Ambient --}}
                            <div class="h-6 w-5/6 mx-auto bg-slate-900/20 blur-xl rounded-full -mt-2"></div>
                        </div>

                        {{-- 2. SMARTPHONE MOCKUP — HANBANK MOBILE SISWA (Overlapping Front Right) --}}
                        <div class="absolute -bottom-6 sm:-bottom-10 right-2 sm:right-4 md:right-8 w-[170px] sm:w-[210px] md:w-[240px] z-20 transition transform hover:-translate-y-1 duration-300">
                            
                            {{-- Phone Chassis / Outer Rim --}}
                            <div class="relative bg-slate-950 rounded-[2.2rem] sm:rounded-[2.8rem] p-2 sm:p-2.5 shadow-2xl border-[3px] sm:border-4 border-slate-800 ring-1 ring-slate-700/80">
                                
                                {{-- Phone Speaker Notch --}}
                                <div class="absolute top-3 sm:top-3.5 left-1/2 -translate-x-1/2 w-14 sm:w-16 h-1 bg-slate-800 rounded-full z-30"></div>

                                {{-- Screen Display (Real Student Mobile Banking Screen) --}}
                                <div class="overflow-hidden rounded-[1.8rem] sm:rounded-[2.4rem] aspect-[502/1024] bg-white border border-slate-900">
                                    <img src="{{ asset('images/showcase_mobile_siswa.jpg') }}" 
                                         alt="HanBank Mobile Siswa - Saldo & Mutasi" 
                                         class="w-full h-full object-cover select-none" />
                                </div>
                            </div>

                            {{-- Phone Drop Shadow --}}
                            <div class="h-6 w-3/4 mx-auto bg-slate-950/40 blur-xl rounded-full -mt-3"></div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- 3. OVERVIEW / KEUNGGULAN SISTEM (4 Strategic Pillars) --}}
    <section id="layanan" class="py-16 sm:py-20 bg-white border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Section Title & Subtitle --}}
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider mb-3">
                    Keunggulan Sistem
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Fondasi Perbankan Sekolah yang Tangguh dan Akuntabel
                </h2>
                <p class="mt-4 text-base text-slate-600 leading-relaxed">
                    Membawa standar operasional perbankan komersial ke dalam lingkungan laboratorium akuntansi dan pelayanan keuangan siswa.
                </p>
            </div>

            {{-- 4 Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                {{-- Card 1: Pengelolaan Tabungan Digital --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-300 transition group flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-5 group-hover:bg-emerald-500 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">
                            Pengelolaan Tabungan Digital
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Pemantauan saldo dan riwayat mutasi tabungan siswa. Pemisahan saldo aktif dengan saldo mengendap minimal Rp 10.000.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-emerald-700 flex items-center gap-1">
                        <span>Transparansi Rekening</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>

                {{-- Card 2: Operasional Teller Terintegrasi --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md hover:border-slate-400 transition group flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center mb-5 group-hover:bg-slate-800 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">
                            Operasional Teller Terintegrasi
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Pelayanan setoran tunai kode 101, penarikan aman kode 201 dengan otorisasi PIN 6 digit, dan validasi laci kas loket.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-slate-800 flex items-center gap-1">
                        <span>Layanan Loket Kasir</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>

                {{-- Card 3: Pengawasan dan Akuntabilitas --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md hover:border-amber-300 transition group flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-5 group-hover:bg-amber-500 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">
                            Pengawasan &amp; Akuntabilitas
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Monitoring transaksi harian, approval center pengajuan kas berjenjang, verifikasi daily report, dan rekonsiliasi kas brankas.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-amber-700 flex items-center gap-1">
                        <span>Approval &amp; Audit Trail</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>

                {{-- Card 4: Akses Multi-Platform --}}
                <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-300 transition group flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center mb-5 group-hover:bg-emerald-600 group-hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 tracking-tight mb-2">
                            Akses Multi-Platform
                        </h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Terhubung melalui aplikasi mobile Flutter untuk nasabah dan portal web responsif untuk kebutuhan operasional petugas sekolah.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-semibold text-emerald-700 flex items-center gap-1">
                        <span>Web &amp; Android APK</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- 4. PRODUCT SHOWCASE — MOBILE & WEB DASHBOARD --}}
    <section id="platform" class="py-16 sm:py-24 bg-[#F8FAFC] border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Section Title & Subtitle --}}
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20">
                <span class="inline-block px-3.5 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-3">
                    Product Showcase
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Satu Ekosistem, Dua Platform Terintegrasi
                </h2>
                <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
                    Dirancang untuk mendukung kegiatan perbankan sekolah melalui portal operasional web dan aplikasi mobile.
                </p>
            </div>

            <div class="space-y-20 lg:space-y-28">

                {{-- SHOWCASE 1: WEB OPERATIONAL DASHBOARD --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    
                    {{-- Left Editorial Content --}}
                    <div class="lg:col-span-5 order-2 lg:order-1">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-900 text-white text-xs font-semibold mb-4">
                            <span>Portal Operasional Web</span>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Web Operational Dashboard
                        </h3>
                        <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">
                            Pusat kendali komprehensif bagi petugas loket teller, supervisor, dan administrator untuk melayani transaksi dan memantau kesehatan perbankan sekolah secara terperinci.
                        </p>

                        {{-- Bullet Features --}}
                        <div class="mt-6 space-y-3.5">
                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Pengelolaan Transaksi dan Layanan Teller</h4>
                                    <p class="text-xs text-slate-500">Pencarian rekening via NIS atau scan QR, formulir setoran tunai [101], dan penarikan tunai [201].</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Monitoring Posisi Kas &amp; Laci Kasir</h4>
                                    <p class="text-xs text-slate-500">Pemantauan saldo modal awal, arus kas masuk/keluar, dan fisik uang tersedia secara real-time.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Pengawasan dan Persetujuan Supervisor</h4>
                                    <p class="text-xs text-slate-500">Approval center pengajuan droping kas, rekonsiliasi kas brankas, dan verifikasi lembar daily report.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900">Manajemen Data dan Administrasi</h4>
                                    <p class="text-xs text-slate-500">Impor massal nasabah format CSV, kontrol hak akses RBAC, log audit sistem, dan cetak slip transaksi.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow">
                                <span>Akses Portal Petugas</span>
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>

                    {{-- Right Mockup (Landscape Laptop) --}}
                    <div class="lg:col-span-7 order-1 lg:order-2">
                        <div class="relative bg-white rounded-3xl p-3 sm:p-5 shadow-xl border border-slate-200/90">
                            
                            {{-- Browser Shell Window --}}
                            <div class="rounded-2xl bg-slate-900 p-2 sm:p-2.5 pb-0 shadow-lg border border-slate-800">
                                <div class="flex items-center justify-between pb-2 px-2 text-slate-400 text-xs">
                                    <div class="text-[11px] font-semibold text-slate-400">Loket Kasir Web</div>
                                    <div class="bg-slate-800/80 rounded px-2.5 py-0.5 text-[11px] font-mono text-slate-300">
                                        Pelayanan Loket Kasir — SMKN 1 Bantul
                                    </div>
                                    <span class="text-[10px] text-emerald-400 font-semibold">Aktif</span>
                                </div>
                                <div class="rounded-t-lg overflow-hidden aspect-[1024/507] bg-slate-50 border border-slate-800">
                                    <img src="{{ asset('images/showcase_web_teller.png') }}" 
                                         alt="Screenshot Dashboard Teller Operasional" 
                                         class="w-full h-full object-cover object-top select-none" />
                                </div>
                            </div>

                            {{-- Bottom Caption Tag --}}
                            <div class="mt-3 flex items-center justify-between text-xs text-slate-500 px-2">
                                <span>Antarmuka Web Loket Pelayanan Kasir Bank Mini</span>
                                <span class="font-semibold text-slate-700">Resolusi Desktop / Laptop</span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- SHOWCASE 2: HANBANK MOBILE SISWA --}}
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    
                    {{-- Left Mockup (Portrait Smartphone) --}}
                    <div class="lg:col-span-6 flex justify-center">
                        <div class="relative max-w-[280px] sm:max-w-[320px] w-full">
                            
                            {{-- Phone Outer Frame --}}
                            <div class="relative bg-slate-950 rounded-[3rem] p-3 shadow-2xl border-4 border-slate-800 ring-1 ring-slate-700/80">
                                
                                {{-- Phone Speaker Notch --}}
                                <div class="absolute top-4 left-1/2 -translate-x-1/2 w-16 sm:w-20 h-1 bg-slate-800 rounded-full z-30"></div>

                                {{-- Screen Aspect --}}
                                <div class="overflow-hidden rounded-[2.5rem] aspect-[502/1024] bg-white border border-slate-900">
                                    <img src="{{ asset('images/showcase_mobile_siswa.jpg') }}" 
                                         alt="Screenshot HanBank Mobile Nasabah Siswa" 
                                         class="w-full h-full object-cover select-none" />
                                </div>
                            </div>

                            {{-- Ambient Shadow --}}
                            <div class="h-8 w-4/5 mx-auto bg-slate-900/30 blur-2xl rounded-full -mt-4"></div>
                        </div>
                    </div>

                    {{-- Right Editorial Content --}}
                    <div class="lg:col-span-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold mb-4">
                            <span>Aplikasi Mobile Nasabah (Flutter)</span>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            HanBank Mobile
                        </h3>
                        <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">
                            Aplikasi mobile interaktif bagi nasabah siswa untuk memantau tabungan, melakukan verifikasi kunjungan loket, serta menumbuhkan kebiasaan hidup hemat melalui gamifikasi literasi keuangan.
                        </p>

                        {{-- Feature Matrix --}}
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                                <div class="text-emerald-600 font-bold text-xs uppercase tracking-wider mb-1">01. Saldo &amp; QR</div>
                                <div class="text-sm font-bold text-slate-900">Monitoring Saldo Tabungan</div>
                                <div class="text-xs text-slate-500 mt-1">Pemisahan saldo aktif dan simpanan wajib, disertai kartu QRIS digital cepat.</div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                                <div class="text-emerald-600 font-bold text-xs uppercase tracking-wider mb-1">02. Mutasi &amp; Grafik</div>
                                <div class="text-sm font-bold text-slate-900">Buku Mutasi Real-Time</div>
                                <div class="text-xs text-slate-500 mt-1">Catatan riwayat transaksi setoran/penarikan dan grafik arus kas bulanan.</div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                                <div class="text-emerald-600 font-bold text-xs uppercase tracking-wider mb-1">03. Target Nabung</div>
                                <div class="text-sm font-bold text-slate-900">Saving Goals &amp; Impian</div>
                                <div class="text-xs text-slate-500 mt-1">Edukasi pengelolaan dana siswa dengan persentase progres tabungan.</div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                                <div class="text-emerald-600 font-bold text-xs uppercase tracking-wider mb-1">04. Keamanan Rekening</div>
                                <div class="text-sm font-bold text-slate-900">Emergency Freeze &amp; PIN</div>
                                <div class="text-xs text-slate-500 mt-1">Blokir kartu mandiri saat kartu hilang, otorisasi PIN 6 digit, dan log sesi aktif.</div>
                            </div>

                        </div>

                        <div class="mt-8">
                            <a href="{{ route('download') }}" 
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-slate-900 bg-emerald-400 hover:bg-emerald-300 transition shadow">
                                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh APK HanBank Mobile</span>
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

    {{-- 5. FEATURE HIGHLIGHTS — FITUR BERDASARKAN PENGGUNA (PRD RBAC) --}}
    <section id="fitur" class="py-16 sm:py-24 bg-white border-b border-slate-200/70" x-data="{ activeTab: 'siswa' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Section Title & Subtitle --}}
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider mb-3">
                    Hak Akses &amp; Peran Sistem
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Fitur Khusus Berdasarkan Pengguna Sistem
                </h2>
                <p class="mt-4 text-base text-slate-600 leading-relaxed">
                    Setiap peran dalam ekosistem memiliki hak akses dan modul operasional yang terisolasi secara akuntabel sesuai standar perbankan.
                </p>
            </div>

            {{-- Role Switcher Tabs (Alpine.js) --}}
            <div class="flex items-center justify-center mb-10 overflow-x-auto pb-2">
                <div class="inline-flex p-1.5 rounded-2xl bg-slate-100 border border-slate-200/80 gap-1 sm:gap-2">
                    <button type="button" 
                            @click="activeTab = 'siswa'"
                            :class="activeTab === 'siswa' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition">
                        Nasabah / Siswa
                    </button>

                    <button type="button" 
                            @click="activeTab = 'teller'"
                            :class="activeTab === 'teller' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition">
                        Petugas Teller
                    </button>

                    <button type="button" 
                            @click="activeTab = 'supervisor'"
                            :class="activeTab === 'supervisor' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition">
                        Supervisor
                    </button>

                    <button type="button" 
                            @click="activeTab = 'admin'"
                            :class="activeTab === 'admin' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                            class="px-4 py-2 rounded-xl text-xs sm:text-sm font-bold transition">
                        Admin &amp; Avalis
                    </button>
                </div>
            </div>

            {{-- TAB 1: NASABAH / SISWA --}}
            <div x-show="activeTab === 'siswa'" 
                 x-cloak
                 x-transition:enter="transition-opacity ease-out duration-150" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 class="space-y-6">
                <div class="bg-gradient-to-r from-emerald-50/60 to-slate-50 p-6 sm:p-8 rounded-3xl border border-emerald-100">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100/70 px-2.5 py-1 rounded-md">Kanal Nasabah Siswa</span>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">Layanan Perbankan Siswa Mandiri</h3>
                            <p class="text-sm text-slate-600 mt-1">Memberikan transparansi saldo tabungan dan membiasakan literasi finansial terencana bagi pelajar.</p>
                        </div>
                        <a href="{{ route('download') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm shrink-0">
                            Unduh Aplikasi Siswa
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Dashboard Saldo Tabungan</div>
                            <p class="text-xs text-slate-500">Pemisahan otomatis antara saldo yang dapat ditarik dan saldo mengendap minimal.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Kartu Digital QR Rekening</div>
                            <p class="text-xs text-slate-500">Identitas nasabah berbasis QRIS digital untuk transaksi cepat di loket kasir.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Riwayat &amp; Buku Mutasi</div>
                            <p class="text-xs text-slate-500">Catatan debit dan kredit tabungan lengkap dengan timestamp dan status transaksi.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Saving Goals (Target Nabung)</div>
                            <p class="text-xs text-slate-500">Rencanakan target menabung untuk kebutuhan sekolah dengan estimasi capaian.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Badges &amp; Gamifikasi</div>
                            <p class="text-xs text-slate-500">Lencana apresiasi kebiasaan hemat guna memotivasi siswa menabung rutin.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Grafik Analitik Arus Kas</div>
                            <p class="text-xs text-slate-500">Visualisasi statistik total setoran dan penarikan bulanan secara informatif.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Emergency Freeze Mandiri</div>
                            <p class="text-xs text-slate-500">Kunci status rekening instan dari aplikasi jika kartu atau ponsel hilang.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Manajemen Keamanan Akun</div>
                            <p class="text-xs text-slate-500">Ubah PIN 6 digit mandiri, log sesi perangkat aktif, dan pemulihan OTP.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: PETUGAS TELLER --}}
            <div x-show="activeTab === 'teller'" 
                 x-cloak
                 x-transition:enter="transition-opacity ease-out duration-150" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 class="space-y-6">
                <div class="bg-gradient-to-r from-slate-100 to-slate-50 p-6 sm:p-8 rounded-3xl border border-slate-200">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-800 bg-slate-200/80 px-2.5 py-1 rounded-md">Petugas Loket Kasir</span>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">Pelayanan Transaksi &amp; Kas Loket</h3>
                            <p class="text-sm text-slate-600 mt-1">Efisiensi loket dengan verifikasi ganda, pencegahan selisih kas, dan pelaporan harian.</p>
                        </div>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm shrink-0">
                            Masuk Loket Teller
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Pencarian &amp; Identifikasi Nasabah</div>
                            <p class="text-xs text-slate-500">Cari rekening cepat berdasarkan NIS, nomor rekening, atau nama siswa.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Pemindaian QR &amp; Barcode</div>
                            <p class="text-xs text-slate-500">Pindai kode QR kartu siswa menggunakan kamera web atau scanner barcode fisik.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Setoran Tunai (Kode 101)</div>
                            <p class="text-xs text-slate-500">Pencatatan kas masuk, penambahan saldo nasabah, dan pencetakan slip setoran.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Penarikan Tunai (Kode 201)</div>
                            <p class="text-xs text-slate-500">Penarikan uang tunai dengan verifikasi PIN 6 digit dan validasi saldo laci.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Validasi Kas &amp; Anti-Kas Minus</div>
                            <p class="text-xs text-slate-500">Sistem memblokir penarikan jika uang fisik pada laci teller tidak mencukupi.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Void Transaksi Resmi</div>
                            <p class="text-xs text-slate-500">Pembatalan kesalahan input transaksi dengan pencatatan jurnal balik otomatis.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Pengajuan Droping Kas Loket</div>
                            <p class="text-xs text-slate-500">Permintaan tambahan modal operasional laci kasir langsung ke supervisor.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Penutupan Kas (Daily Report)</div>
                            <p class="text-xs text-slate-500">Hitung fisik kas akhir shift dan submit rekonsiliasi kas harian loket.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Cetak Bukti Slip Transaksi</div>
                            <p class="text-xs text-slate-500">Cetak lembar bukti slip setoran dan penarikan standar tanda terima bank.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: SUPERVISOR --}}
            <div x-show="activeTab === 'supervisor'" 
                 x-cloak
                 x-transition:enter="transition-opacity ease-out duration-150" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 class="space-y-6">
                <div class="bg-gradient-to-r from-amber-50/70 to-slate-50 p-6 sm:p-8 rounded-3xl border border-amber-200/80">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-800 bg-amber-100 px-2.5 py-1 rounded-md">Pengawasan &amp; Manajerial</span>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-2">Approval Center &amp; Akuntabilitas Keuangan</h3>
                            <p class="text-sm text-slate-600 mt-1">Otorisasi berjenjang prinsip maker-checker, rekonsiliasi brankas, dan laporan akuntansi.</p>
                        </div>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm shrink-0">
                            Masuk Supervisor
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Approval Center Berjenjang</div>
                            <p class="text-xs text-slate-500">Persetujuan atau penolakan pengajuan droping kas loket dengan locking aman.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Verifikasi Daily Report Closing</div>
                            <p class="text-xs text-slate-500">Validasi lembar penutupan kas harian teller sebelum pembukuan diarsipkan.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Monitoring Aktivitas Kasir</div>
                            <p class="text-xs text-slate-500">Pantau transaksi yang sedang berlangsung pada setiap loket aktif.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Rekonsiliasi Kas &amp; Brankas</div>
                            <p class="text-xs text-slate-500">Pencocokan saldo mutasi sistem dengan uang fisik brankas utama.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Monitoring Saldo Brankas</div>
                            <p class="text-xs text-slate-500">Pemantauan ketersediaan likuiditas brankas sebelum menyetujui modal loket.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Laporan Akuntansi Double-Entry</div>
                            <p class="text-xs text-slate-500">Buku jurnal otomatis debit-kredit untuk neraca saldo dan laporan keuangan.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Rekapitulasi Saldo per Kelas</div>
                            <p class="text-xs text-slate-500">Laporan agregasi tabungan siswa per program keahlian dan rombongan belajar.</p>
                        </div>
                        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
                            <div class="text-xs font-bold text-slate-900 mb-1">Notifikasi Operasional Real-time</div>
                            <p class="text-xs text-slate-500">Pemberitahuan instan saat ada pengajuan kas atau penutupan kasir baru.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 4: ADMINISTRATOR DAN AVALIS --}}
            <div x-show="activeTab === 'admin'" 
                 x-cloak
                 x-transition:enter="transition-opacity ease-out duration-150" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 class="space-y-6">
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white p-6 sm:p-8 rounded-3xl border border-slate-700 shadow-xl">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 bg-slate-800 px-2.5 py-1 rounded-md border border-slate-700">Tata Kelola &amp; Ketertiban Loket</span>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-white mt-2">Administrator Sistem &amp; Petugas Avalis</h3>
                            <p class="text-sm text-slate-400 mt-1">Pengendalian integritas sistem, data master perbankan, dan ketertiban piket jaga loket.</p>
                        </div>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-900 bg-white hover:bg-slate-100 transition shadow-sm shrink-0">
                            Masuk Administrasi
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Sub-panel: Administrator --}}
                        <div class="bg-slate-800/80 p-5 rounded-2xl border border-slate-700">
                            <h4 class="text-sm font-bold text-emerald-400 uppercase tracking-wider mb-3">Wewenang Administrator Utama</h4>
                            <ul class="space-y-2.5 text-xs text-slate-300">
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Manajemen Akun &amp; RBAC:</strong> Pembuatan dan pengelolaan peran petugas sekolah.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Manajemen Data Nasabah &amp; CSV:</strong> Impor data siswa massal format spreadsheet.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Persetujuan Reset PIN &amp; Password:</strong> Verifikasi tiket pemulihan kredensial.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Audit Trail &amp; Log Keamanan:</strong> Rekaman audit komprehensif tanpa modifikasi.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Monitoring Kesehatan Sistem:</strong> Pengecekan latensi database dan server.</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Sub-panel: Avalis (Piket Jaga Loket Siswa) --}}
                        <div class="bg-slate-800/80 p-5 rounded-2xl border border-slate-700">
                            <h4 class="text-sm font-bold text-amber-400 uppercase tracking-wider mb-3">Tugas Petugas Avalis (Piket Jaga)</h4>
                            <ul class="space-y-2.5 text-xs text-slate-300">
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Presensi Shift Loket:</strong> Check-in dan check-out shift jaga loket (Pagi / Siang).</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Sinkronisasi Jadwal Petugas:</strong> Pemantauan petugas teller yang bertugas hari ini.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Verifikasi Kunjungan Siswa:</strong> Scan kartu atau barcode siswa yang datang bertransaksi.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>Manajemen Antrean Loket:</strong> Menjaga ketertiban pelayanan nasabah di laboratorium bank mini.</span>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- 6. SECURITY & RELIABILITY --}}
    <section id="keamanan" class="py-16 sm:py-24 bg-[#F8FAFC] border-b border-slate-200/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Section Title & Subtitle --}}
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider mb-3">
                    Keamanan Finansial
                </span>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Keamanan dan Akuntabilitas dalam Setiap Transaksi
                </h2>
                <p class="mt-4 text-base text-slate-600 leading-relaxed">
                    Dibangun dengan prinsip kehati-hatian perbankan komersial guna menjamin integritas saldo nasabah, keabsahan transaksi, dan perlindungan kas sekolah.
                </p>
            </div>

            {{-- 6 Security Pillars Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                {{-- Security 1: Role-Based Access Control --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Autentikasi &amp; RBAC Terisolasi</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Pemisahan wewenang ketat antara Administrator, Supervisor, Teller, Avalis, dan Nasabah untuk mencegah konflik wewenang.
                    </p>
                </div>

                {{-- Security 2: PIN 6 Digit Transaksi --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Proteksi PIN 6 Digit Otorisasi</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Setiap penarikan dana tunai di loket wajib diverifikasi menggunakan PIN kartu 6 digit rahasia yang terenkripsi aman di basis data.
                    </p>
                </div>

                {{-- Security 3: Pemulihan Password via OTP --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Pemulihan Akun via OTP Mandiri</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Mekanisme pemulihan kata sandi aman melalui One-Time Password (OTP) 6 digit terenkripsi dan verifikasi berjenjang admin.
                    </p>
                </div>

                {{-- Security 4: Validasi Saldo Mengendap --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Validasi Saldo Mengendap Wajib</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Batas saldo simpanan minimal Rp 10.000 dijaga otomatis oleh sistem agar rekening siswa tetap aktif dan membatasi penarikan berlebih.
                    </p>
                </div>

                {{-- Security 5: Proteksi Kas Minus --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Proteksi Kas Minus &amp; Idempotency</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Pencegahan saldo laci kasir defisit saat penarikan tunai serta proteksi kunci idempotensi untuk mencegah mutasi ganda tak disengaja.
                    </p>
                </div>

                {{-- Security 6: Audit Trail & Maker-Checker --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md transition">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 mb-2">Audit Trail &amp; Maker-Checker</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Seluruh aktivitas operasional terekam dalam log audit tak dapat dihapus, diperkuat persetujuan supervisor untuk pengeluaran kas.
                    </p>
                </div>

            </div>

        </div>
    </section>

    {{-- 7. FAQ (INTERACTIVE ACCORDION) --}}
    <section id="faq" class="py-16 sm:py-24 bg-white border-b border-slate-200/70">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Section Title & Subtitle --}}
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase tracking-wider mb-3">
                    Pertanyaan Umum
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Frequently Asked Questions
                </h2>
                <p class="mt-3 text-sm sm:text-base text-slate-600">
                    Informasi penting seputar penggunaan dan akses Sistem Bank Mini Sekolah.
                </p>
            </div>

            {{-- Accordion Items --}}
            <div class="space-y-4" x-data="{ selectedFaq: null }">
                
                {{-- FAQ 1 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition bg-white shadow-sm">
                    <button type="button" 
                            @click="selectedFaq = selectedFaq === 1 ? null : 1" 
                            class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-900 text-sm sm:text-base hover:bg-slate-50 transition">
                        <span>Apa itu Sistem Bank Mini Sekolah?</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': selectedFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="selectedFaq === 1" x-collapse x-cloak class="px-5 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        Sistem Bank Mini Sekolah adalah platform perbankan mikro terpadu (Core Micro-Banking System) yang memfasilitasi transaksi tabungan siswa nyata di sekolah sekaligus berfungsi sebagai sarana simulator operasional perbankan berstandar akuntansi nasional untuk kompetensi keahlian siswa SMK.
                    </div>
                </div>

                {{-- FAQ 2 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition bg-white shadow-sm">
                    <button type="button" 
                            @click="selectedFaq = selectedFaq === 2 ? null : 2" 
                            class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-900 text-sm sm:text-base hover:bg-slate-50 transition">
                        <span>Siapa saja yang dapat menggunakan sistem ini?</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': selectedFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="selectedFaq === 2" x-collapse x-cloak class="px-5 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        Sistem ini dirancang dengan hak akses terpisah (Role-Based Access Control) untuk 5 peran utama: Nasabah (siswa penabung), Petugas Loket (Teller), Supervisor (Kepala Bank Mini), Petugas Avalis (siswa piket jaga loket), dan Administrator Utama.
                    </div>
                </div>

                {{-- FAQ 3 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition bg-white shadow-sm">
                    <button type="button" 
                            @click="selectedFaq = selectedFaq === 3 ? null : 3" 
                            class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-900 text-sm sm:text-base hover:bg-slate-50 transition">
                        <span>Bagaimana cara siswa melihat saldo tabungan?</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': selectedFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="selectedFaq === 3" x-collapse x-cloak class="px-5 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        Siswa dapat memantau saldo tabungan secara mandiri dan real-time melalui aplikasi Android HanBank Mobile. Aplikasi menampilkan pemisahan transparan antara saldo yang dapat ditarik dengan batas simpanan mengendap minimal Rp 10.000.
                    </div>
                </div>

                {{-- FAQ 4 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition bg-white shadow-sm">
                    <button type="button" 
                            @click="selectedFaq = selectedFaq === 4 ? null : 4" 
                            class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-900 text-sm sm:text-base hover:bg-slate-50 transition">
                        <span>Bagaimana cara mengakses aplikasi mobile?</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': selectedFaq === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="selectedFaq === 4" x-collapse x-cloak class="px-5 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        Siswa masuk ke aplikasi HanBank Mobile menggunakan username atau Nomor Induk Siswa (NIS) serta kata sandi akun yang telah didaftarkan secara resmi oleh administrator bank mini sekolah.
                    </div>
                </div>

                {{-- FAQ 5 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition bg-white shadow-sm">
                    <button type="button" 
                            @click="selectedFaq = selectedFaq === 5 ? null : 5" 
                            class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-900 text-sm sm:text-base hover:bg-slate-50 transition">
                        <span>Bagaimana cara mengunduh aplikasi Android?</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': selectedFaq === 5 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="selectedFaq === 5" x-collapse x-cloak class="px-5 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        Aplikasi mobile Android dapat diunduh melalui tombol Unduh Aplikasi di navigasi atau langsung mengunjungi halaman <a href="{{ route('download') }}" class="text-emerald-700 font-semibold underline">Unduh APK Mobile</a>.
                    </div>
                </div>

                {{-- FAQ 6 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition bg-white shadow-sm">
                    <button type="button" 
                            @click="selectedFaq = selectedFaq === 6 ? null : 6" 
                            class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-900 text-sm sm:text-base hover:bg-slate-50 transition">
                        <span>Apa fungsi dashboard operasional web?</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': selectedFaq === 6 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="selectedFaq === 6" x-collapse x-cloak class="px-5 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        Dashboard operasional web memfasilitasi aktivitas inti perbankan: pelayanan setoran dan penarikan oleh teller, persetujuan droping kas dan closing kasir oleh supervisor, serta audit log dan manajemen data master oleh administrator.
                    </div>
                </div>

                {{-- FAQ 7 --}}
                <div class="border border-slate-200 rounded-2xl overflow-hidden transition bg-white shadow-sm">
                    <button type="button" 
                            @click="selectedFaq = selectedFaq === 7 ? null : 7" 
                            class="w-full flex items-center justify-between p-5 text-left font-bold text-slate-900 text-sm sm:text-base hover:bg-slate-50 transition">
                        <span>Bagaimana cara masuk ke dalam sistem web?</span>
                        <svg class="w-5 h-5 text-slate-500 transition-transform duration-200" :class="{ 'rotate-180': selectedFaq === 7 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="selectedFaq === 7" x-collapse x-cloak class="px-5 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-100 pt-3">
                        Petugas sekolah dapat menekan tombol Masuk Dashboard di bilah menu navigasi atau mengunjungi halaman <a href="{{ route('login') }}" class="text-emerald-700 font-semibold underline">Portal Masuk</a>, lalu memasukkan username dan kata sandi petugas yang telah terdaftar.
                    </div>
                </div>

            </div>

        </div>
    </section>

    {{-- 8. FINAL CTA SECTION (Premium Navy Banner with Grid Pattern) --}}
    <section class="relative bg-slate-900 text-white py-16 sm:py-20 overflow-hidden">
        
        {{-- Thin Grid Pattern (matching Reference 2 Login page) --}}
        <div class="absolute inset-0 opacity-[0.05] pointer-events-none" 
             style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>
        
        {{-- Subtle Emerald Aura in Background --}}
        <div class="absolute -top-24 right-1/4 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight leading-tight max-w-3xl mx-auto">
                Mulai Akses Ekosistem Bank Mini Sekolah
            </h2>

            <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto leading-relaxed">
                Kelola layanan perbankan sekolah melalui sistem yang terintegrasi, transparan, dan mudah diakses.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('download') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-bold text-slate-950 bg-emerald-400 hover:bg-emerald-300 shadow-lg shadow-emerald-500/20 transition active:scale-[0.99] text-sm sm:text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh Aplikasi Mobile</span>
                </a>

                <a href="{{ route('login') }}" 
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 shadow-sm transition active:scale-[0.99] text-sm sm:text-base">
                    <span>Masuk Dashboard Petugas</span>
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

        </div>
    </section>

    {{-- 9. FOOTER --}}
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
                        <li><a href="#hero" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="#layanan" class="hover:text-white transition">Layanan Utama</a></li>
                        <li><a href="#platform" class="hover:text-white transition">Platform Terintegrasi</a></li>
                        <li><a href="#fitur" class="hover:text-white transition">Fitur Berdasarkan Peran</a></li>
                    </ul>
                </div>

                {{-- Column 3: Security & Access Links --}}
                <div class="md:col-span-3 lg:col-span-4">
                    <div class="text-xs font-bold uppercase tracking-wider text-white mb-3">Akses &amp; Bantuan</div>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="#keamanan" class="hover:text-white transition">Standar Keamanan Transaksi</a></li>
                        <li><a href="#faq" class="hover:text-white transition">Pertanyaan Umum (FAQ)</a></li>
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
