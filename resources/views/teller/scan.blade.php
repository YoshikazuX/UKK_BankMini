<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Nasabah - E-Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Library HTML5 QR Code Scanner -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <x-navbar />

    <main class="max-w-xl mx-auto py-10 px-4">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 text-center">
            <h1 class="text-xl font-bold text-slate-800 mb-2">Scan QR Code Nasabah</h1>
            <p class="text-slate-600 text-sm mb-6">Arahkan kamera ke QR Code kartu pelajar atau aplikasi mobile nasabah.</p>

            <!-- Kotak Kamera Scanner -->
            <div id="reader" class="w-full rounded-lg overflow-hidden border border-slate-300 mb-4"></div>

            <div id="result" class="text-sm font-medium text-slate-700">
                Status: <span class="text-blue-600">Menyiapkan kamera...</span>
            </div>
        </div>
    </main>

    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Hentikan scanner setelah berhasil membaca
            html5QrcodeScanner.clear().then(_ => {
                document.getElementById('result').innerHTML = `Status: <span class="text-emerald-600 font-bold">Berhasil mendeteksi NIS: ${decodedText}. Membuka transaksi...</span>`;
                // Alihkan ke rute pencarian NIS di Laravel
                window.location.href = "{{ url('/teller/cari-nis') }}/" + decodedText;
            }).catch(error => {
                console.error("Gagal menghentikan scanner.", error);
            });
        }

        function onScanFailure(error) {
            // Abaikan error per frame agar tidak memenuhi console
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false);
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>
