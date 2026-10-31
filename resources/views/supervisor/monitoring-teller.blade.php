<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Produktivitas & Aktivitas Teller - Supervisor</title>
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
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Monitoring Produktivitas & Aktivitas Teller</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Pemantauan kinerja operasional loket berbasis data transaksi nyata tanpa membandingkan nominal secara subyektif.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('supervisor.rekonsiliasi') }}" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-3.5 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Rekonsiliasi Tutup Kas
                </a>
            </div>
        </div>

        <!-- FORM FILTER PERIODE & TELLER -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form action="{{ route('supervisor.monitoring-teller') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="dari_tanggal" value="{{ $dariTanggal }}"
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai_tanggal" value="{{ $sampaiTanggal }}"
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none font-medium">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Teller</label>
                    <select name="teller_id" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Teller</option>
                        @foreach($allTellers as $t)
                            <option value="{{ $t->id }}" {{ (string) $selectedTellerId === (string) $t->id ? 'selected' : '' }}>
                                {{ $t->nama_petugas }} ({{ $t->kelas ?? 'Loket' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Transaksi</label>
                    <select name="jenis_transaksi" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Jenis</option>
                        <option value="setor" {{ $selectedJenis === 'setor' ? 'selected' : '' }}>Setoran</option>
                        <option value="tarik" {{ $selectedJenis === 'tarik' ? 'selected' : '' }}>Penarikan</option>
                        <option value="admin" {{ $selectedJenis === 'admin' ? 'selected' : '' }}>Biaya Admin</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Terapkan
                    </button>
                    <a href="{{ route('supervisor.monitoring-teller') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3 rounded-lg text-xs transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- 6 KARTU RINGKASAN PRODUKTIVITAS OPERASIONAL -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Transaksi</span>
                <p class="text-xl font-black text-slate-900 mt-1 tabular-nums">{{ $ringkasanProduktivitas->total_transaksi_keseluruhan }}</p>
                <span class="text-[10px] text-slate-400">Di luar transaksi batal</span>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Transaksi Sukses</span>
                <p class="text-xl font-black text-emerald-700 mt-1 tabular-nums">{{ $ringkasanProduktivitas->total_sukses }}</p>
                <span class="text-[10px] text-emerald-600">Berhasil diproses</span>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Dibatalkan (Void)</span>
                <p class="text-xl font-black text-rose-700 mt-1 tabular-nums">{{ $ringkasanProduktivitas->total_batal }}</p>
                <span class="text-[10px] text-rose-600">Dipisahkan dari sukses</span>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Total Setoran</span>
                <p class="text-lg font-black text-slate-900 mt-1 tabular-nums truncate">Rp {{ number_format($ringkasanProduktivitas->total_setoran_keseluruhan, 0, ',', '.') }}</p>
                <span class="text-[10px] text-slate-400">Dana nasabah masuk</span>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Total Penarikan</span>
                <p class="text-lg font-black text-slate-900 mt-1 tabular-nums truncate">Rp {{ number_format($ringkasanProduktivitas->total_penarikan_keseluruhan, 0, ',', '.') }}</p>
                <span class="text-[10px] text-slate-400">Dana nasabah ditarik</span>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">Pengajuan Kas</span>
                <p class="text-xl font-black text-amber-700 mt-1 tabular-nums">{{ $ringkasanProduktivitas->total_pengajuan_kas }}</p>
                <span class="text-[10px] text-slate-400">Permintaan droping</span>
            </div>
        </div>

        <!-- GRAFIK VISUALISASI AKTIVITAS (CSS/SVG DATA-DRIVEN) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- 1. GRAFIK BATANG VOLUME TRANSAKSI PER TELLER -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h2 class="text-sm font-bold text-slate-900 pb-2 mb-3 border-b border-slate-100 flex items-center justify-between">
                    <span>Distribusi Volume Transaksi per Teller</span>
                    <span class="text-[10px] text-slate-400 font-normal">Periode: {{ date('d/m/y', strtotime($dariTanggal)) }} - {{ date('d/m/y', strtotime($sampaiTanggal)) }}</span>
                </h2>

                <div class="space-y-3 pt-2">
                    @php
                        $maxTrx = max(array_merge($chartTrxCounts, [1]));
                    @endphp
                    @foreach($tellerStats as $stat)
                        @php
                            $pct = round(($stat->total_transaksi / $maxTrx) * 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-semibold text-slate-800">{{ $stat->teller->nama_petugas }}</span>
                                <span class="tabular-nums font-bold text-slate-900">{{ $stat->total_transaksi }} transaksi ({{ $stat->transaksi_sukses }} sukses &bull; {{ $stat->transaksi_batal }} void)</span>
                            </div>
                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden flex">
                                <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 2. PERBANDINGAN SETORAN VS PENARIKAN -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h2 class="text-sm font-bold text-slate-900 pb-2 mb-3 border-b border-slate-100 flex items-center justify-between">
                    <span>Arus Kas Tunai: Setoran vs Penarikan</span>
                    <span class="text-[10px] text-slate-400 font-normal">Rasio Nominal Transaksi</span>
                </h2>

                <div class="space-y-3 pt-2">
                    @php
                        $maxCash = max(array_merge($chartDepositSums, $chartWithdrawalSums, [1]));
                    @endphp
                    @foreach($tellerStats as $stat)
                        @php
                            $pctDep = round(($stat->total_setoran / $maxCash) * 100);
                            $pctWdr = round(($stat->total_penarikan / $maxCash) * 100);
                        @endphp
                        <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100 text-xs">
                            <span class="font-bold text-slate-800 block mb-1.5">{{ $stat->teller->nama_petugas }}</span>
                            <!-- Bar Setoran -->
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-16 text-[10px] text-emerald-700 font-semibold">Setoran:</span>
                                <div class="flex-1 bg-slate-200 h-2 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $pctDep }}%"></div>
                                </div>
                                <span class="tabular-nums font-bold text-emerald-800 text-[11px] w-28 text-right">Rp {{ number_format($stat->total_setoran, 0, ',', '.') }}</span>
                            </div>
                            <!-- Bar Penarikan -->
                            <div class="flex items-center gap-2">
                                <span class="w-16 text-[10px] text-rose-700 font-semibold">Penarikan:</span>
                                <div class="flex-1 bg-slate-200 h-2 rounded-full overflow-hidden">
                                    <div class="bg-rose-500 h-2 rounded-full" style="width: {{ $pctWdr }}%"></div>
                                </div>
                                <span class="tabular-nums font-bold text-rose-800 text-[11px] w-28 text-right">Rp {{ number_format($stat->total_penarikan, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TABEL MONITORING AKTIVITAS DETAIL PER TELLER -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">
                    Rekapitulasi Operasional Setiap Petugas Teller
                </span>
                <span class="text-slate-500">Data bersumber dari transaksi dan daily report aktual</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Nama Teller & Kelas</th>
                            <th class="py-3 px-4 text-center">Jml Transaksi</th>
                            <th class="py-3 px-4 text-right">Total Setoran</th>
                            <th class="py-3 px-4 text-right">Total Penarikan</th>
                            <th class="py-3 px-4 text-center">Void (Koreksi)</th>
                            <th class="py-3 px-4 text-center">Pengajuan Kas</th>
                            <th class="py-3 px-4 text-center">Status Daily Report</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($tellerStats as $stat)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">{{ $stat->teller->nama_petugas }}</span>
                                <span class="text-[10px] text-slate-400">Kelas: {{ $stat->teller->kelas ?? '-' }} &bull; @ {{ $stat->teller->username }}</span>
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums font-bold text-slate-900">
                                {{ $stat->total_transaksi }}
                                <span class="block text-[10px] font-normal text-emerald-600">{{ $stat->transaksi_sukses }} Sukses</span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-700 tabular-nums">
                                Rp {{ number_format($stat->total_setoran, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-rose-700 tabular-nums">
                                Rp {{ number_format($stat->total_penarikan, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums">
                                @if($stat->transaksi_batal > 0)
                                    <span class="text-xs font-semibold text-rose-700">
                                        {{ $stat->transaksi_batal }} Void
                                    </span>
                                @else
                                    <span class="text-slate-400 font-normal">0</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center tabular-nums">
                                @if($stat->pengajuan_kas_count > 0)
                                    <span class="text-xs font-semibold text-amber-700">
                                        {{ $stat->pengajuan_kas_count }} Tiket
                                    </span>
                                @else
                                    <span class="text-slate-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($stat->daily_report_status === 'approved')
                                    <span class="text-xs font-semibold text-emerald-700">Approved</span>
                                @elseif($stat->daily_report_status === 'hold')
                                    <span class="text-xs font-semibold text-amber-700">Hold</span>
                                @elseif($stat->daily_report_status === 'rejected')
                                    <span class="text-xs font-semibold text-rose-700">Rejected</span>
                                @elseif($stat->daily_report_status === 'pending')
                                    <span class="text-xs font-semibold text-blue-700">Pending Review</span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum Tutup Kas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('supervisor.laporan', ['petugas_id' => $stat->teller->id, 'dari_tanggal' => $dariTanggal, 'sampai_tanggal' => $sampaiTanggal]) }}" class="inline-flex items-center gap-1 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-1 px-2.5 rounded-lg text-xs transition shadow-sm">
                                    <span>Log Transaksi</span>
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 italic">Belum ada data aktivitas teller pada periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>

