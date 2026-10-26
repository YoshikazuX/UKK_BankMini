<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Buku Tabungan - {{ $nasabah->nama_siswa }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Courier New', Courier, monospace; color: black; background: #e2e8f0; }
        .buku-kertas { background: #fdfbf7; width: 210mm; min-height: 148mm; padding: 15mm; margin: 20px auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); border: 1px solid #cbd5e1; }
        .tabel-buku { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        .tabel-buku th, .tabel-buku td { border: 1px solid #334155; padding: 4px 6px; }
        .tabel-buku th { background-color: #cbd5e1; text-align: center; font-weight: bold; }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .buku-kertas { box-shadow: none; margin: 0; padding: 0; width: 100%; border: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="max-w-4xl mx-auto mt-4 mb-2 flex justify-between items-center no-print px-4">
        <a href="javascript:history.back()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs px-4 py-2 rounded-lg transition">
            &larr; Kembali
        </a>
        <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs px-5 py-2 rounded-lg transition shadow">
            Cetak Lembar Pas Buku
        </button>
    </div>

    <div class="buku-kertas">
        <div class="border-b-2 border-slate-800 pb-2 mb-3 flex justify-between items-end">
            <div>
                <h1 class="font-bold text-base tracking-wider uppercase">LKM MITRA SISWA ABADI</h1>
                <p class="text-[10px]">BUKU TABUNGAN SISWA / BUKU BESAR PEMBANTU</p>
            </div>
            <div class="text-right text-[11px]">
                <p>No. Rekening : <strong>{{ $nasabah->no_rekening ?? $nasabah->nis }}</strong></p>
                <p>Nama Nasabah : <strong>{{ $nasabah->nama_siswa }}</strong> ({{ $nasabah->kelas }})</p>
            </div>
        </div>

        <table class="tabel-buku">
            <thead>
                <tr>
                    <th style="width: 30px;">NO</th>
                    <th style="width: 80px;">TANGGAL</th>
                    <th>KETERANGAN / SANDI</th>
                    <th style="width: 100px;">DEBIT (TARIK)</th>
                    <th style="width: 100px;">KREDIT (SETOR)</th>
                    <th style="width: 110px;">SALDO</th>
                    <th style="width: 70px;">PARAF</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transaksi as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ date('d/m/y', strtotime($trx->created_at)) }}</td>
                    <td>
                        @if($trx->jenis_transaksi === 'setor')
                            SETOR TUNAI
                        @elseif($trx->jenis_transaksi === 'tarik')
                            TARIK TUNAI
                        @else
                            ADM BUKU
                        @endif
                    </td>
                    <td class="text-right">
                        {{ $trx->jenis_transaksi !== 'setor' ? number_format($trx->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right">
                        {{ $trx->jenis_transaksi === 'setor' ? number_format($trx->nominal, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-bold">
                        {{ number_format($trx->saldo_berjalan, 0, ',', '.') }}
                    </td>
                    <td class="text-center text-[10px]">
                        {{ substr($trx->nama_teller ?? 'TLR', 0, 5) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-6 italic">Belum ada transaksi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
