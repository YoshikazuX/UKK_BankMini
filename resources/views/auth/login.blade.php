<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LKM Mitra Siswa Abadi</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-mesh {
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(30, 41, 59, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, rgba(15, 23, 42, 1) 0, transparent 50%),
                radial-gradient(at 50% 100%, rgba(15, 118, 110, 0.15) 0, transparent 60%);
        }
    </style>
</head>
<body class="bg-mesh min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 antialiased selection:bg-slate-700 selection:text-white">

    <div class="w-full max-w-md">

        <!-- LOGO & HEADER KONSISTEN DENGAN DASHBOARD -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-800 text-emerald-400 border border-slate-700 shadow-xl mb-3 ring-4 ring-slate-800/50">
                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-wider">LKM Mitra Siswa Abadi</h1>
            <p class="text-slate-400 text-xs sm:text-sm mt-1">Sistem Bank Mini Sekolah & Layanan Perbankan</p>
            <div class="mt-2.5">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-800 text-emerald-400 border border-slate-700 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span>
                    Portal Autentikasi Resmi
                </span>
            </div>
        </div>

        <!-- CARD FORM UTAMA -->
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 overflow-hidden">

            <!-- NOTIFIKASI ERROR / SUCCESS -->
            @if (session('error'))
                <div class="bg-rose-50 border-b border-rose-200 text-rose-800 p-4 flex items-start space-x-3" role="alert">
                    <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <div class="text-xs sm:text-sm">
                        <p class="font-bold">Gagal Masuk</p>
                        <p class="mt-0.5 text-rose-700 leading-relaxed">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-emerald-50 border-b border-emerald-200 text-emerald-800 p-4 flex items-start space-x-3" role="alert">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-xs sm:text-sm">
                        <p class="font-bold text-emerald-900">Operasi Berhasil</p>
                        <p class="mt-0.5 text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="bg-amber-50 border-b border-amber-200 text-amber-900 p-4 text-xs sm:text-sm">
                    <p class="font-bold mb-1">Periksa kembali data masukan:</p>
                    <ul class="list-disc list-inside text-amber-800 space-y-0.5">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="p-6 sm:p-8">

                <!-- INFORMASI REGISTRASI TERTUTUP (KARENA AKUN HANYA DIBUATKAN OLEH ADMIN) -->
                <div class="mb-6 p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-600 text-xs flex items-start space-x-2.5">
                    <svg class="w-4 h-4 text-slate-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="leading-relaxed">
                        <span class="font-bold text-slate-800">Akses Terkelola:</span> Registrasi akun baru (Admin, Supervisor, Teller, & Nasabah) didaftarkan langsung oleh Administrator. Pendaftaran mandiri ditiadakan.
                    </p>
                </div>

                <!-- FORM LOGIN -->
                <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                    @csrf

                    <!-- FIELD USERNAME -->
                    <div>
                        <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Username Pengguna
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                name="username" 
                                id="username" 
                                value="{{ old('username') }}" 
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition duration-150" 
                                required 
                                placeholder="Contoh: admin, teller1, spv1" 
                                autocomplete="username"
                                autofocus
                            >
                        </div>
                    </div>

                    <!-- FIELD PASSWORD DENGAN FITUR SHOW / HIDE -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kata Sandi
                            </label>
                            <!-- LINK LUPA PASSWORD KONSISTEN DENGAN PERMINTAAN USER -->
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline decoration-slate-400 hover:decoration-slate-800 transition">
                                Lupa Password?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="w-full pl-11 pr-11 py-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition duration-150" 
                                required 
                                placeholder="••••••••" 
                                autocomplete="current-password"
                            >
                            <!-- TOMBOL SHOW / HIDE PASSWORD -->
                            <button 
                                type="button" 
                                onclick="togglePasswordVisibility()" 
                                id="toggle-pw-btn" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 focus:outline-none"
                                title="Lihat / Sembunyikan Password"
                            >
                                <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-slash-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- REMEMBER ME -->
                    <div class="flex items-center justify-between text-xs text-slate-600">
                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-slate-800 focus:ring-slate-800 border-slate-300">
                            <span>Ingat sesi masuk saya</span>
                        </label>
                    </div>

                    <!-- TOMBOL SUBMIT KONSISTEN DENGAN TEMA SLATE DASHBOARD -->
                    <button 
                        type="submit" 
                        class="w-full bg-slate-800 hover:bg-slate-900 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center space-x-2 text-sm focus:outline-none focus:ring-4 focus:ring-slate-300"
                    >
                        <span>Masuk ke Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </form>

                <!-- PEMISAH OPSI BANTUAN -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-3 text-slate-400 font-semibold tracking-wider">Pusat Bantuan Akun</span>
                    </div>
                </div>

                <!-- MENU BANTUAN CEPAT (LUPA PASSWORD & WHATSAPP ADMIN) -->
                <div class="grid grid-cols-2 gap-3">
                    <a 
                        href="{{ route('password.request') }}" 
                        class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-slate-400 bg-slate-50 hover:bg-slate-100/80 transition group text-center"
                    >
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center mb-1.5 group-hover:bg-slate-800 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-700 group-hover:text-slate-900">Lupa Password</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Reset via WA/Email</span>
                    </a>

                    @php
                        $cleanAdminWa = preg_replace('/[^0-9]/', '', $adminPhone ?? '081234567890');
                        if (str_starts_with($cleanAdminWa, '0')) {
                            $cleanAdminWa = '62' . substr($cleanAdminWa, 1);
                        }
                        $waDefaultMsg = urlencode("Halo Admin LKM Mitra Siswa Abadi, saya mengalami kendala masuk/lupa password akun saya. Mohon bantuan verifikasi.");
                    @endphp
                    <a 
                        href="https://wa.me/{{ $cleanAdminWa }}?text={{ $waDefaultMsg }}" 
                        target="_blank" 
                        rel="noopener noreferrer" 
                        class="flex flex-col items-center justify-center p-3 rounded-xl border border-emerald-200 hover:border-emerald-400 bg-emerald-50/50 hover:bg-emerald-50 transition group text-center"
                    >
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mb-1.5 group-hover:bg-emerald-600 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-emerald-800 group-hover:text-emerald-900">WhatsApp Admin</span>
                        <span class="text-[10px] text-emerald-600 mt-0.5">Bantuan Petugas</span>
                    </a>
                </div>

            </div>

            <!-- FOOTER INFO KARTU -->
            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Enkripsi Keamanan SSL/TLS
                </span>
                <span>v2.4 - Bank Mini</span>
            </div>
        </div>

        <!-- COPYRIGHT BAWAH KONSISTEN DENGAN SISTEM -->
        <p class="text-center text-slate-400 text-xs mt-6 font-medium">
            &copy; {{ date('Y') }} <strong>LKM Mitra Siswa Abadi</strong>. Jurusan Rekayasa Perangkat Lunak.
        </p>

    </div>

    <!-- SCRIPT INTERAKTIF -->
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
