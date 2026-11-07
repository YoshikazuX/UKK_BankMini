<!-- resources/views/download.blade.php -->
<!-- Download Page for Bank Mini Sekolah Mobile App -->
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Unduh Aplikasi Mobile') }}
    </h2>
@endsection

@section('content')
<div class="bg-white">
    <!-- Hero section -->
    <section class="relative bg-gradient-to-r from-slate-800 to-slate-900 text-white py-16 px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight mb-4">
                Aplikasi Android Bank Mini Sekolah
            </h1>
            <p class="mt-4 text-lg sm:text-xl text-slate-200">
                Versi terbaru: <strong>v1.0.0</strong> (dirilis 1 Nov 2026)
            </p>
            <p class="mt-6 text-base sm:text-lg text-slate-200 max-w-2xl mx-auto">
                Aplikasi memungkinkan siswa, orang tua, dan staf mengakses semua fitur perbankan langsung dari ponsel.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">
                <button type="button" class="px-6 py-3 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed shadow-sm" disabled>
                    APK akan tersedia segera
                </button>
                <a href="{{ route('download') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400 focus:outline-none rounded-lg text-white font-medium shadow-sm transition">
                    Notifikasi Ketersediaan
                </a>
            </div>
        </div>
        <!-- subtle dotted background -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <svg class="absolute top-0 left-1/2 -translate-x-1/2" width="800" height="800" fill="none" viewBox="0 0 800 800" aria-hidden="true">
                <circle cx="400" cy="400" r="400" fill="rgba(255,255,255,0.05)" />
            </svg>
        </div>
    </section>

    <!-- Mobile mock-up preview -->
    <section class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center">
        <!-- Smartphone Frame Preview -->
        <div class="mx-auto mb-6 max-w-[270px]">
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

        <p class="text-gray-600 mb-4">
            Klik tombol di atas untuk menerima notifikasi ketika APK sudah siap.
        </p>
    </section>
</div>
@endsection
