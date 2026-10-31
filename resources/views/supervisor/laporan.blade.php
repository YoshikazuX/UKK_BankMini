<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Operasional - Supervisor Bank Mini</title>
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Laporan Transaksi Operasional</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                        Buku Jurnal Kas
                    </span>
                </div>
                <p class="text-slate-500 text-sm mt-1">
                    Seluruh riwayat mutasi setoran dan penarikan nasabah yang diproses loket teller dengan multi-filter lengkap.
                </p>
            </div>
            
            <a href="{{ url('/supervisor/cetak-harian') }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 py-2 px-4 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Lembar Harian
            </a>
        </div>

        <!-- 3 KARTU METRIK HASIL FILTER -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Transaksi Ditemukan</span>
                <p class="text-2xl font-black text-slate-900 mt-0.5 tabular-nums">{{ $totalTransaksiCount }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Sesuai kriteria pencarian</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Akumulasi Setoran Masuk</span>
                <p class="text-2xl font-black text-emerald-700 mt-0.5 tabular-nums">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</p>
                <span class="text-[11px] text-emerald-600 mt-1 block">Dana nasabah masuk kas</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Akumulasi Penarikan Keluar</span>
                <p class="text-2xl font-black text-rose-700 mt-0.5 tabular-nums">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</p>
                <span class="text-[11px] text-rose-600 mt-1 block">Dana nasabah dicairkan</span>
            </div>
        </div>

        <!-- MULTI-FILTER FORM (FITUR 7) -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form action="{{ route('supervisor.laporan') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cari Nasabah / NIS / ID</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIS / No. Rek..."
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                </div>

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
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Petugas Teller</label>
                    <select name="petugas_id" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Teller</option>
                        @foreach($tellers as $t)
                            <option value="{{ $t->id }}" {{ (string) request('petugas_id') === (string) $t->id ? 'selected' : '' }}>{{ $t->nama_petugas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Transaksi</label>
                    <select name="jenis_transaksi" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Jenis</option>
                        <option value="setor" {{ request('jenis_transaksi') === 'setor' ? 'selected' : '' }}>Setoran</option>
                        <option value="tarik" {{ request('jenis_transaksi') === 'tarik' ? 'selected' : '' }}>Penarikan</option>
                        <option value="admin" {{ request('jenis_transaksi') === 'admin' ? 'selected' : '' }}>Biaya Admin</option>
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'dari_tanggal', 'sampai_tanggal', 'petugas_id', 'jenis_transaksi', 'status']))
                    <a href="{{ route('supervisor.laporan') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-2 px-2.5 rounded-lg text-xs transition">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABEL LAPORAN TRANSAKSI -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">
                    Menampilkan {{ $transaksi->total() }} baris data transaksi
                </span>
                <span class="text-slate-500">Urutan: Waktu transaksi terbaru di atas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Waktu Transaksi</th>
                            <th class="py-3 px-4">Nama Nasabah & No. Rek</th>
                            <th class="py-3 px-4">Jenis & Kode</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4">Petugas Loket</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi Dokumen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($transaksi as $t)
                        <tr class="hover:bg-slate-50 transition {{ $t->status === 'dibatalkan' ? 'bg-rose-50/40 text-slate-400' : '' }}">
                            <td class="py-3 px-4 text-slate-500 tabular-nums">
                                <span class="font-bold text-slate-900 block">{{ $t->created_at->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-slate-400">{{ $t->created_at->format('H:i:s') }} WIB</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">{{ $t->nama_siswa ?? $t->nasabah?->nama_siswa }}</span>
                                <span class="text-[10px] text-slate-400 tabular-nums">NIS: {{ $t->nis ?? $t->nasabah?->nis }} &bull; Rek: {{ $t->nasabah?->no_rekening ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="capitalize font-semibold text-slate-800 block">{{ str_replace('_', ' ', $t->jenis_transaksi) }}</span>
                                @if($t->kode_sandi)
                                    <span class="text-[10px] text-slate-400 font-mono">Sandi: {{ $t->kode_sandi }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-black tabular-nums text-sm {{ $t->status === 'dibatalkan' ? 'line-through text-slate-400' : ($t->jenis_transaksi == 'setor' ? 'text-emerald-700' : 'text-rose-700') }}">
                                {{ $t->jenis_transaksi == 'setor' ? '+' : '-' }} Rp {{ number_format($t->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $t->petugas?->nama_petugas ?? 'Loket' }}</span>
                                <span class="text-[10px] text-slate-400">ID: #{{ $t->petugas_id }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($t->status === 'dibatalkan')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Dibatalkan</span>
                                @elseif($t->status === 'sukses')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Sukses</span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Pending</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap space-x-1">
                                <a href="{{ url('/buku-tabungan/'.$t->nasabah_id) }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-1 px-2.5 rounded-lg text-xs transition shadow-sm">
                                    Buku
                                </a>
                                @if($t->jenis_transaksi === 'setor')
                                    <a href="{{ url('/teller/slip-setor/'.$t->id) }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-1 px-2 rounded-lg text-xs transition shadow-sm">
                                        Slip
                                    </a>
                                @else
                                    <a href="{{ url('/teller/slip-tarik/'.$t->id) }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-1 px-2 rounded-lg text-xs transition shadow-sm">
                                        Slip
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 italic">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Tidak ada data transaksi yang sesuai dengan parameter filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINASI -->
            @if($transaksi->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $transaksi->links() }}
            </div>
            @endif
        </div>
    </main>
</body>
</html>
