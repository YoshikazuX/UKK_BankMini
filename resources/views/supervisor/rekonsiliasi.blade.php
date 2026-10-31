<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekonsiliasi Kas - Supervisor Bank Mini</title>
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
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Rekonsiliasi Kas Sistem vs Kas Fisik</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        Audit Keseimbangan Kas
                    </span>
                </div>
                <p class="text-slate-500 text-sm mt-1">
                    Membandingkan kas transaksi sistem komputer dengan kas fisik uang tunai di laci loket teller hasil cash opname.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('supervisor.daily-report') }}" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Pemeriksaan Dokumen
                </a>
            </div>
        </div>

        <!-- 4 KARTU METRIK REKONSILIASI KAS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Laporan Direview</span>
                <p class="text-2xl font-black text-slate-900 mt-0.5 tabular-nums">{{ $totalLaporan }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Dokumen tutup kas teller</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Kas Seimbang (Sesuai)</span>
                <p class="text-2xl font-black text-emerald-700 mt-0.5 tabular-nums">{{ $totalSesuai }}</p>
                <span class="text-[11px] text-emerald-600 mt-1 block">Selisih tepat Rp 0</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Surplus (Lebih Fisik)</span>
                <p class="text-2xl font-black text-blue-700 mt-0.5 tabular-nums">{{ $totalSurplus }}</p>
                <span class="text-[11px] text-blue-600 mt-1 block">Kas fisik > kas sistem</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Defisit (Kurang Fisik)</span>
                <p class="text-2xl font-black text-rose-700 mt-0.5 tabular-nums">{{ $totalDefisit }}</p>
                <span class="text-[11px] text-rose-600 mt-1 block">Kas fisik < kas sistem</span>
            </div>
        </div>

        <!-- FORM FILTER REKONSILIASI -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form action="{{ route('supervisor.rekonsiliasi') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai_tanggal" value="{{ request('sampai_tanggal') }}"
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Teller</label>
                    <select name="teller_id" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Teller</option>
                        @foreach($tellers as $t)
                            <option value="{{ $t->id }}" {{ (string) request('teller_id') === (string) $t->id ? 'selected' : '' }}>{{ $t->nama_petugas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kondisi Selisih</label>
                    <select name="kondisi_selisih" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Kondisi</option>
                        <option value="sesuai" {{ request('kondisi_selisih') === 'sesuai' ? 'selected' : '' }}>Seimbang (Rp 0)</option>
                        <option value="surplus" {{ request('kondisi_selisih') === 'surplus' ? 'selected' : '' }}>Surplus (Lebih Fisik)</option>
                        <option value="defisit" {{ request('kondisi_selisih') === 'defisit' ? 'selected' : '' }}>Defisit (Kurang Fisik)</option>
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['dari_tanggal', 'sampai_tanggal', 'teller_id', 'kondisi_selisih']))
                    <a href="{{ route('supervisor.rekonsiliasi') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-2 px-2.5 rounded-lg text-xs transition">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABEL REKONSILIASI KAS -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">
                    Menampilkan {{ $reports->total() }} laporan rekonsiliasi kas
                </span>
                <span class="text-slate-500">Formula: Selisih = Kas Fisik Aktual - Kas Sistem</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Dokumen / Tanggal</th>
                            <th class="py-3 px-4">Petugas Teller</th>
                            <th class="py-3 px-4 text-right">Saldo Awal</th>
                            <th class="py-3 px-4 text-right">Setoran (+)</th>
                            <th class="py-3 px-4 text-right">Droping (+)</th>
                            <th class="py-3 px-4 text-right">Penarikan (-)</th>
                            <th class="py-3 px-4 text-right">Kas Sistem</th>
                            <th class="py-3 px-4 text-right">Kas Fisik</th>
                            <th class="py-3 px-4 text-right">Selisih Kas</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($reports as $r)
                        @php
                            $diff = $r->difference;
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 tabular-nums">
                                <span class="font-bold text-slate-900 block">#REP-{{ str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-slate-400 text-[10px]">{{ $r->report_date ? $r->report_date->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $r->user->nama_petugas ?? 'Teller' }}
                                <span class="block text-[10px] font-normal text-slate-400">Kelas: {{ $r->user->kelas ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 text-right tabular-nums text-slate-600">Rp {{ number_format($r->opening_balance, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-emerald-700 tabular-nums font-medium">Rp {{ number_format($r->total_deposit, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-indigo-700 tabular-nums font-medium">Rp {{ number_format($r->total_cash_drop, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right text-rose-700 tabular-nums font-medium">Rp {{ number_format($r->total_withdrawal, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900 tabular-nums">Rp {{ number_format($r->system_balance, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-black text-slate-900 tabular-nums">Rp {{ number_format($r->physical_balance, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-black tabular-nums {{ $diff == 0 ? 'text-emerald-600' : ($diff > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                {{ $diff >= 0 ? '+' : '' }}Rp {{ number_format($diff, 0, ',', '.') }}
                                <span class="block text-[9px] font-bold uppercase">
                                    {{ $diff == 0 ? 'Seimbang' : ($diff > 0 ? 'Surplus' : 'Defisit') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $r->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : ($r->status === 'rejected' ? 'bg-rose-100 text-rose-800' : ($r->status === 'hold' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('supervisor.daily-report.detail', $r->id) }}" class="inline-block bg-slate-900 hover:bg-slate-800 text-white font-semibold py-1 px-2.5 rounded-lg text-xs transition shadow-sm">
                                    Periksa
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-10 text-center text-slate-400 italic">
                                Tidak ada data rekonsiliasi kas yang cocok dengan kriteria filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINASI -->
            @if($reports->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $reports->links() }}
            </div>
            @endif
        </div>
    </main>
</body>
</html>

