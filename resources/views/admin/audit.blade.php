<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Audit Sistem - Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div class="flex flex-wrap justify-between items-end mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Jejak Audit</h1>
                <p class="text-slate-600 text-sm mt-1">Pantau seluruh aktivitas transaksi. Gunakan filter spesifik untuk investigasi anomali.</p>
            </div>

            <a href="{{ url('/admin/audit/export?'.http_build_query(request()->all())) }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 py-2 px-4 rounded-lg text-sm font-medium shadow-sm transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor CSV
            </a>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-8">
            <form action="{{ url('/admin/audit') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="col-span-1 md:col-span-3 lg:col-span-1">
                        <label class="block text-slate-700 text-xs font-medium mb-2">Target Subjek</label>
                        <input type="text" name="target" value="{{ request('target') }}" placeholder="Ketik NIS atau nama teller..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-slate-700 text-xs font-medium mb-2">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-slate-700 text-xs font-medium mb-2">Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir" value="{{ request('tgl_akhir') }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="checkbox" name="luar_jam" value="1" {{ request('luar_jam') ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        <span class="text-sm font-medium text-rose-600 group-hover:text-rose-800 transition">Deteksi Transaksi di Luar Jam Operasional (06.00 - 15.00)</span>
                    </label>

                    <div class="space-x-2">
                        @if(request()->anyFilled(['target', 'tgl_mulai', 'tgl_akhir', 'luar_jam']))
                            <a href="{{ url('/admin/audit') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-5 rounded-lg text-sm transition shadow-sm">Reset</a>
                        @endif
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-6 rounded-lg shadow-sm text-sm transition">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left whitespace-nowrap">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Waktu Sistem</th>
                            <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Petugas</th>
                            <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Nasabah (NIS)</th>
                            <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Aktivitas</th>
                            <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Nominal</th>
                            <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 text-sm">
                        @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100 {{ $log->status == 'dibatalkan' ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3 px-4 text-slate-500 tabular-nums text-xs">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 px-4 font-medium text-slate-900">{{ $log->nama_petugas ?? 'Sistem' }}</td>
                            <td class="py-3 px-4">{{ $log->nama_siswa }} <span class="text-xs text-slate-500 tabular-nums">({{ $log->nis }})</span></td>
                            <td class="py-3 px-4 text-xs font-semibold capitalize">{{ $log->jenis_transaksi }}</td>
                            <td class="py-3 px-4 tabular-nums font-semibold text-slate-900">Rp {{ number_format($log->nominal, 0, ',', '.') }}</td>
                            <td class="py-3 px-4">
                                @if($log->status == 'dibatalkan')
                                    <span class="bg-rose-100 text-rose-700 font-semibold px-2 py-0.5 rounded text-xs">Void / Koreksi</span>
                                    <div class="text-[10px] text-rose-500 mt-1 truncate max-w-[150px]" title="{{ $log->alasan_batal }}">{{ $log->alasan_batal }}</div>
                                @else
                                    <span class="bg-emerald-100 text-emerald-700 font-semibold px-2 py-0.5 rounded text-xs capitalize">{{ $log->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500 italic">Tidak ada log aktivitas yang cocok dengan filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
