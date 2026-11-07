<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Bank Mini Sekolah - LKM Mitra Siswa Abadi SMK Negeri 1 Bantul' }}</title>

        <!-- Favicon / Logo Web -->
        <link rel="icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Tailwind CSS & Scripts -->
        <script src="https://cdn.tailwindcss.com"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-800 selection:bg-slate-900 selection:text-white">
        <div class="min-h-screen {{ (request()->is('/') || request()->is('download')) ? 'bg-[#F8FAFC]' : 'bg-gray-100' }}">
            @if (!request()->is('/') && !request()->is('download'))
                @include('layouts.navigation')
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </body>
</html>
