<!-- resources/views/download.blade.php -->
{{-- Download Page for Bank Mini Sekolah Mobile App --}}
@extends('layouts.app')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Unduh Aplikasi Mobile') }}
    </h2>
@endsection

@section('content')
<div class="bg-white">
    {{-- Hero section – dark gradient for consistency */}
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
                <button type="button" class="px-6 py-3 bg-gray-400 text-white rounded-md cursor-not-allowed" disabled>
                    APK akan tersedia segera
                </button>
                <a href="{{ route('download') }}" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400 focus:outline-none rounded-md text-white transition">
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

    {{-- Mobile mock‑up preview */}
    <section class="max-w-5xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center">
        <img src="{{ asset('images/mobile_mockup.png') }}" alt="Mobile app preview" class="mx-auto mb-6 max-w-xs hidden md:block" />
        <p class="text-gray-600 mb-4">
            Klik tombol di atas untuk menerima notifikasi ketika APK sudah siap.
        </p>
    </section>
</div>
@endsection