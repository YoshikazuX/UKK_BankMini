<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Saldo Simpanan Per Produk - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: #fff; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 p-4 md:p-8">

    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl shadow border border-slate-200">
        <span class="text-sm font-bold text-slate-700">Pratinjau Cetak Laporan Saldo Simpanan Per Produk</span>
        <div class="flex space-x-3">
            <button onclick="window.close()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition">
                Tutup
            </button>
            <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-5 py-2 rounded-lg text-sm shadow transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Laporan
            </button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 md:p-12 border border-slate-300 shadow-md print:border-none print:shadow-none print:p-0">
        <!-- KOP SURAT -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-4 mb-6">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-blue-900 text-white rounded-lg flex items-center justify-center font-black text-2xl tracking-tighter">
                    MSA
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 uppercase">LKM MITRA SISWA ABADI</h2>
                    <p class="text-xs font-bold text-slate-600 uppercase">Laboratorium Perbankan & Lembaga Keuangan Mikro Sekolah</p>
                    <p class="text-[11px] text-slate-500">Jl. Pendidikan No. 01, Kompleks Kampus Sekolah Kejuruan | Telp: (021) 8899-7711</p>
                </div>
            </div>
            <div class="text-right text-xs">
                <p class="font-bold text-slate-800">REKAPITULASI PRODUK</p>
                <p class="text-[11px] text-slate-500">Per: {{ date('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <div class="text-center mb-6">
            <h1 class="text-lg font-black uppercase text-slate-900 underline underline-offset-4">
                LAPORAN SALDO SIMPANAN DANA PIHAK KETIGA (DPK) PER PRODUK
            </h1>
            <p class="text-xs text-slate-500 mt-1">Per Tanggal: {{ date('d F Y') }}</p>
        </div>

        <!-- TABEL REKAP PRODUK -->
        <table class="w-full text-xs border border-slate-400 border-collapse mb-6">
            <thead class="bg-slate-100 font-bold uppercase text-slate-800 border-b border-slate-400">
                <tr>
                    <th class="p-2 border border-slate-300 text-center w-10">No</th>
                    <th class="p-2 border border-slate-300">Nama Produk Simpanan</th>
                    <th class="p-2 border border-slate-300 text-center">Jumlah Rekening</th>
                    <th class="p-2 border border-slate-300 text-right">Porsi (%)</th>
                    <th class="p-2 border border-slate-300 text-right">Total Saldo (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="p-2 border border-slate-300 text-center tabular-nums">1</td>
                    <td class="p-2 border border-slate-300 font-bold">Tabungan Siswa (Pelajar)</td>
                    <td class="p-2 border border-slate-300 text-center tabular-nums">{{ count($siswaList) }} Rekening</td>
                    <td class="p-2 border border-slate-300 text-right tabular-nums">{{ $grandTotal > 0 ? round(($totalSiswaSaldo / $grandTotal) * 100, 1) : 0 }}%</td>
                    <td class="p-2 border border-slate-300 text-right tabular-nums font-bold text-slate-900">Rp {{ number_format($totalSiswaSaldo, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="p-2 border border-slate-300 text-center tabular-nums">2</td>
                    <td class="p-2 border border-slate-300 font-bold">Tabungan Guru & Karyawan</td>
                    <td class="p-2 border border-slate-300 text-center tabular-nums">{{ count($guruList) }} Rekening</td>
                    <td class="p-2 border border-slate-300 text-right tabular-nums">{{ $grandTotal > 0 ? round(($totalGuruSaldo / $grandTotal) * 100, 1) : 0 }}%</td>
                    <td class="p-2 border border-slate-300 text-right tabular-nums font-bold text-slate-900">Rp {{ number_format($totalGuruSaldo, 0, ',', '.') }}</td>
                </tr>
                <tr class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-500">
                    <td colspan="2" class="p-2 text-right uppercase border border-slate-300">Total Akumulasi DPK:</td>
                    <td class="p-2 text-center tabular-nums border border-slate-300">{{ count($siswaList) + count($guruList) }} Rekening</td>
                    <td class="p-2 text-right tabular-nums border border-slate-300">100.0%</td>
                    <td class="p-2 text-right tabular-nums border border-slate-300 text-sm">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TANDA TANGAN -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-12">
            <div>
                <p class="text-slate-500 mb-1">Dibuat & Diverifikasi Oleh,</p>
                <p class="font-bold text-slate-700">Bagian Accounting / Laporan</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline uppercase">( ................................... )</p>
                </div>
                <p class="text-[10px] text-slate-400">Staf Pembukuan</p>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Mengetahui & Menyetujui,</p>
                <p class="font-bold text-slate-700">Kepala Unit Bank Mini</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline uppercase">( Handayani, S.Pd., M.M )</p>
                </div>
                <p class="text-[10px] text-slate-400">NIP. 19850712 201001 2 021</p>
            </div>
        </div>
    </div>
</body>
</html>
