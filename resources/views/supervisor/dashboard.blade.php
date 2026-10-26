<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Supervisor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-lg shadow-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Dashboard Pengawasan</h1>
            <p class="text-slate-600 text-sm mt-1">Pantau ringkasan saldo, ketersediaan kas loket, dan transaksi menunggu persetujuan.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-2">
                        <span>Total Nasabah</span>
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">{{ $totalNasabah }} <span class="text-sm font-normal text-slate-500">Siswa</span></p>
                </div>
                <p class="text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">Buku rekening tabungan aktif</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-2">
                        <span>Total Saldo Nasabah</span>
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</p>
                </div>
                <p class="text-xs text-slate-400 mt-3 pt-3 border-t border-slate-100">Akumulasi simpanan siswa</p>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-2">
                        <span>Kas Loket Hari Ini</span>
                        @if(($kasTersedia ?? 0) <= 0)
                            <span class="bg-rose-100 text-rose-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Habis</span>
                        @elseif(($kasTersedia ?? 0) < 500000)
                            <span class="bg-amber-100 text-amber-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Menipis</span>
                        @else
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Aman</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-600' : 'text-slate-900' }} tabular-nums">Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="text-xs text-slate-500 mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="tabular-nums">Kas Awal: Rp {{ number_format($kasAwal ?? 0, 0, ',', '.') }}</span>
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="text-slate-700 hover:text-slate-900 font-medium text-xs">Kelola &rarr;</a>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border {{ ($pendingPengajuanKasCount ?? 0) > 0 ? 'border-amber-300 ring-2 ring-amber-100' : 'border-slate-200' }} flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-medium mb-2">
                        <span>Pengajuan Kas</span>
                        @if(($pendingPengajuanKasCount ?? 0) > 0)
                            <span class="bg-amber-500 text-slate-900 text-[10px] font-semibold px-2 py-0.5 rounded-full">{{ $pendingPengajuanKasCount }} Menunggu</span>
                        @else
                            <span class="bg-slate-100 text-slate-600 text-[10px] font-medium px-2 py-0.5 rounded-full">0 Pending</span>
                        @endif
                    </div>
                    <p class="text-2xl font-bold text-slate-900 tabular-nums">{{ $pendingPengajuanKasCount ?? 0 }} <span class="text-sm font-normal text-slate-500">Permintaan</span></p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100">
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="inline-flex items-center text-xs font-medium text-amber-700 hover:text-amber-800">
                        Buka Permintaan Kas Teller
                        <svg class="w-3.5 h-3.5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h2 class="font-bold text-slate-900 text-sm">Menunggu Persetujuan (Pending)</h2>
            </div>
            <table class="min-w-full bg-white text-left">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Waktu</th>
                        <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Nasabah (NIS)</th>
                        <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Jenis Transaksi</th>
                        <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs">Nominal</th>
                        <th class="py-3 px-4 font-semibold border-b border-slate-200 text-xs text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 text-sm">
                    @forelse ($transaksi as $t)
                        @if($t->status == 'pending')
                        <tr class="hover:bg-slate-50 border-b border-slate-100">
                            <td class="py-3 px-4 tabular-nums text-slate-500 text-xs">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 font-medium text-slate-900">{{ $t->nama_siswa }} <span class="tabular-nums text-slate-500 text-xs font-normal">({{ $t->nis }})</span></td>
                            <td class="py-3 px-4 text-xs font-semibold capitalize">{{ $t->jenis_transaksi }}</td>
                            <td class="py-3 px-4 font-semibold tabular-nums text-slate-900">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ url('/supervisor/approve/'.$t->id) }}" onclick="return confirm('Setujui transaksi ini?')" class="inline-block bg-slate-900 hover:bg-slate-800 text-white font-medium py-1 px-3 rounded-lg text-xs transition shadow-sm">
                                    Setujui
                                </a>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada transaksi yang butuh persetujuan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
