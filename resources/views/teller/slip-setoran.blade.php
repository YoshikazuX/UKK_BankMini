<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Setoran Tunai #{{ $trx->id }} - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; margin: 0; padding: 0; }
            .slip-page { box-shadow: none !important; border: none !important; padding: 0 !important; width: 100% !important; margin: 0 !important; }
        }
        .slip-box {
            border: 2px solid #1e293b;
            padding: 16px 20px;
            font-family: Arial, Helvetica, sans-serif;
            position: relative;
        }
    </style>
</head>
<body class="bg-slate-200 py-6 min-h-screen">

    <!-- Action Bar (Kembali & Print) -->
    <div class="max-w-4xl mx-auto mb-4 flex justify-between items-center no-print px-4">
        <a href="javascript:history.back()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs px-4 py-2 rounded-lg transition flex items-center shadow-sm">
            &larr; Kembali
        </a>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs px-5 py-2 rounded-lg transition flex items-center shadow-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Slip Setoran
            </button>
        </div>
    </div>

    <div class="slip-page max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg border border-slate-300 space-y-8">

        <!-- ========================================================================= -->
        <!-- LEMBAR 1: LEMBAR PUTIH (ARSIP BANK MINI) -->
        <!-- ========================================================================= -->
        <div class="slip-box bg-white rounded-lg">
            <!-- Header Slip -->
            <div class="flex justify-between items-start border-b-2 border-slate-800 pb-3 mb-3">
                <div>
                    <h1 class="font-black text-lg tracking-wider text-slate-900 uppercase">LKM MITRA SISWA ABADI</h1>
                    <p class="text-xs text-slate-600 font-medium">Laboratorium Perbankan Mini Siswa • SMK Mitra Siswa Abadi</p>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-slate-900 text-white font-bold text-[11px] px-2.5 py-1 rounded uppercase tracking-wider">SLIP SETORAN TUNAI</span>
                    <p class="text-[11px] text-slate-500 font-bold mt-1 uppercase">LEMBAR 1: ARSIP BANK MINI</p>
                </div>
            </div>

            <!-- Konten Slip -->
            <div class="grid grid-cols-2 gap-4 text-xs mb-3">
                <div>
                    <table class="w-full">
                        <tr>
                            <td class="w-32 py-1 text-slate-600">Tanggal Transaksi</td>
                            <td class="w-3">:</td>
                            <td class="font-bold text-slate-900">{{ date('d F Y - H:i', strtotime($trx->created_at)) }} WIB</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Nomor Rekening</td>
                            <td>:</td>
                            <td class="font-bold tabular-nums text-sm text-slate-900">{{ $trx->no_rekening ?? $trx->nis }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Nama Pemilik / Siswa</td>
                            <td>:</td>
                            <td class="font-bold text-slate-900 uppercase">{{ $trx->nama_siswa }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Kelas / Jurusan</td>
                            <td>:</td>
                            <td class="font-semibold text-slate-800">{{ $trx->kelas }} - {{ $trx->jurusan }}</td>
                        </tr>
                    </table>
                </div>

                <div>
                    <table class="w-full">
                        <tr>
                            <td class="w-28 py-1 text-slate-600">No. Bukti / Trx</td>
                            <td class="w-3">:</td>
                            <td class="tabular-nums font-bold text-slate-900">#TRX-{{ sprintf('%06d', $trx->id) }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Jenis Setoran</td>
                            <td>:</td>
                            <td class="font-semibold text-slate-900">
                                <label class="mr-2"><input type="checkbox" checked disabled> Tabungan</label>
                                <label class="mr-2 text-slate-400"><input type="checkbox" disabled> Angsuran Pinjaman</label>
                                <label class="text-slate-400"><input type="checkbox" disabled> Deposito</label>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Status Validasi</td>
                            <td>:</td>
                            <td>
                                <span class="font-bold uppercase text-xs text-emerald-700">
                                    Sukses Tercatat
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Kotak Nominal & Terbilang -->
            <div class="bg-slate-50 border border-slate-300 rounded p-3 mb-4">
                <div class="flex justify-between items-center border-b border-slate-200 pb-2 mb-2">
                    <span class="text-xs font-bold text-slate-600 uppercase">Jumlah Nominal Setoran (Rp)</span>
                    <span class="font-bold text-xl text-slate-900 tabular-nums">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                </div>
                <div class="text-xs">
                    <span class="text-slate-500 font-bold uppercase">Terbilang: </span>
                    <span class="font-bold italic text-slate-800 uppercase tracking-wide">"{{ $terbilang }}"</span>
                </div>
            </div>

            <!-- Tanda Tangan & Cap Persetujuan -->
            <div class="grid grid-cols-3 gap-4 text-center text-xs pt-2">
                <div>
                    <p class="text-slate-600">Penyetor,</p>
                    <div class="h-16"></div>
                    <p class="font-bold text-slate-900 underline uppercase">({{ $trx->nama_siswa }})</p>
                    <p class="text-[10px] text-slate-500">Tanda Tangan Nasabah</p>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <div class="w-20 h-20 rounded-full border-2 border-dashed border-slate-400 flex items-center justify-center text-[10px] text-slate-400 uppercase font-bold text-center leading-tight">
                        CAP / STEMPEL<br>BANK MINI
                    </div>
                </div>
                <div>
                    <p class="text-slate-600">Teller / Petugas Bank,</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-300">[VALIDASI SISTEM]</span>
                    </div>
                    <p class="font-bold text-slate-900 underline uppercase">({{ $trx->nama_teller ?? Auth::user()->nama_petugas ?? 'Teller' }})</p>
                    <p class="text-[10px] text-slate-500">Tanda Tangan & Nama Terang</p>
                </div>
            </div>
        </div>

        <!-- Garis Potong Perforasi -->
        <div class="border-b-2 border-dashed border-slate-400 relative my-6 text-center">
            <span class="bg-white px-3 text-[11px] text-slate-500 font-bold uppercase relative -top-2.5">
                ✂ - - - - - - - - - - - - - - - - - Gunting / Lipat Di Sini - - - - - - - - - - - - - - - - - ✂
            </span>
        </div>

        <!-- ========================================================================= -->
        <!-- LEMBAR 2: LEMBAR BAWAH (ARSIP NASABAH) -->
        <!-- ========================================================================= -->
        <div class="slip-box bg-amber-50/40 rounded-lg border-amber-800">
            <!-- Header Slip -->
            <div class="flex justify-between items-start border-b-2 border-amber-800 pb-3 mb-3">
                <div>
                    <h1 class="font-black text-lg tracking-wider text-slate-900 uppercase">LKM MITRA SISWA ABADI</h1>
                    <p class="text-xs text-slate-600 font-medium">Laboratorium Perbankan Mini Siswa • SMK Mitra Siswa Abadi</p>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-amber-800 text-white font-bold text-[11px] px-2.5 py-1 rounded uppercase tracking-wider">SLIP SETORAN TUNAI</span>
                    <p class="text-[11px] text-amber-900 font-bold mt-1 uppercase">LEMBAR 2: ARSIP NASABAH</p>
                </div>
            </div>

            <!-- Konten Slip -->
            <div class="grid grid-cols-2 gap-4 text-xs mb-3">
                <div>
                    <table class="w-full">
                        <tr>
                            <td class="w-32 py-1 text-slate-600">Tanggal Transaksi</td>
                            <td class="w-3">:</td>
                            <td class="font-bold text-slate-900">{{ date('d F Y - H:i', strtotime($trx->created_at)) }} WIB</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Nomor Rekening</td>
                            <td>:</td>
                            <td class="font-bold tabular-nums text-sm text-slate-900">{{ $trx->no_rekening ?? $trx->nis }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Nama Pemilik / Siswa</td>
                            <td>:</td>
                            <td class="font-bold text-slate-900 uppercase">{{ $trx->nama_siswa }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Kelas / Jurusan</td>
                            <td>:</td>
                            <td class="font-semibold text-slate-800">{{ $trx->kelas }} - {{ $trx->jurusan }}</td>
                        </tr>
                    </table>
                </div>

                <div>
                    <table class="w-full">
                        <tr>
                            <td class="w-28 py-1 text-slate-600">No. Bukti / Trx</td>
                            <td class="w-3">:</td>
                            <td class="tabular-nums font-bold text-slate-900">#TRX-{{ sprintf('%06d', $trx->id) }}</td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Jenis Setoran</td>
                            <td>:</td>
                            <td class="font-semibold text-slate-900">
                                <label class="mr-2"><input type="checkbox" checked disabled> Tabungan</label>
                                <label class="mr-2 text-slate-400"><input type="checkbox" disabled> Angsuran Pinjaman</label>
                                <label class="text-slate-400"><input type="checkbox" disabled> Deposito</label>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-slate-600">Status Validasi</td>
                            <td>:</td>
                            <td>
                                <span class="font-bold uppercase text-xs text-emerald-700">
                                    Sukses Tercatat
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Kotak Nominal & Terbilang -->
            <div class="bg-amber-100/60 border border-amber-300 rounded p-3 mb-4">
                <div class="flex justify-between items-center border-b border-amber-200 pb-2 mb-2">
                    <span class="text-xs font-bold text-amber-900 uppercase">Jumlah Nominal Setoran (Rp)</span>
                    <span class="font-bold text-xl text-slate-900 tabular-nums">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</span>
                </div>
                <div class="text-xs">
                    <span class="text-amber-900 font-bold uppercase">Terbilang: </span>
                    <span class="font-bold italic text-slate-800 uppercase tracking-wide">"{{ $terbilang }}"</span>
                </div>
            </div>

            <!-- Tanda Tangan & Cap Persetujuan -->
            <div class="grid grid-cols-3 gap-4 text-center text-xs pt-2">
                <div>
                    <p class="text-slate-600">Penyetor,</p>
                    <div class="h-16"></div>
                    <p class="font-bold text-slate-900 underline uppercase">({{ $trx->nama_siswa }})</p>
                    <p class="text-[10px] text-slate-500">Tanda Tangan Nasabah</p>
                </div>
                <div class="flex flex-col items-center justify-center">
                    <div class="w-20 h-20 rounded-full border-2 border-dashed border-amber-400 flex items-center justify-center text-[10px] text-amber-700 uppercase font-bold text-center leading-tight">
                        CAP / STEMPEL<br>BANK MINI
                    </div>
                </div>
                <div>
                    <p class="text-slate-600">Teller / Petugas Bank,</p>
                    <div class="h-16 flex items-center justify-center">
                        <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-300">[VALIDASI SISTEM]</span>
                    </div>
                    <p class="font-bold text-slate-900 underline uppercase">({{ $trx->nama_teller ?? Auth::user()->nama_petugas ?? 'Teller' }})</p>
                    <p class="text-[10px] text-slate-500">Tanda Tangan & Nama Terang</p>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
