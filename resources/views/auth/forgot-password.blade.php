<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - LKM Mitra Siswa Abadi</title>
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
<body class="bg-mesh min-h-screen flex flex-col justify-between p-4 sm:p-6 lg:p-8 antialiased text-slate-800">

    <!-- TOP BAR -->
    <header class="max-w-4xl mx-auto w-full flex items-center justify-between pb-6 border-b border-slate-800/80">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-slate-800 text-emerald-400 border border-slate-700 flex items-center justify-center shadow">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-base font-bold text-white leading-tight">LKM Mitra Siswa Abadi</h1>
                <p class="text-slate-400 text-xs">Pusat Layanan Pemulihan Akses Akun</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="inline-flex items-center space-x-1.5 text-xs sm:text-sm font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 px-3.5 py-2 rounded-xl transition border border-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali ke Login</span>
        </a>
    </header>

    <!-- KONTEN UTAMA -->
    <main class="max-w-2xl mx-auto w-full my-8">

        <!-- NOTIFIKASI ERROR / SUCCESS -->
        @if (session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 mb-6 rounded-2xl flex items-start space-x-3 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="text-xs sm:text-sm">
                    <p class="font-bold">Perhatian</p>
                    <p class="mt-0.5 text-rose-700 leading-relaxed">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 mb-6 rounded-2xl flex items-start space-x-3 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-xs sm:text-sm">
                    <p class="font-bold">Informasi Akun</p>
                    <p class="mt-0.5 text-amber-800 leading-relaxed">{{ session('warning') }}</p>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 mb-6 rounded-2xl flex items-start space-x-3 shadow-sm" role="alert">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-xs sm:text-sm">
                    <p class="font-bold text-emerald-900">Berhasil</p>
                    <p class="mt-0.5 text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-900 p-4 mb-6 rounded-2xl text-xs sm:text-sm">
                <p class="font-bold mb-1">Silakan periksa kesalahan berikut:</p>
                <ul class="list-disc list-inside text-rose-700 space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KARTU UTAMA DENGAN TAB PILIHAN METODE -->
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">

            <!-- HEADER KARTU -->
            <div class="p-6 sm:p-8 bg-slate-50 border-b border-slate-200">
                <div class="flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                    <span class="w-2 h-2 rounded-full bg-slate-700"></span>
                    <span>Layanan Pengaturan Ulang Kata Sandi</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Lupa Password Akun?</h2>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">
                    Karena akun hanya dibuatkan oleh Admin, Anda dapat memulihkan kata sandi secara mandiri menggunakan kode OTP via WhatsApp/Email, atau meminta bantuan langsung ke Admin.
                </p>

                <!-- TAB NAVIGASI -->
                @if (session('pwd_reset_step') !== 'verify')
                <div class="flex flex-wrap gap-2 mt-6 p-1.5 bg-slate-200/80 rounded-2xl text-xs font-bold">
                    <button 
                        type="button" 
                        onclick="switchTab('tab-otp')" 
                        id="btn-tab-otp" 
                        class="flex-1 py-2.5 px-3 rounded-xl bg-white text-slate-900 shadow-sm transition flex items-center justify-center space-x-1.5"
                    >
                        <span>⚡ Reset Mandiri (OTP)</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="switchTab('tab-wa')" 
                        id="btn-tab-wa" 
                        class="flex-1 py-2.5 px-3 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-1.5"
                    >
                        <span>💬 WhatsApp Admin</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="switchTab('tab-ticket')" 
                        id="btn-tab-ticket" 
                        class="flex-1 py-2.5 px-3 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-1.5"
                    >
                        <span>📝 Ajukan Tiket</span>
                    </button>
                </div>
                @endif
            </div>

            <div class="p-6 sm:p-8">

                <!-- ========================================================= -->
                <!-- KONDISI 1: JIKA SUDAH MEMINTA OTP (LANGKAH 2 VERIFIKASI) -->
                <!-- ========================================================= -->
                @if (session('pwd_reset_step') === 'verify')

                    <div class="space-y-6">

                        <!-- BADGE INFORMASI PROGRES -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                    Langkah 2: Verifikasi & Reset
                                </span>
                                <h3 class="text-lg font-bold text-slate-800 mt-2">Masukkan Kode OTP & Buat Kata Sandi Baru</h3>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Kode verifikasi telah dikirimkan ke: <strong class="text-slate-800">{{ session('pwd_reset_masked_contact') }}</strong> (Akun: {{ session('pwd_reset_username') }})
                                </p>
                            </div>
                        </div>

                        <!-- SIMULASI KODE OTP (MEMBANTU SAAT DI LOKAL / OFFLINE TANPA GATEWAY WA BERBAYAR) -->
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">
                                        OTP
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-emerald-950 uppercase tracking-wider">Simulasi Kode Keamanan</p>
                                        <p class="text-[11px] text-emerald-700">Gunakan kode 6 digit berikut untuk menyelesaikan verifikasi:</p>
                                    </div>
                                </div>
                                <div class="bg-white border-2 border-emerald-500 px-4 py-1.5 rounded-xl text-xl font-mono font-black text-emerald-800 tracking-widest shadow-sm select-all">
                                    {{ session('pwd_reset_otp') }}
                                </div>
                            </div>

                            @if(session('pwd_reset_metode') === 'whatsapp' && session('pwd_reset_wa_link'))
                                <div class="mt-3 pt-3 border-t border-emerald-200/60 flex items-center justify-between text-xs">
                                    <span class="text-emerald-800">Atau kirim ke WhatsApp nomor tersebut:</span>
                                    <a 
                                        href="{{ session('pwd_reset_wa_link') }}" 
                                        target="_blank" 
                                        rel="noopener noreferrer" 
                                        class="inline-flex items-center px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition space-x-1"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                                        <span>Buka WhatsApp</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- FORM INPUT OTP & NEW PASSWORD -->
                        <form action="{{ route('password.reset') }}" method="POST" class="space-y-5">
                            @csrf

                            <!-- KODE OTP -->
                            <div>
                                <label for="otp_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Masukkan 6 Digit Kode OTP
                                </label>
                                <input 
                                    type="text" 
                                    name="otp_code" 
                                    id="otp_code" 
                                    maxlength="6" 
                                    required 
                                    value="{{ session('pwd_reset_otp') }}"
                                    placeholder="Contoh: 123456" 
                                    class="w-full text-center tracking-[0.5em] text-2xl font-mono font-bold py-3 px-4 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition"
                                    autocomplete="off"
                                >
                            </div>

                            <!-- PASSWORD BARU -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Kata Sandi Baru
                                    </label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        id="new_password" 
                                        required 
                                        minlength="6" 
                                        placeholder="Min. 6 Karakter" 
                                        class="w-full py-3 px-3.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition"
                                    >
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                        Konfirmasi Sandi Baru
                                    </label>
                                    <input 
                                        type="password" 
                                        name="password_confirmation" 
                                        id="new_password_confirmation" 
                                        required 
                                        minlength="6" 
                                        placeholder="Ketik Ulang Sandi" 
                                        class="w-full py-3 px-3.5 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition"
                                    >
                                </div>
                            </div>

                            <!-- TOMBOL SIMPAN -->
                            <div class="flex items-center space-x-3 pt-2">
                                <button 
                                    type="submit" 
                                    class="flex-1 bg-slate-800 hover:bg-slate-900 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition duration-200 text-sm flex items-center justify-center space-x-2"
                                >
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Simpan & Perbarui Kata Sandi</span>
                                </button>
                                <a 
                                    href="{{ route('password.cancel') }}" 
                                    class="py-3.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-xs text-center"
                                >
                                    Batal
                                </a>
                            </div>
                        </form>

                    </div>

                @else

                    <!-- ========================================================= -->
                    <!-- TAB 1: RESET MANDIRI (REQUEST KODE OTP)                   -->
                    <!-- ========================================================= -->
                    <div id="tab-otp" class="tab-pane space-y-6">

                        <form action="{{ route('password.otp') }}" method="POST" class="space-y-5">
                            @csrf

                            <!-- INPUT USERNAME -->
                            <div>
                                <label for="username_req" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Username Akun Anda
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
                                        id="username_req" 
                                        value="{{ old('username', session('suggest_username')) }}" 
                                        required 
                                        placeholder="Contoh: teller1, spv1, admin, atau nama user" 
                                        class="w-full pl-11 pr-4 py-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800 transition"
                                        autofocus
                                    >
                                </div>
                            </div>

                            <!-- PILIHAN METODE SALURAN VERIFIKASI (WA ATAU EMAIL) -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                    Pilih Saluran Pengiriman Kode OTP
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                    <!-- OPTION WHATSAPP -->
                                    <label class="relative flex items-start p-4 rounded-2xl border-2 border-emerald-500 bg-emerald-50/40 cursor-pointer hover:bg-emerald-50 transition">
                                        <input type="radio" name="metode" value="whatsapp" checked class="mt-1 text-emerald-600 focus:ring-emerald-500">
                                        <div class="ml-3">
                                            <div class="flex items-center space-x-1.5">
                                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                                                <span class="text-xs font-bold text-slate-900">Nomor WhatsApp</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-1">Kirim OTP ke nomor WA terdaftar di profil akun.</p>
                                        </div>
                                    </label>

                                    <!-- OPTION EMAIL -->
                                    <label class="relative flex items-start p-4 rounded-2xl border-2 border-slate-200 bg-white cursor-pointer hover:bg-slate-50 transition">
                                        <input type="radio" name="metode" value="email" class="mt-1 text-slate-800 focus:ring-slate-800">
                                        <div class="ml-3">
                                            <div class="flex items-center space-x-1.5">
                                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <span class="text-xs font-bold text-slate-900">Alamat Email</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 mt-1">Kirim instruksi kode pemulihan ke email terdaftar.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- OPSIONAL JIKA AKUN BELUM ADA KONTAK -->
                            @if(session('suggest_ticket'))
                                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-900">
                                    <p class="font-bold">Akun belum memiliki No. HP terdaftar?</p>
                                    <p class="mt-0.5 text-amber-800">
                                        Masukkan nomor WhatsApp aktif Anda untuk verifikasi darurat:
                                    </p>
                                    <input 
                                        type="text" 
                                        name="manual_contact" 
                                        placeholder="Contoh: 081234567890" 
                                        class="mt-2 w-full py-2 px-3 bg-white border border-amber-300 rounded-lg text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500"
                                    >
                                </div>
                            @endif

                            <!-- TOMBOL MINTA OTP -->
                            <button 
                                type="submit" 
                                class="w-full bg-slate-800 hover:bg-slate-900 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition duration-200 text-sm flex items-center justify-center space-x-2"
                            >
                                <span>Kirim Kode Verifikasi OTP</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </form>

                    </div>

                    <!-- ========================================================= -->
                    <!-- TAB 2: HUBUNGI ADMIN VIA WHATSAPP LANGSUNG                -->
                    <!-- ========================================================= -->
                    <div id="tab-wa" class="tab-pane hidden space-y-6">

                        <div class="p-5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md">
                                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/>
                                </svg>
                            </div>
                            <div class="text-xs sm:text-sm text-emerald-900">
                                <h4 class="font-bold text-emerald-950 text-base">Layanan Cepat WhatsApp Administrator</h4>
                                <p class="mt-1 leading-relaxed text-emerald-800">
                                    Karena seluruh akun pegawai & nasabah didaftarkan secara resmi oleh Bank Mini Sekolah, Admin dapat mereset kata sandi Anda secara langsung ke kata sandi standar atau membuatkan sandi baru.
                                </p>
                            </div>
                        </div>

                        <!-- GENERATOR PESAN WHATSAPP OTOMATIS -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Nama Lengkap / Username Anda
                                </label>
                                <input 
                                    type="text" 
                                    id="wa_sender_name" 
                                    placeholder="Contoh: Ahmad Subagyo (teller1)" 
                                    class="w-full py-3 px-4 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-sm font-medium focus:outline-none focus:ring-2 focus:ring-slate-800 focus:border-slate-800"
                                >
                            </div>

                            @php
                                $cleanAdminPhone = preg_replace('/[^0-9]/', '', $adminPhone ?? '081234567890');
                                if (str_starts_with($cleanAdminPhone, '0')) {
                                    $cleanAdminPhone = '62' . substr($cleanAdminPhone, 1);
                                }
                            @endphp

                            <button 
                                type="button" 
                                onclick="openWhatsAppAdmin('{{ $cleanAdminPhone }}')" 
                                class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition duration-200 text-sm flex items-center justify-center space-x-2"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.072.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                                <span>Buka Chat WhatsApp Resmi Admin</span>
                            </button>
                        </div>

                    </div>

                    <!-- ========================================================= -->
                    <!-- TAB 3: AJUKAN TIKET PERMOHONAN RESET                      -->
                    <!-- ========================================================= -->
                    <div id="tab-ticket" class="tab-pane hidden space-y-6">

                        @if(session('ticket_success'))
                            <div class="p-5 bg-emerald-50 border-2 border-emerald-500 rounded-2xl text-emerald-900 space-y-3">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <h4 class="font-bold text-base">Tiket Berhasil Diajukan!</h4>
                                </div>
                                <p class="text-xs text-emerald-800 leading-relaxed">
                                    Permohonan Anda tercatat dengan Nomor Tiket: <strong class="font-mono text-emerald-950 bg-white px-2 py-0.5 rounded border border-emerald-300">#{{ session('ticket_id') }}</strong>. Administrator akan meninjau dan mereset kata sandi Anda.
                                </p>
                                @if(session('wa_admin_link'))
                                    <a 
                                        href="{{ session('wa_admin_link') }}" 
                                        target="_blank" 
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition space-x-1.5 shadow"
                                    >
                                        <span>Konfirmasi ke Admin via WhatsApp</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </a>
                                @endif
                            </div>
                        @else

                            <form action="{{ route('password.ticket') }}" method="POST" class="space-y-4">
                                @csrf

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="ticket_username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                            Username Akun
                                        </label>
                                        <input 
                                            type="text" 
                                            name="username" 
                                            id="ticket_username" 
                                            value="{{ old('username', session('suggest_username')) }}" 
                                            required 
                                            placeholder="Contoh: teller1" 
                                            class="w-full py-2.5 px-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-slate-800"
                                        >
                                    </div>
                                    <div>
                                        <label for="ticket_nama" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                            Nama Lengkap Pemohon
                                        </label>
                                        <input 
                                            type="text" 
                                            name="nama_pemohon" 
                                            id="ticket_nama" 
                                            value="{{ old('nama_pemohon', session('suggest_nama')) }}" 
                                            required 
                                            placeholder="Nama sesuai KTP/Daftar Siswa" 
                                            class="w-full py-2.5 px-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-slate-800"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label for="ticket_no_wa" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Nomor WhatsApp Baru / Aktif
                                    </label>
                                    <input 
                                        type="text" 
                                        name="no_wa" 
                                        id="ticket_no_wa" 
                                        value="{{ old('no_wa') }}" 
                                        required 
                                        placeholder="Contoh: 081234567890 (Untuk dihubungi Admin)" 
                                        class="w-full py-2.5 px-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-slate-800"
                                    >
                                </div>

                                <div>
                                    <label for="ticket_alasan" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                        Keterangan / Alasan Permohonan Reset
                                    </label>
                                    <textarea 
                                        name="alasan" 
                                        id="ticket_alasan" 
                                        rows="3" 
                                        required 
                                        placeholder="Contoh: Lupa password akun dan belum mendaftarkan nomor HP di profil sistem..." 
                                        class="w-full py-2.5 px-3 bg-slate-50 focus:bg-white border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-slate-800"
                                    >{{ old('alasan') }}</textarea>
                                </div>

                                <button 
                                    type="submit" 
                                    class="w-full bg-slate-800 hover:bg-slate-900 active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition duration-200 text-sm flex items-center justify-center space-x-2"
                                >
                                    <span>Kirim Tiket ke Administrator</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                </button>
                            </form>
                        @endif

                    </div>

                @endif

            </div>

            <!-- FOOTER KARTU -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Autentikasi Terenkripsi SHA-256 / Bcrypt
                </span>
                <a href="{{ route('login') }}" class="font-bold text-slate-700 hover:text-slate-900 underline">
                    Sudah ingat kata sandi? Masuk
                </a>
            </div>

        </div>

    </main>

    <!-- FOOTER COPYRIGHT -->
    <footer class="max-w-4xl mx-auto w-full pt-6 border-t border-slate-800/80 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} <strong>LKM Mitra Siswa Abadi</strong>. Sistem Bank Mini Sekolah. Seluruh hak cipta dilindungi.
    </footer>

    <!-- SCRIPT TAB DAN HELPER -->
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
            const target = document.getElementById(tabId);
            if (target) {
                target.classList.remove('hidden');
            }

            // Update styling tombol tab
            ['otp', 'wa', 'ticket'].forEach(key => {
                const btn = document.getElementById('btn-tab-' + key);
                if (btn) {
                    if ('tab-' + key === tabId) {
                        btn.className = "flex-1 py-2.5 px-3 rounded-xl bg-white text-slate-900 shadow-sm transition flex items-center justify-center space-x-1.5 font-bold";
                    } else {
                        btn.className = "flex-1 py-2.5 px-3 rounded-xl text-slate-600 hover:text-slate-900 transition flex items-center justify-center space-x-1.5 font-bold";
                    }
                }
            });
        }

        function openWhatsAppAdmin(adminPhone) {
            const sender = document.getElementById('wa_sender_name').value.trim() || 'Pegawai / Pengguna';
            const text = encodeURIComponent(`Halo Admin LKM Mitra Siswa Abadi, saya ${sender} memohon bantuan pembaruan / reset kata sandi akun saya karena lupa password. Mohon bantuannya. Terima kasih.`);
            window.open(`https://wa.me/${adminPhone}?text=${text}`, '_blank');
        }
    </script>

</body>
</html>
