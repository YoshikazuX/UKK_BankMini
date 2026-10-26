<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - LKM Mitra Siswa Abadi</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-white min-h-screen text-slate-800 antialiased selection:bg-slate-800 selection:text-white">

    <div class="min-h-screen lg:h-screen flex flex-col lg:flex-row lg:overflow-hidden">

        <!-- ============================================================== -->
        <!-- PANEL KIRI: FORM LOGIN BERSIH & FOKUS PADA TUGAS PETUGAS        -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-1/2 h-full flex flex-col justify-center items-center p-6 sm:p-10 lg:p-12 xl:p-16 bg-white lg:overflow-y-auto">

            <!-- CONTAINER FORM -->
            <div class="max-w-md w-full mx-auto my-auto py-6">

                <!-- JUDUL FORM & SUBJUDUL -->
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight mb-1.5">Masuk ke Akun</h1>
                <p class="text-sm text-slate-500 mb-6">Silakan masuk menggunakan akun petugas Anda.</p>

                <!-- NOTIFIKASI ERROR / SUKSES -->
                @if (session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 rounded-lg text-sm mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-3.5 py-2.5 rounded-lg text-sm mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 rounded-lg text-sm mb-4">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- FORM LOGIN -->
                <form action="{{ url('/login') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- FIELD USERNAME -->
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Username
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            value="{{ old('username') }}" 
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 focus:border-slate-700 focus:ring-2 focus:ring-slate-900/10 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none transition" 
                            required 
                            placeholder="Masukkan username" 
                            autocomplete="username"
                            autofocus
                        >
                    </div>

                    <!-- FIELD KATA SANDI -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="w-full px-3.5 py-2.5 pr-10 bg-white border border-slate-300 focus:border-slate-700 focus:ring-2 focus:ring-slate-900/10 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none transition" 
                                required 
                                placeholder="Masukkan kata sandi" 
                                autocomplete="current-password"
                            >
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()" 
                                id="toggle-pw-btn" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                title="Tampilkan / Sembunyikan Kata Sandi"
                            >
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-slash-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- KONTROL: INGAT SESI & LUPA KATA SANDI -->
                    <div class="flex items-center justify-between text-sm pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-slate-900 focus:ring-slate-900 border-slate-300">
                            <span class="text-sm text-slate-600">Ingat Sesi Saya</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 hover:underline">
                            Lupa kata sandi?
                        </a>
                    </div>

                    <!-- TOMBOL UTAMA MASUK (WARNA SOLID) -->
                    <button 
                        type="submit" 
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 mt-2"
                    >
                        Masuk
                    </button>
                </form>

                <!-- SATU BARIS TEKS BANTUAN DI BAWAH TOMBOL -->
                @php
                    $cleanAdminWa = preg_replace('/[^0-9]/', '', $adminPhone ?? '081234567890');
                    if (str_starts_with($cleanAdminWa, '0')) {
                        $cleanAdminWa = '62' . substr($cleanAdminWa, 1);
                    }
                    $waDefaultMsg = urlencode("Halo Admin LKM Mitra Siswa Abadi, saya mengalami kendala masuk akun saya. Mohon bantuan verifikasi.");
                @endphp
                <p class="text-xs text-slate-500 text-center mt-6">
                    Terkendala akses akun? <a href="https://wa.me/{{ $cleanAdminWa }}?text={{ $waDefaultMsg }}" target="_blank" rel="noopener noreferrer" class="text-slate-800 font-semibold hover:underline">Hubungi Administrator</a>.
                </p>

            </div>

        </div>

        <!-- ============================================================== -->
        <!-- PANEL KANAN: SOLID DARK, GRID PATTERN, JUSTIFY-BETWEEN          -->
        <!-- ============================================================== -->
        <div class="hidden lg:flex lg:w-1/2 bg-slate-900 text-white flex-col justify-between p-8 sm:p-12 lg:p-12 xl:p-16 relative overflow-hidden">

            <!-- POLA GRID TIPIS (OPACITY-5) PADA BACKGROUND GELAP -->
            <div class="absolute inset-0 opacity-5 pointer-events-none" style="background-image: linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px); background-size: 32px 32px;"></div>

            <!-- BAGIAN ATAS: LOGO & NAMA APLIKASI -->
            <div class="relative z-10 flex items-center space-x-3 shrink-0">
                <div class="w-10 h-10 rounded-lg bg-slate-800 text-white border border-slate-700/80 flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                    </svg>
                </div>
                <span class="text-base font-bold text-white tracking-tight">LKM Mitra Siswa Abadi</span>
            </div>

            <!-- BAGIAN TENGAH / BAWAH: BLOK TEKS DESKRIPSI -->
            <div class="relative z-10 max-w-lg my-auto py-8">
                <h2 class="text-3xl xl:text-4xl font-extrabold tracking-tight text-white mb-3 leading-tight">
                    Sistem Perbankan &amp; Kas Bank Mini
                </h2>
                <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                    Platform terpadu untuk pengelolaan tabungan siswa, kas operasional teller, dan pelaporan keuangan sekolah terpercaya.
                </p>
            </div>

            <!-- BAGIAN PALING BAWAH: COPYRIGHT (PINDAHAN DARI PANEL KIRI) -->
            <div class="relative z-10 shrink-0">
                <p class="text-xs text-slate-500">
                    &copy; {{ date('Y') }} LKM Mitra Siswa Abadi
                </p>
            </div>

        </div>

    </div>

    <!-- SCRIPT INTERAKTIF SHOW / HIDE PASSWORD -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>

</body>
</html>
