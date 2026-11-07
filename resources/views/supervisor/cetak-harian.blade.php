<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Favicon / Logo Web -->
    <link rel="icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Times New Roman', Times, serif; color: black; background: #e2e8f0; }
        .kertas { background: white; width: 210mm; min-height: 297mm; padding: 20mm; margin: 20px auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .tabel-uang, .tabel-rincian { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .tabel-uang th, .tabel-uang td, .tabel-rincian th, .tabel-rincian td { border: 1px solid black; padding: 6px 12px; }
        .tabel-uang th, .tabel-rincian th { background-color: #facc15; font-weight: bold; text-align: center; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .input-lembar { width: 60px; border: 1px solid #ccc; text-align: center; font-family: inherit; }
        .input-lembar:focus { outline: none; border-color: black; }

        @media print {
            body { background: white; margin: 0; padding: 0; }
            .kertas { box-shadow: none; margin: 0; padding: 0; width: 100%; }
            .no-print { display: none !important; }
            .input-lembar { border: none !important; font-weight: bold; }
            .page-break { page-break-before: always; margin-top: 40px; }
        }
    </style>
</head>
<body>

    <div class="max-w-4xl mx-auto mt-4 mb-4 flex justify-end items-stretch gap-2 font-sans no-print">
        <a href="{{ url('/supervisor/laporan') }}" class="bg-slate-500 hover:bg-slate-600 text-white py-2.5 px-5 rounded-lg text-sm font-bold shadow-sm transition flex items-center">
            &larr; Kembali
        </a>
        <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white py-2.5 px-5 rounded-lg text-sm font-bold shadow-sm transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Cetak Laporan
        </button>
    </div>

    <div class="kertas">

        <div class="text-center mb-8">
            <h1 class="text-xl font-bold uppercase tracking-wide">LKM MITRA SISWA ABADI</h1>
            <h2 class="text-lg font-bold uppercase">LAPORAN HARIAN</h2>
        </div>

        <div class="mb-6 leading-relaxed">
            <p>Hari, tanggal : <strong>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</strong></p>
            <p class="mt-2">Petugas Teller Hari Ini :</p>
            <ol class="list-decimal list-inside ml-4 mt-1 font-bold">
                @forelse($petugasTeller as $petugas)
                    <li>{{ $petugas }}</li>
                @empty
                    <li class="italic font-normal text-gray-500">Belum ada transaksi hari ini</li>
                @endforelse
            </ol>
        </div>

        <h3 class="font-bold underline mb-2">SALDO AWAL</h3>
        <table class="tabel-uang text-sm">
            <thead>
                <tr>
                    <th class="w-1/4">NOMINAL</th>
                    <th colspan="3" class="w-2/4">JUMLAH</th>
                    <th class="w-1/4">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach([100000, 50000, 20000, 10000, 5000] as $nom)
                <tr>
                    <td class="text-right pr-4">Rp{{ number_format($nom, 0, ',', '.') }}</td>
                    <td class="text-center w-10">X</td>
                    <td class="w-24 text-center">
                        <input type="number" class="input-lembar awal-qty" data-nilai="{{ $nom }}" oninput="hitungAwal()">
                    </td>
                    <td>Lembar</td>
                    <td class="text-right pr-4">= <span class="awal-subtotal">0</span></td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="font-bold">TOTAL SALDO AWAL</td>
                    <td class="font-bold text-right pr-4">Rp <span id="awal-grandtotal">{{ number_format($brankas->saldo_awal, 0, ',', '.') }}</span></td>
                </tr>
            </tbody>
        </table>

        <h3 class="font-bold mb-3 mt-8">Rekap Transaksi :</h3>
        <div class="ml-4 mb-8">
            <table class="w-3/4">
                <tr><td class="w-10">1.</td><td>Saldo awal</td><td class="w-4">:</td><td class="font-bold">Rp {{ number_format($brankas->saldo_awal, 0, ',', '.') }}</td></tr>
                <tr><td>2.</td><td>Setoran tabungan</td><td>:</td><td class="font-bold">Rp {{ number_format($brankas->masuk, 0, ',', '.') }}</td></tr>
                <tr><td>3.</td><td>Administrasi buku tabungan</td><td>:</td><td class="font-bold">Rp {{ number_format($totalAdmin, 0, ',', '.') }}</td></tr>
                <tr><td>4.</td><td>Penarikan tabungan</td><td>:</td><td class="font-bold">Rp {{ number_format($brankas->keluar, 0, ',', '.') }}</td></tr>
                <tr><td>5.</td><td>Saldo Akhir</td><td>:</td><td class="font-bold">Rp {{ number_format($brankas->total, 0, ',', '.') }}</td></tr>
            </table>
        </div>

        <table class="tabel-uang text-sm">
            <thead>
                <tr>
                    <th class="w-1/4">NOMINAL</th>
                    <th colspan="3" class="w-2/4">JUMLAH</th>
                    <th class="w-1/4">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach([100000, 50000, 20000, 10000, 5000, 2000, 1000] as $nom)
                <tr>
                    <td class="text-right pr-4">Rp{{ number_format($nom, 0, ',', '.') }}</td>
                    <td class="text-center w-10">X</td>
                    <td class="w-24 text-center">
                        <input type="number" class="input-lembar akhir-qty-lembar" data-nilai="{{ $nom }}" oninput="hitungAkhir()">
                    </td>
                    <td>Lembar</td>
                    <td class="text-right pr-4">= <span class="akhir-subtotal">0</span></td>
                </tr>
                @endforeach
                @foreach([1000, 500, 200, 100] as $nom)
                <tr>
                    <td class="text-right pr-4">Rp{{ number_format($nom, 0, ',', '.') }}</td>
                    <td class="text-center w-10">X</td>
                    <td class="w-24 text-center">
                        <input type="number" class="input-lembar akhir-qty-keping" data-nilai="{{ $nom }}" oninput="hitungAkhir()">
                    </td>
                    <td>Keping</td>
                    <td class="text-right pr-4">= <span class="akhir-subtotal">0</span></td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="font-bold">TOTAL SALDO AKHIR</td>
                    <td class="font-bold text-right pr-4 text-lg">Rp <span id="akhir-grandtotal">{{ number_format($brankas->total, 0, ',', '.') }}</span></td>
                </tr>
            </tbody>
        </table>

        <div class="page-break">
            <h3 class="font-bold uppercase mb-4 text-center mt-8">Lampiran: Rincian Transaksi Hari Ini</h3>
            <table class="tabel-rincian text-sm">
                <thead>
                    <tr>
                        <th class="w-12">No</th>
                        <th>Waktu</th>
                        <th>Nama Nasabah (NIS)</th>
                        <th>Petugas Teller</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksiHariIni as $index => $t)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }}</td>
                        <td>{{ $t->nama_siswa }} <br><span class="text-xs text-gray-500">{{ $t->nis }}</span></td>
                        <td>{{ $t->nama_petugas ?? 'Sistem' }}</td>
                        <td class="text-center uppercase font-bold">{{ $t->jenis_transaksi }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center italic py-4">Belum ada transaksi terekam pada hari ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function hitungAwal() {
            let total = 0;
            document.querySelectorAll('.awal-qty').forEach((input, index) => {
                let nilai = parseInt(input.getAttribute('data-nilai'));
                let qty = parseInt(input.value) || 0;
                let subtotal = nilai * qty;
                document.querySelectorAll('.awal-subtotal')[index].innerText = formatRupiah(subtotal);
                total += subtotal;
            });
            document.getElementById('awal-grandtotal').innerText = formatRupiah(total);
        }

        function hitungAkhir() {
            let total = 0;
            let allInputs = document.querySelectorAll('.akhir-qty-lembar, .akhir-qty-keping');
            allInputs.forEach((input, index) => {
                let nilai = parseInt(input.getAttribute('data-nilai'));
                let qty = parseInt(input.value) || 0;
                let subtotal = nilai * qty;
                document.querySelectorAll('.akhir-subtotal')[index].innerText = formatRupiah(subtotal);
                total += subtotal;
            });
            document.getElementById('akhir-grandtotal').innerText = formatRupiah(total);
        }
    </script>
</body>
</html>
