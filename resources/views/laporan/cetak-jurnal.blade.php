<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Jurnal Akuntansi - Bank Mini LKM MSA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: #fff; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 p-4 md:p-8">

    <div class="no-print max-w-5xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl shadow border border-slate-200">
        <span class="text-sm font-bold text-slate-700">Pratinjau Cetak Jurnal Umum Akuntansi (Total {{ count($jurnal) }} Baris)</span>
        <div class="flex space-x-3">
            <button onclick="window.close()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition">
                Tutup
            </button>
            <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-5 py-2 rounded-lg text-sm shadow transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Lembar Jurnal
            </button>
        </div>
    </div>

    <div class="max-w-5xl mx-auto bg-white p-8 md:p-12 border border-slate-300 shadow-md print:border-none print:shadow-none print:p-0">
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
                <p class="font-bold text-slate-800">BUKU JURNAL UMUM</p>
                <p class="text-[11px] text-slate-500">Dicetak: {{ date('d/m/Y H:i') }} WIB</p>
            </div>
        </div>

        <div class="text-center mb-6">
            <h1 class="text-lg font-black uppercase text-slate-900 underline underline-offset-4">
                LAPORAN BUKU JURNAL TRANSAKSI AKUNTANSI
            </h1>
            <p class="text-xs text-slate-500 mt-1">Status Keseimbangan: <strong class="{{ $isBalanced ? 'text-emerald-700' : 'text-rose-700' }}">{{ $isBalanced ? 'SEIMBANG (KLOP)' : 'DEFISIT / TIDAK SEIMBANG' }}</strong></p>
        </div>

        <!-- TABEL JURNAL -->
        <table class="w-full text-[11px] border border-slate-400 border-collapse mb-8">
            <thead class="bg-slate-100 font-bold uppercase border-b border-slate-400 text-slate-800">
                <tr>
                    <th class="p-2 border border-slate-300 text-center w-8">No</th>
                    <th class="p-2 border border-slate-300">Tanggal</th>
                    <th class="p-2 border border-slate-300">No. Rekening</th>
                    <th class="p-2 border border-slate-300">Nama Nasabah</th>
                    <th class="p-2 border border-slate-300">Keterangan Akuntansi</th>
                    <th class="p-2 border border-slate-300">Teller</th>
                    <th class="p-2 border border-slate-300 text-right">Debit (Rp)</th>
                    <th class="p-2 border border-slate-300 text-right">Kredit (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jurnal as $idx => $j)
                <tr>
                    <td class="p-1.5 border border-slate-300 text-center tabular-nums">{{ $idx + 1 }}</td>
                    <td class="p-1.5 border border-slate-300 tabular-nums">{{ date('d/m/Y', strtotime($j->tanggal)) }}</td>
                    <td class="p-1.5 border border-slate-300 tabular-nums font-bold">{{ $j->no_rekening ?? $j->nis ?? '-' }}</td>
                    <td class="p-1.5 border border-slate-300">{{ $j->nama_siswa ?? '-' }}</td>
                    <td class="p-1.5 border border-slate-300">{{ $j->keterangan }}</td>
                    <td class="p-1.5 border border-slate-300">{{ $j->nama_teller ?? 'Teller' }}</td>
                    <td class="p-1.5 border border-slate-300 text-right tabular-nums font-bold {{ $j->debit > 0 ? 'text-emerald-800' : '' }}">
                        {{ $j->debit > 0 ? number_format($j->debit, 0, ',', '.') : '-' }}
                    </td>
                    <td class="p-1.5 border border-slate-300 text-right tabular-nums font-bold {{ $j->kredit > 0 ? 'text-rose-800' : '' }}">
                        {{ $j->kredit > 0 ? number_format($j->kredit, 0, ',', '.') : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="p-4 text-center text-slate-400 border border-slate-300">Tidak ada transaksi yang tercatat.</td>
                </tr>
                @endforelse
                <tr class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-500">
                    <td colspan="6" class="p-2 text-right uppercase border border-slate-300">Total Akumulasi:</td>
                    <td class="p-2 text-right tabular-nums border border-slate-300 text-emerald-900">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="p-2 text-right tabular-nums border border-slate-300 text-rose-900">Rp {{ number_format($totalKredit, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TANDA TANGAN -->
        <div class="grid grid-cols-3 gap-6 text-center text-xs mt-10">
            <div>
                <p class="text-slate-500 mb-1">Dibuat Oleh,</p>
                <p class="font-bold text-slate-700">Petugas Teller Loket</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline uppercase">( ................................... )</p>
                </div>
                <p class="text-[10px] text-slate-400">Petugas Kasir</p>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Diperiksa Oleh,</p>
                <p class="font-bold text-slate-700">Bagian Accounting</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline uppercase">( ................................... )</p>
                </div>
                <p class="text-[10px] text-slate-400">Penyusun Pembukuan</p>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Disetujui Oleh,</p>
                <p class="font-bold text-slate-700">Supervisor / Kepala Bank Mini</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline uppercase">( Handayani, S.Pd., M.M )</p>
                </div>
                <p class="text-[10px] text-slate-400">NIP. 19850712 201001 2 021</p>
            </div>
        </div>
    </div>
</body>
</html>
