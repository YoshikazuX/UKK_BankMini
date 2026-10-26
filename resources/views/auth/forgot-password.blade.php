<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - LKM Mitra Siswa Abadi</title>
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
        <!-- PANEL KIRI: FORM LUPA KATA SANDI (MULTI-STEP FLOW)              -->
        <!-- ============================================================== -->
        <div class="w-full lg:w-1/2 h-full flex flex-col justify-center items-center p-6 sm:p-10 lg:p-12 xl:p-16 bg-white lg:overflow-y-auto">

            <div class="max-w-md w-full mx-auto my-auto py-6">

                <!-- NOTIFIKASI ERROR / VALIDASI GLOBAL (JS DRIVEN & SESSION) -->
                @if (session('error'))
                    <div class="bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 rounded-lg text-sm mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div id="alert-box" class="hidden bg-rose-50 border border-rose-200 text-rose-700 px-3.5 py-2.5 rounded-lg text-sm mb-4">
                    <span id="alert-message"></span>
                </div>

                <div id="success-box" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 px-3.5 py-2.5 rounded-lg text-sm mb-4">
                    <span id="success-message"></span>
                </div>

                <!-- ========================================================== -->
                <!-- TAHAP 1: INPUT ALAMAT EMAIL TERDAFTAR                      -->
                <!-- ========================================================== -->
                <div id="step-1" class="step-container">
                    <!-- TAUTAN ATAS: KEMBALI KE HALAMAN MASUK -->
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 mb-6 inline-flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Kembali ke halaman masuk</span>
                    </a>

                    <!-- JUDUL & SUBJUDUL -->
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Lupa Kata Sandi?</h1>
                    <p class="text-sm text-slate-500 mt-1 mb-6">
                        Masukkan alamat email yang terdaftar pada akun Anda untuk menerima kode verifikasi OTP.
                    </p>

                    <!-- FORM INPUT EMAIL -->
                    <form id="form-step-1" onsubmit="submitStep1(event)" class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Alamat Email
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                placeholder="Masukkan alamat email terdaftar" 
                                required 
                                autofocus
                                autocomplete="email"
                                class="w-full px-3.5 py-2.5 bg-white border border-slate-300 focus:border-slate-700 focus:ring-2 focus:ring-slate-900/10 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none transition"
                            >
                        </div>

                        <!-- TOMBOL KIRIM KODE OTP -->
                        <button 
                            type="submit" 
                            id="btn-step-1"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 mt-2"
                        >
                            Kirim Kode OTP
                        </button>
                    </form>

                    <!-- FOOTER BAWAH TOMBOL -->
                    @php
                        $cleanAdminWa = preg_replace('/[^0-9]/', '', $adminPhone ?? '081234567890');
                        if (str_starts_with($cleanAdminWa, '0')) {
                            $cleanAdminWa = '62' . substr($cleanAdminWa, 1);
                        }
                        $waDefaultMsg = urlencode("Halo Admin LKM Mitra Siswa Abadi, saya mengalami kendala email pemulihan akun saya. Mohon bantuan verifikasi.");
                    @endphp
                    <p class="text-xs text-slate-500 text-center mt-6">
                        Terkendala dengan email Anda? <a href="https://wa.me/{{ $cleanAdminWa }}?text={{ $waDefaultMsg }}" target="_blank" rel="noopener noreferrer" class="text-slate-800 font-semibold hover:underline">Hubungi Administrator</a>.
                    </p>
                </div>

                <!-- ========================================================== -->
                <!-- TAHAP 2: VERIFIKASI KODE OTP 6 DIGIT                       -->
                <!-- ========================================================== -->
                <div id="step-2" class="step-container hidden">
                    <!-- TAUTAN ATAS: GANTI ALAMAT EMAIL -->
                    <button type="button" onclick="goToStep(1)" class="text-sm font-medium text-slate-600 hover:text-slate-900 mb-6 inline-flex items-center gap-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Ganti alamat email</span>
                    </button>

                    <!-- JUDUL & SUBJUDUL -->
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Verifikasi Kode OTP</h1>
                    <p class="text-sm text-slate-500 mt-1 mb-6">
                        Masukkan 6 digit kode yang kami kirimkan ke <span id="display-email" class="font-semibold text-slate-800 break-all"></span>.
                    </p>

                    <!-- FORM INPUT OTP -->
                    <form id="form-step-2" onsubmit="submitStep2(event)" class="space-y-4">
                        <div>
                            <div class="grid grid-cols-6 gap-2.5" id="otp-container">
                                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box h-12 text-center text-lg font-semibold rounded-lg border border-slate-300 focus:border-slate-800 focus:ring-2 focus:ring-slate-900/10 focus:outline-none transition text-slate-900 bg-white" data-index="0" autocomplete="one-time-code">
                                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box h-12 text-center text-lg font-semibold rounded-lg border border-slate-300 focus:border-slate-800 focus:ring-2 focus:ring-slate-900/10 focus:outline-none transition text-slate-900 bg-white" data-index="1">
                                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box h-12 text-center text-lg font-semibold rounded-lg border border-slate-300 focus:border-slate-800 focus:ring-2 focus:ring-slate-900/10 focus:outline-none transition text-slate-900 bg-white" data-index="2">
                                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box h-12 text-center text-lg font-semibold rounded-lg border border-slate-300 focus:border-slate-800 focus:ring-2 focus:ring-slate-900/10 focus:outline-none transition text-slate-900 bg-white" data-index="3">
                                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box h-12 text-center text-lg font-semibold rounded-lg border border-slate-300 focus:border-slate-800 focus:ring-2 focus:ring-slate-900/10 focus:outline-none transition text-slate-900 bg-white" data-index="4">
                                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*" class="otp-box h-12 text-center text-lg font-semibold rounded-lg border border-slate-300 focus:border-slate-800 focus:ring-2 focus:ring-slate-900/10 focus:outline-none transition text-slate-900 bg-white" data-index="5">
                            </div>
                        </div>

                        <!-- TOMBOL VERIFIKASI KODE -->
                        <button 
                            type="submit" 
                            id="btn-step-2"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 mt-2"
                        >
                            Verifikasi Kode
                        </button>
                    </form>

                    <!-- BAWAH TOMBOL: TIMER & KIRIM ULANG -->
                    <div class="text-center mt-6">
                        <p class="text-xs text-slate-500">
                            Belum menerima kode? 
                            <span id="timer-wrapper">
                                Kirim ulang (<span id="timer-display" class="font-medium text-slate-700">00:59</span>)
                            </span>
                            <button type="button" id="btn-resend" onclick="handleResendOtp()" class="hidden text-slate-900 font-semibold hover:underline focus:outline-none">
                                Kirim ulang sekarang
                            </button>
                        </p>
                    </div>
                </div>

                <!-- ========================================================== -->
                <!-- TAHAP 3: BUAT KATA SANDI BARU                              -->
                <!-- ========================================================== -->
                <div id="step-3" class="step-container hidden">
                    <!-- JUDUL & SUBJUDUL -->
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Buat Kata Sandi Baru</h1>
                    <p class="text-sm text-slate-500 mt-1 mb-6">
                        Silakan masukkan kata sandi baru untuk akun Anda.
                    </p>

                    <!-- FORM RESET PASSWORD -->
                    <form id="form-step-3" onsubmit="submitStep3(event)" class="space-y-4">
                        <!-- INPUT KATA SANDI BARU -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    placeholder="Masukkan kata sandi baru" 
                                    required 
                                    minlength="8"
                                    autocomplete="new-password"
                                    class="w-full px-3.5 py-2.5 pr-10 bg-white border border-slate-300 focus:border-slate-700 focus:ring-2 focus:ring-slate-900/10 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none transition"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePasswordVisibility('new_password', 'eye-1', 'eye-slash-1')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                    title="Tampilkan / Sembunyikan Kata Sandi"
                                >
                                    <svg id="eye-1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg id="eye-slash-1" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Minimal 8 karakter.</p>
                        </div>

                        <!-- INPUT KONFIRMASI KATA SANDI BARU -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Konfirmasi Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    placeholder="Ulangi kata sandi baru" 
                                    required 
                                    minlength="8"
                                    autocomplete="new-password"
                                    class="w-full px-3.5 py-2.5 pr-10 bg-white border border-slate-300 focus:border-slate-700 focus:ring-2 focus:ring-slate-900/10 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none transition"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePasswordVisibility('confirm_password', 'eye-2', 'eye-slash-2')" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                    title="Tampilkan / Sembunyikan Kata Sandi"
                                >
                                    <svg id="eye-2" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <svg id="eye-slash-2" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- TOMBOL SIMPAN KATA SANDI -->
                        <button 
                            type="submit" 
                            id="btn-step-3"
                            class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg text-sm transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 mt-2"
                        >
                            Simpan Kata Sandi
                        </button>
                    </form>
                </div>

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

    <!-- ============================================================== -->
    <!-- LOGIKA JAVASCRIPT: MULTI-STEP STATE, OTP INPUT, & TIMER        -->
    <!-- ============================================================== -->
    <script>
        // State management
        const state = {
            currentStep: 1,
            email: '',
            timer: null,
            timeLeft: 59
        };

        // DOM elements
        const step1 = document.getElementById('step-1');
        const step2 = document.getElementById('step-2');
        const step3 = document.getElementById('step-3');
        const alertBox = document.getElementById('alert-box');
        const alertMessage = document.getElementById('alert-message');
        const successBox = document.getElementById('success-box');
        const successMessage = document.getElementById('success-message');
        const displayEmail = document.getElementById('display-email');
        const otpBoxes = document.querySelectorAll('.otp-box');
        const timerWrapper = document.getElementById('timer-wrapper');
        const timerDisplay = document.getElementById('timer-display');
        const btnResend = document.getElementById('btn-resend');

        function showAlert(msg) {
            hideSuccess();
            alertMessage.textContent = msg;
            alertBox.classList.remove('hidden');
        }

        function hideAlert() {
            alertBox.classList.add('hidden');
            alertMessage.textContent = '';
        }

        function showSuccess(msg) {
            hideAlert();
            successMessage.textContent = msg;
            successBox.classList.remove('hidden');
        }

        function hideSuccess() {
            successBox.classList.add('hidden');
            successMessage.textContent = '';
        }

        function goToStep(step) {
            hideAlert();
            hideSuccess();
            state.currentStep = step;

            step1.classList.toggle('hidden', step !== 1);
            step2.classList.toggle('hidden', step !== 2);
            step3.classList.toggle('hidden', step !== 3);

            if (step === 1) {
                const emailInput = document.getElementById('email');
                if (emailInput) {
                    emailInput.focus();
                }
            } else if (step === 2) {
                displayEmail.textContent = state.email || 'email Anda';
                clearOtpBoxes();
                startCountdownTimer();
                setTimeout(() => {
                    if (otpBoxes[0]) otpBoxes[0].focus();
                }, 100);
            } else if (step === 3) {
                const newPw = document.getElementById('new_password');
                if (newPw) {
                    newPw.focus();
                }
            }
        }

        // TAHAP 1: Submit email
        function submitStep1(event) {
            event.preventDefault();
            hideAlert();

            const emailInput = document.getElementById('email');
            const emailVal = emailInput.value.trim();

            if (!emailVal || !emailVal.includes('@')) {
                showAlert('Silakan masukkan alamat email yang valid.');
                return;
            }

            state.email = emailVal;
            goToStep(2);
        }

        // OTP Boxes input & navigation logic
        otpBoxes.forEach((box, index) => {
            box.addEventListener('input', (e) => {
                // Hanya terima karakter angka
                e.target.value = e.target.value.replace(/[^0-9]/g, '');

                if (e.target.value.length === 1) {
                    if (index < otpBoxes.length - 1) {
                        otpBoxes[index + 1].focus();
                    }
                }
            });

            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (!e.target.value && index > 0) {
                        otpBoxes[index - 1].focus();
                    }
                } else if (e.key === 'ArrowLeft' && index > 0) {
                    otpBoxes[index - 1].focus();
                } else if (e.key === 'ArrowRight' && index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
            });

            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                if (!pasteData) return;

                pasteData.split('').forEach((char, i) => {
                    if (otpBoxes[i]) {
                        otpBoxes[i].value = char;
                    }
                });

                const nextFocus = Math.min(pasteData.length, otpBoxes.length - 1);
                otpBoxes[nextFocus].focus();
            });
        });

        function clearOtpBoxes() {
            otpBoxes.forEach(box => box.value = '');
        }

        function getOtpValue() {
            let code = '';
            otpBoxes.forEach(box => code += box.value.trim());
            return code;
        }

        // TAHAP 2: Submit OTP
        function submitStep2(event) {
            event.preventDefault();
            hideAlert();

            const otp = getOtpValue();
            if (otp.length < 6) {
                showAlert('Silakan lengkapi 6 digit kode OTP verifikasi.');
                return;
            }

            // Transisi ke Tahap 3
            goToStep(3);
        }

        // Countdown timer logic
        function startCountdownTimer() {
            if (state.timer) {
                clearInterval(state.timer);
            }

            state.timeLeft = 59;
            btnResend.classList.add('hidden');
            timerWrapper.classList.remove('hidden');
            updateTimerDisplay();

            state.timer = setInterval(() => {
                state.timeLeft--;
                updateTimerDisplay();

                if (state.timeLeft <= 0) {
                    clearInterval(state.timer);
                    state.timer = null;
                    timerWrapper.classList.add('hidden');
                    btnResend.classList.remove('hidden');
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const formatted = '00:' + String(state.timeLeft).padStart(2, '0');
            timerDisplay.textContent = formatted;
        }

        function handleResendOtp() {
            clearOtpBoxes();
            startCountdownTimer();
            showSuccess('Kode OTP baru telah dikirimkan ke ' + state.email);
            if (otpBoxes[0]) otpBoxes[0].focus();
        }

        // TAHAP 3: Submit New Password
        function submitStep3(event) {
            event.preventDefault();
            hideAlert();

            const newPw = document.getElementById('new_password').value;
            const confirmPw = document.getElementById('confirm_password').value;

            if (newPw.length < 8) {
                showAlert('Kata sandi baru minimal harus 8 karakter.');
                return;
            }

            if (newPw !== confirmPw) {
                showAlert('Konfirmasi kata sandi tidak cocok. Silakan periksa kembali.');
                return;
            }

            // Tampilkan status proses
            const btn = document.getElementById('btn-step-3');
            btn.disabled = true;
            btn.innerHTML = 'Menyimpan Kata Sandi...';
            btn.classList.add('opacity-80', 'cursor-not-allowed');

            // Redirect ke halaman login dengan notifikasi sukses
            setTimeout(() => {
                window.location.href = "{{ route('login') }}?reset=success";
            }, 500);
        }

        // Toggle visibility password
        function togglePasswordVisibility(inputId, eyeId, slashId) {
            const input = document.getElementById(inputId);
            const eye = document.getElementById(eyeId);
            const slash = document.getElementById(slashId);

            if (!input || !eye || !slash) return;

            if (input.type === 'password') {
                input.type = 'text';
                eye.classList.add('hidden');
                slash.classList.remove('hidden');
            } else {
                input.type = 'password';
                eye.classList.remove('hidden');
                slash.classList.add('hidden');
            }
        }
    </script>

</body>
</html>
