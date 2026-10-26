<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tabungan - {{ $nasabah->nama_siswa }} - LKM Mitra Siswa Abadi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 min-h-screen flex flex-col antialiased">

    <x-navbar />

    <!-- KONTEN BUKU TABUNGAN NASABAH -->
    <main class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 mb-5 rounded-xl text-xs shadow-sm no-print">
            {{ session('success') }}
        </div>
        @endif

        <!-- TOP BAR: TOMBOL KEMBALI & CETAK BUKU -->
        <div class="mb-5 flex flex-wrap justify-between items-center gap-3 no-print">
            <a href="javascript:history.back()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-medium px-3.5 py-2 rounded-lg shadow-sm transition flex items-center">
                &larr; Kembali ke Daftar
            </a>

            <div class="flex items-center space-x-2">
                @if($role === 'teller')
                    <a href="{{ url('/teller/transaksi/'.$nasabah->id) }}" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs px-4 py-2 rounded-lg shadow-sm transition flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buka Form Transaksi
                    </a>
                    <a href="{{ url('/buku-tabungan/cetak/'.$nasabah->id) }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs px-4 py-2 rounded-lg shadow-sm transition flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Lembar Buku Tabungan
                    </a>
                @else
                    <a href="{{ url('/buku-tabungan/cetak/'.$nasabah->id) }}" target="_blank" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs px-4 py-2 rounded-lg shadow-sm transition flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Cetak Lembar Buku Tabungan
                    </a>
                @endif
            </div>
        </div>

        <!-- KARTU PROFIL NASABAH (BERSIH & PROFESIONAL) -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-none mb-6">
            <!-- Bagian Atas Kartu Profil: Nama, Status, Saldo -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <h1 class="text-xl font-bold text-slate-900">{{ ucwords(strtolower($nasabah->nama_siswa)) }}</h1>
                    @if(($nasabah->status ?? 'aktif') === 'aktif')
                        <span class="bg-emerald-50 text-emerald-700 text-xs font-medium px-2.5 py-0.5 rounded-full">Aktif</span>
                    @else
                        <span class="bg-slate-100 text-slate-600 text-xs font-medium px-2.5 py-0.5 rounded-full">Nonaktif</span>
                    @endif
                </div>

                <div class="text-left sm:text-right">
                    <p class="text-xs text-slate-500 font-medium">Saldo Tersedia</p>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Bagian Bawah Kartu Profil: 4 Kolom Informasi -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 border-t border-slate-100 pt-4 mt-4">
                <div>
                    <p class="text-xs text-slate-500">Nomor Rekening</p>
                    <p class="text-sm font-medium text-slate-900 tabular-nums mt-0.5">{{ $nasabah->no_rekening ?? $nasabah->nis }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Nomor Induk Siswa (NIS)</p>
                    <p class="text-sm font-medium text-slate-900 tabular-nums mt-0.5">{{ $nasabah->nis }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Kelas & Jurusan</p>
                    <p class="text-sm font-medium text-slate-900 mt-0.5">{{ $nasabah->kelas }} ({{ $nasabah->jurusan }})</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Kategori Nasabah</p>
                    <p class="text-sm font-medium text-slate-900 mt-0.5">{{ ucwords(strtolower($nasabah->kategori ?? 'Siswa')) }}</p>
                </div>
            </div>
        </div>

        <!-- KARTU TABEL MUTASI REKENING -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-none overflow-hidden">
            <!-- Header Tabel Mutasi -->
            <div class="p-4 sm:p-5 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-base font-semibold text-slate-900">Riwayat Mutasi Rekening</h2>
                <span class="text-xs text-slate-500 font-medium">Total: {{ count($transaksi) }} Transaksi</span>
            </div>

            <!-- Isi Tabel Mutasi -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 text-xs font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-3 w-12 text-center">No</th>
                            <th class="p-3">Tanggal & Waktu</th>
                            <th class="p-3">Keterangan</th>
                            <th class="p-3 text-right">Penarikan (Debit)</th>
                            <th class="p-3 text-right">Setoran (Kredit)</th>
                            <th class="p-3 text-right">Saldo Berjalan</th>
                            <th class="p-3 text-center">Teller</th>
                            <th class="p-3 text-center no-print">Slip</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transaksi as $index => $trx)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3 text-center tabular-nums text-slate-500">{{ $index + 1 }}</td>
                            <td class="p-3 tabular-nums text-slate-600 whitespace-nowrap">{{ date('d/m/Y H:i', strtotime($trx->created_at)) }}</td>
                            <td class="p-3">
                                <div class="font-medium text-slate-900">
                                    @if($trx->jenis_transaksi === 'setor')
                                        Setoran Tunai
                                    @elseif($trx->jenis_transaksi === 'tarik')
                                        Penarikan Tunai
                                    @else
                                        Biaya Administrasi
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400 block tabular-nums">#TRX-{{ sprintf('%06d', $trx->id) }}</span>
                            </td>

                            <!-- Mutasi Debit (Penarikan) -->
                            <td class="p-3 text-right tabular-nums text-rose-700 font-medium">
                                @if($trx->jenis_transaksi !== 'setor')
                                    Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Mutasi Kredit (Setoran) -->
                            <td class="p-3 text-right tabular-nums text-emerald-700 font-medium">
                                @if($trx->jenis_transaksi === 'setor')
                                    Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>

                            <!-- Saldo Berjalan -->
                            <td class="p-3 text-right tabular-nums font-semibold text-slate-900">
                                Rp {{ number_format($trx->saldo_berjalan, 0, ',', '.') }}
                            </td>

                            <!-- Petugas / Teller -->
                            <td class="p-3 text-center text-slate-600">
                                {{ $trx->nama_teller ?? 'Teller' }}
                            </td>

                            <!-- Tombol Cetak Slip -->
                            <td class="p-3 text-center no-print whitespace-nowrap">
                                @if($trx->jenis_transaksi === 'setor')
                                    <a href="{{ url('/teller/slip-setor/'.$trx->id) }}" target="_blank" class="text-slate-600 hover:text-slate-900 font-medium hover:underline text-xs" title="Cetak Slip Setoran">
                                        Slip Setor
                                    </a>
                                @else
                                    <a href="{{ url('/teller/slip-tarik/'.$trx->id) }}" target="_blank" class="text-slate-600 hover:text-slate-900 font-medium hover:underline text-xs" title="Cetak Slip Penarikan">
                                        Slip Tarik
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400 italic">
                                Belum ada catatan mutasi transaksi pada rekening ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-semibold text-xs text-slate-800 border-t border-slate-200">
                            <td colspan="3" class="p-3 text-right uppercase">Total Akumulasi:</td>
                            <td class="p-3 text-right text-rose-700 tabular-nums">Rp {{ number_format($totalTarik, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-emerald-700 tabular-nums">Rp {{ number_format($totalSetor, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-slate-900 tabular-nums font-bold">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </main>
</body>
</html>
