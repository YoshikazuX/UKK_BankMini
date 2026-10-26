<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo Simpanan Per Produk - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- HEADER & CETAK -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Laporan Saldo Simpanan Per Produk
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Klasifikasi total simpanan dana pihak ketiga antara produk Tabungan Siswa dan Tabungan Guru/Karyawan.
                </p>
            </div>

            <div class="mt-4 md:mt-0">
                <a href="{{ url('/laporan/saldo-produk?export=print') }}" target="_blank"
                   class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Laporan Produk
                </a>
            </div>
        </div>

        <!-- 3 KARTU SUMMARY PRODUK -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-blue-950 text-white p-6 rounded-xl shadow-md border border-blue-900">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-blue-300 uppercase tracking-wider">Grand Total Saldo Simpanan</p>
                    <span class="p-2 bg-blue-800 text-blue-200 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-white mt-2 tabular-nums">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                <p class="text-xs text-blue-300 mt-1"><span class="tabular-nums">{{ count($siswaList) + count($guruList) }}</span> Total Rekening Nasabah Terdaftar</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded uppercase">Produk 1</span>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Tabungan Siswa</p>
                    </div>
                    <span class="text-xs tabular-nums font-semibold text-slate-500">{{ count($siswaList) }} Siswa</span>
                </div>
                <p class="text-2xl font-bold text-emerald-700 mt-2 tabular-nums">Rp {{ number_format($totalSiswaSaldo, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">
                    Porsi: <span class="tabular-nums">{{ $grandTotal > 0 ? round(($totalSiswaSaldo / $grandTotal) * 100, 1) : 0 }}%</span> dari total DPK
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="bg-purple-100 text-purple-800 text-[10px] font-semibold px-2 py-0.5 rounded uppercase">Produk 2</span>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-1">Tabungan Guru & Karyawan</p>
                    </div>
                    <span class="text-xs tabular-nums font-semibold text-slate-500">{{ count($guruList) }} Nasabah</span>
                </div>
                <p class="text-2xl font-bold text-purple-700 mt-2 tabular-nums">Rp {{ number_format($totalGuruSaldo, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">
                    Porsi: <span class="tabular-nums">{{ $grandTotal > 0 ? round(($totalGuruSaldo / $grandTotal) * 100, 1) : 0 }}%</span> dari total DPK
                </p>
            </div>
        </div>

        <!-- 2 TABEL DAFTAR REKENING PER PRODUK -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- PRODUK TABUNGAN SISWA -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-emerald-50 border-b border-emerald-100 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-emerald-950 text-sm">Daftar Nasabah Tabungan Siswa</h2>
                        <p class="text-[11px] text-emerald-700">Total: <span class="tabular-nums">{{ count($siswaList) }}</span> Rekening Siswa</p>
                    </div>
                    <span class="font-bold text-emerald-800 text-sm tabular-nums">Rp {{ number_format($totalSiswaSaldo, 0, ',', '.') }}</span>
                </div>

                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-700 text-xs font-semibold sticky top-0 border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">No. Rekening</th>
                                <th class="p-2.5">Nama Siswa</th>
                                <th class="p-2.5">Kelas</th>
                                <th class="p-2.5 text-right">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($siswaList as $s)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 tabular-nums font-semibold text-slate-800">{{ $s->no_rekening ?? $s->nis }}</td>
                                <td class="p-2.5 font-medium text-slate-900">{{ $s->nama_siswa }}</td>
                                <td class="p-2.5 text-slate-500">{{ $s->kelas }}</td>
                                <td class="p-2.5 text-right tabular-nums font-semibold text-emerald-700">
                                    Rp {{ number_format($s->saldo, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">Belum ada nasabah kategori siswa.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PRODUK TABUNGAN GURU & KARYAWAN -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-purple-50 border-b border-purple-100 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-purple-950 text-sm">Daftar Nasabah Tabungan Guru & Karyawan</h2>
                        <p class="text-[11px] text-purple-700">Total: <span class="tabular-nums">{{ count($guruList) }}</span> Rekening</p>
                    </div>
                    <span class="font-bold text-purple-800 text-sm tabular-nums">Rp {{ number_format($totalGuruSaldo, 0, ',', '.') }}</span>
                </div>

                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-700 text-xs font-semibold sticky top-0 border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">No. Rekening</th>
                                <th class="p-2.5">Nama Guru / Staf</th>
                                <th class="p-2.5">Unit / Jabatan</th>
                                <th class="p-2.5 text-right">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($guruList as $g)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 tabular-nums font-semibold text-purple-900">{{ $g->no_rekening ?? $g->nis }}</td>
                                <td class="p-2.5 font-medium text-slate-900">{{ $g->nama_siswa }}</td>
                                <td class="p-2.5 text-slate-500">{{ $g->kelas }}</td>
                                <td class="p-2.5 text-right tabular-nums font-semibold text-purple-700">
                                    Rp {{ number_format($g->saldo, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">Belum ada nasabah kategori Guru / Karyawan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
