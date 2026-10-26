<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Supervisor E-Bank</title>
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
        <div class="flex flex-wrap justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Laporan Transaksi Hari Ini</h1>
                <p class="text-slate-600 text-sm mt-1">Daftar riwayat transaksi nasabah pada <strong>{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</strong>.</p>
            </div>
            <!-- Tombol Cetak LKM -->
            <a href="{{ url('/supervisor/cetak-harian') }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 py-2 px-4 rounded-lg text-sm font-medium shadow-sm transition inline-flex items-center">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan Harian
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-6 font-semibold text-xs">Waktu Transaksi</th>
                            <th class="py-3 px-6 font-semibold text-xs">Nama Nasabah & NIS</th>
                            <th class="py-3 px-6 font-semibold text-xs">Jenis Transaksi</th>
                            <th class="py-3 px-6 font-semibold text-xs">Nominal</th>
                            <th class="py-3 px-6 font-semibold text-xs text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 text-sm">
                        @forelse ($transaksi as $t)
                        <tr class="hover:bg-slate-50 border-b border-slate-100 transition">
                            <td class="py-4 px-6 text-slate-500 tabular-nums text-xs">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }} WIB</td>
                            <td class="py-4 px-6 font-medium text-slate-900">
                                {{ $t->nama_siswa }}
                                <div class="text-xs text-slate-500 font-normal mt-0.5 tabular-nums">{{ $t->nis }}</div>
                            </td>
                            <td class="py-4 px-6 capitalize text-xs font-semibold">{{ $t->jenis_transaksi }}</td>
                            <td class="py-4 px-6 font-semibold tabular-nums {{ $t->jenis_transaksi == 'setor' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $t->jenis_transaksi == 'setor' ? '+' : '-' }} Rp {{ number_format($t->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-center whitespace-nowrap space-x-1">
                                <a href="{{ url('/buku-tabungan/'.$t->nasabah_id) }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-1 px-2.5 rounded-lg text-xs transition shadow-sm">
                                    Buku Tabungan
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
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada transaksi hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
