<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Favicon / Logo Web -->
    <link rel="icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Nasabah - E-Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" type="text/javascript"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
        #reader video,
        #reader canvas {
            width: 100% !important;
            height: 100% !important;
            border-radius: 1rem;
            object-fit: cover;
            transform: scaleX(-1) !important; /* Default: Dibalik horisontal agar webcam tidak mirror */
            -webkit-transform: scaleX(-1) !important;
        }
        @keyframes scanLaser {
            0% { top: 12%; opacity: 0.2; }
            50% { opacity: 0.95; }
            100% { top: 88%; opacity: 0.2; }
        }
        .laser-line {
            position: absolute;
            left: 8%;
            right: 8%;
            height: 3px;
            background: linear-gradient(90deg, rgba(16,185,129,0) 0%, rgba(16,185,129,1) 50%, rgba(16,185,129,0) 100%);
            box-shadow: 0 0 14px rgba(16, 185, 129, 0.9);
            animation: scanLaser 2.2s infinite ease-in-out;
            pointer-events: none;
            z-index: 20;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <x-navbar />

    <main class="max-w-xl mx-auto py-8 px-4">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            {{-- Header Card --}}
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Scan QR Code Nasabah
                    </h1>
                    <p class="text-slate-500 text-xs mt-1">Arahkan kamera ke QR Code kartu pelajar atau aplikasi mobile nasabah.</p>
                </div>
                <a href="{{ url('/teller/dashboard') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1">
                    &larr; Dashboard
                </a>
            </div>

            <div class="p-6">

                {{-- Status Alert Indicator --}}
                <div id="status-card" class="mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200 flex items-center gap-2.5 text-xs font-medium text-blue-800 transition">
                    <svg id="status-icon-loading" class="w-4 h-4 animate-spin text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <svg id="status-icon-success" class="w-4 h-4 text-emerald-600 hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <svg id="status-icon-error" class="w-4 h-4 text-rose-600 hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span id="status-text">Meminta izin dan mengaktifkan kamera...</span>
                </div>

                {{-- Insecure HTTP Warning --}}
                <div id="insecure-warning" class="hidden mb-4 p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs">
                    <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-900">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
                        </svg>
                        Catatan Keamanan Browser (HTTP)
                    </div>
                    Browser membatasi webcam pada alamat IP jaringan tanpa SSL. Bila kamera gagal menyala, buka website via <strong>http://localhost:8000</strong> pada komputer kasir/server, atau gunakan <strong>Input Manual NIS</strong> di bawah.
                </div>

                {{-- Kotak Kamera Scanner --}}
                <div class="relative bg-slate-950 rounded-2xl overflow-hidden shadow-inner border border-slate-800 min-h-[300px] flex items-center justify-center">
                    
                    {{-- Reader target element --}}
                    <div id="reader" class="w-full"></div>

                    {{-- Tombol Cepat Balik Orientasi Kamera --}}
                    <button type="button" onclick="toggleFlip()" class="absolute top-3 right-3 z-30 bg-slate-900/80 hover:bg-slate-900 text-white px-2.5 py-1.5 rounded-xl text-xs font-semibold backdrop-blur-sm border border-slate-700/80 flex items-center gap-1.5 shadow transition cursor-pointer" title="Klik jika tampilan kamera ingin dibalikkan">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span id="btn-flip-text">Balik Kamera</span>
                    </button>

                    {{-- Laser scanning overlay --}}
                    <div id="laser-overlay" class="hidden absolute inset-0 pointer-events-none z-10">
                        <div class="laser-line"></div>
                        <div class="absolute inset-8 sm:inset-10 border-2 border-emerald-400/70 rounded-2xl pointer-events-none shadow-[0_0_15px_rgba(16,185,129,0.3)]"></div>
                        <div class="absolute bottom-3 inset-x-0 text-center">
                            <span class="inline-block px-3 py-1 rounded-full bg-slate-950/70 text-emerald-300 text-[11px] font-medium backdrop-blur-sm">
                                Posisikan QR di dalam kotak
                            </span>
                        </div>
                    </div>

                    {{-- Placeholder bila kamera mati / izin ditolak --}}
                    <div id="camera-placeholder" class="hidden absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-slate-900/95 text-white z-20">
                        <div class="w-14 h-14 rounded-2xl bg-slate-800 flex items-center justify-center mb-3 text-slate-400 border border-slate-700">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-100 mb-1">Kamera Belum Aktif</h4>
                        <p id="placeholder-text" class="text-xs text-slate-400 max-w-xs mb-4">Pastikan browser diberi izin untuk mengakses kamera perangkat.</p>
                        <div class="flex flex-wrap items-center justify-center gap-2">
                            <button type="button" onclick="startCameraAuto()" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl transition shadow flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                <span>Mulai Kamera</span>
                            </button>
                            <button type="button" onclick="document.getElementById('qr-file-input').click()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-medium text-xs rounded-xl border border-slate-700 transition cursor-pointer">
                                Upload File QR
                            </button>
                        </div>
                    </div>

                </div>

                {{-- Toolbar Controls: Dropdown Kamera & Upload Gambar --}}
                <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <div class="flex items-center gap-2 flex-1">
                        <label for="camera-select" class="text-xs font-semibold text-slate-600 shrink-0">Kamera:</label>
                        <select id="camera-select" onchange="switchCamera(this.value)" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <option value="">Deteksi otomatis...</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        {{-- Tombol Refresh / Coba Ulang --}}
                        <button type="button" onclick="startCameraAuto()" title="Segarkan kamera" class="p-2 text-slate-700 hover:text-slate-950 bg-slate-100 hover:bg-slate-200 rounded-lg transition text-xs flex items-center gap-1 font-semibold cursor-pointer border border-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Ulang</span>
                        </button>

                        {{-- Scan File Gambar QR --}}
                        <input type="file" id="qr-file-input" accept="image/*" class="hidden" onchange="scanImageFile(this)">
                        <button type="button" onclick="document.getElementById('qr-file-input').click()" class="px-3 py-2 text-slate-700 hover:text-slate-950 bg-slate-100 hover:bg-slate-200 rounded-lg transition text-xs font-semibold flex items-center gap-1.5 cursor-pointer border border-slate-200">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span>Upload QR</span>
                        </button>
                    </div>
                </div>

                {{-- Input Manual NIS / Nomor Rekening --}}
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <form onsubmit="handleManualSearch(event)" class="space-y-2">
                        <label for="input-nis-manual" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Atau Input NIS / Nomor Rekening Manual
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </span>
                                <input type="text" id="input-nis-manual" placeholder="Ketik NIS atau No. Rekening..." class="w-full pl-9 pr-3 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                            </div>
                            <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm rounded-xl transition shadow-sm shrink-0 cursor-pointer">
                                Buka Transaksi
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </main>

    <script>
        let html5QrCode = null;
        let isScanning = false;
        let isRedirecting = false;

        // Default: true (Dibalik secara horisontal agar webcam tidak mirror)
        let isFlipped = localStorage.getItem('teller_camera_flipped');
        if (isFlipped === null) {
            isFlipped = true; // Default selalu dibalik agar non-mirror
        } else {
            isFlipped = (isFlipped === 'true');
        }

        function applyFlip() {
            const videoEls = document.querySelectorAll('#reader video, #reader canvas');
            videoEls.forEach(v => {
                const val = isFlipped ? 'scaleX(-1)' : 'none';
                v.style.setProperty('transform', val, 'important');
                v.style.setProperty('-webkit-transform', val, 'important');
            });
            const text = document.getElementById('btn-flip-text');
            if (text) {
                text.textContent = isFlipped ? 'Tampilan: Dibalik' : 'Tampilan: Asli';
            }
        }

        function toggleFlip() {
            isFlipped = !isFlipped;
            localStorage.setItem('teller_camera_flipped', isFlipped);
            applyFlip();
        }

        // Pantau elemen video dari html5-qrcode agar selalu menerapkan orientasi yang dipilih
        const videoObserver = new MutationObserver(() => {
            applyFlip();
        });
        const readerBox = document.getElementById('reader');
        if (readerBox) {
            videoObserver.observe(readerBox, { childList: true, subtree: true });
        }

        // Bunyi Beep Konfirmasi saat QR Berhasil Terdeteksi
        function playScanBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = "sine";
                osc.frequency.value = 920;
                gain.gain.setValueAtTime(0.18, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.16);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.16);
            } catch (e) {}
        }

        function updateStatus(state, message) {
            const card = document.getElementById('status-card');
            const text = document.getElementById('status-text');
            const iconLoading = document.getElementById('status-icon-loading');
            const iconSuccess = document.getElementById('status-icon-success');
            const iconError = document.getElementById('status-icon-error');
            const laser = document.getElementById('laser-overlay');
            const placeholder = document.getElementById('camera-placeholder');

            text.innerHTML = message;
            iconLoading.classList.add('hidden');
            iconSuccess.classList.add('hidden');
            iconError.classList.add('hidden');

            if (state === 'loading') {
                iconLoading.classList.remove('hidden');
                card.className = "mb-4 p-3 rounded-xl bg-blue-50 border border-blue-200 flex items-center gap-2.5 text-xs font-medium text-blue-800 transition";
                laser.classList.add('hidden');
                placeholder.classList.add('hidden');
            } else if (state === 'active') {
                iconSuccess.classList.remove('hidden');
                card.className = "mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center gap-2.5 text-xs font-medium text-emerald-800 transition";
                laser.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else if (state === 'success') {
                iconSuccess.classList.remove('hidden');
                card.className = "mb-4 p-3 rounded-xl bg-emerald-100 border border-emerald-300 flex items-center gap-2.5 text-xs font-bold text-emerald-900 transition";
                laser.classList.add('hidden');
            } else if (state === 'error') {
                iconError.classList.remove('hidden');
                card.className = "mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 flex items-center gap-2.5 text-xs font-medium text-rose-800 transition";
                laser.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }
        }

        function onScanSuccess(decodedText) {
            if (isRedirecting) return;
            isRedirecting = true;

            playScanBeep();
            updateStatus('success', `Berhasil mendeteksi: <strong>${decodedText}</strong>. Membuka transaksi...`);

            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    window.location.href = "{{ url('/teller/cari-nis') }}/" + encodeURIComponent(decodedText);
                }).catch(() => {
                    window.location.href = "{{ url('/teller/cari-nis') }}/" + encodeURIComponent(decodedText);
                });
            } else {
                window.location.href = "{{ url('/teller/cari-nis') }}/" + encodeURIComponent(decodedText);
            }
        }

        function onScanFailure(error) {
            // Abaikan per-frame
        }

        async function stopCameraIfRunning() {
            if (html5QrCode && isScanning) {
                try {
                    await html5QrCode.stop();
                } catch (e) {
                    console.warn("Gagal menghentikan scanner:", e);
                }
                isScanning = false;
            }
        }

        async function startCameraAuto() {
            // Cek apakah bukan localhost dan bukan https
            if (window.isSecureContext === false && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                document.getElementById('insecure-warning').classList.remove('hidden');
            }

            await stopCameraIfRunning();

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            updateStatus('loading', 'Mengaktifkan kamera & meminta izin browser...');

            const scanConfig = {
                fps: 15,
                qrbox: { width: 220, height: 220 },
                aspectRatio: 1.0
            };

            // Strategi 1: Coba kamera belakang (environment)
            html5QrCode.start({ facingMode: "environment" }, scanConfig, onScanSuccess, onScanFailure)
                .then(() => {
                    isScanning = true;
                    updateStatus('active', 'Kamera aktif! Arahkan ke QR Code nasabah.');
                    populateCameraList();
                })
                .catch(err1 => {
                    console.warn("Kamera environment tidak ada/gagal, mencoba kamera user/webcam...", err1);
                    // Strategi 2: Coba kamera user / webcam laptop
                    html5QrCode.start({ facingMode: "user" }, scanConfig, onScanSuccess, onScanFailure)
                        .then(() => {
                            isScanning = true;
                            updateStatus('active', 'Kamera aktif (Webcam)! Arahkan ke QR Code nasabah.');
                            populateCameraList();
                        })
                        .catch(err2 => {
                            // Strategi 3: Ambil daftar hardware kamera yang tersedia
                            Html5Qrcode.getCameras().then(devices => {
                                if (devices && devices.length > 0) {
                                    html5QrCode.start(devices[0].id, scanConfig, onScanSuccess, onScanFailure)
                                        .then(() => {
                                            isScanning = true;
                                            updateStatus('active', 'Kamera aktif! Arahkan ke QR Code nasabah.');
                                            populateCameraList();
                                        })
                                        .catch(err3 => handleCameraError(err3));
                                } else {
                                    handleCameraError(err2);
                                }
                            }).catch(() => handleCameraError(err2));
                        });
                });
        }

        function handleCameraError(err) {
            console.error("Camera error details:", err);
            let msg = "Gagal mengakses kamera.";
            let placeholderMsg = "Kamera tidak dapat diakses.";

            const errStr = (err && err.message ? err.message : String(err)).toLowerCase();

            if (errStr.includes("notallowederror") || errStr.includes("permission")) {
                msg = "Izin kamera diblokir browser. Klik ikon gembok di address bar untuk mengizinkan kamera.";
                placeholderMsg = "Izin akses kamera diblokir oleh browser. Izinkan kamera di sebelah kiri bilah URL, lalu klik Mulai Kamera.";
            } else if (errStr.includes("notfounderror") || errStr.includes("device")) {
                msg = "Perangkat kamera / webcam tidak terdeteksi pada komputer.";
                placeholderMsg = "Tidak ada webcam yang terhubung. Gunakan opsi Upload File QR atau Input Manual NIS di bawah.";
            } else if (errStr.includes("insecure") || (!window.isSecureContext && location.hostname !== 'localhost')) {
                msg = "Browser membatasi webcam via HTTP. Akses melalui http://localhost:8000 atau input manual.";
                placeholderMsg = "Kamera membutuhkan HTTPS bila diakses antar-komputer LAN. Akses via localhost atau gunakan input manual.";
            }

            document.getElementById('placeholder-text').innerText = placeholderMsg;
            updateStatus('error', msg);
        }

        function populateCameraList() {
            Html5Qrcode.getCameras().then(cameras => {
                const select = document.getElementById('camera-select');
                select.innerHTML = '';
                if (cameras && cameras.length > 0) {
                    cameras.forEach((cam, idx) => {
                        const opt = document.createElement('option');
                        opt.value = cam.id;
                        opt.text = cam.label || `Kamera ${idx + 1}`;
                        select.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = "";
                    opt.text = "Kamera aktif";
                    select.appendChild(opt);
                }
            }).catch(e => console.warn(e));
        }

        async function switchCamera(cameraId) {
            if (!cameraId) return;
            await stopCameraIfRunning();
            updateStatus('loading', 'Mengalihkan kamera...');

            const scanConfig = {
                fps: 15,
                qrbox: { width: 220, height: 220 },
                aspectRatio: 1.0
            };

            html5QrCode.start(cameraId, scanConfig, onScanSuccess, onScanFailure)
                .then(() => {
                    isScanning = true;
                    updateStatus('active', 'Kamera aktif! Arahkan ke QR Code nasabah.');
                })
                .catch(err => handleCameraError(err));
        }

        function scanImageFile(input) {
            if (!input.files || input.files.length === 0) return;
            const file = input.files[0];
            
            updateStatus('loading', 'Memindai gambar QR...');

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            html5QrCode.scanFile(file, true)
                .then(decodedText => {
                    onScanSuccess(decodedText);
                })
                .catch(err => {
                    alert("QR Code tidak dapat dideteksi pada gambar ini. Pastikan gambar tidak buram.");
                    updateStatus('active', 'Kamera aktif! Arahkan ke QR Code nasabah.');
                });
        }

        function handleManualSearch(e) {
            e.preventDefault();
            const val = document.getElementById('input-nis-manual').value.trim();
            if (!val) {
                alert("Silakan ketikkan NIS atau Nomor Rekening nasabah terlebih dahulu.");
                return;
            }
            window.location.href = "{{ url('/teller/cari-nis') }}/" + encodeURIComponent(val);
        }

        // Jalankan scanner otomatis saat halaman terbuka
        window.addEventListener('DOMContentLoaded', () => {
            startCameraAuto();
        });
    </script>
</body>
</html>
