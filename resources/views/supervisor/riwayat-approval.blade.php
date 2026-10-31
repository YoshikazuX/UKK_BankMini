<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Persetujuan (Approval History) - Supervisor Bank Mini</title>
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
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Persetujuan & Jejak Otorisasi</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Catatan seluruh keputusan Maker-Checker Supervisor atas pengajuan kas dan daily report teller.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('supervisor.approval-center') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg text-xs shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Pusat Persetujuan
                </a>
            </div>
        </div>

        <!-- 4 KARTU METRIK KEPUTUSAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Keputusan Dicatat</span>
                <p class="text-2xl font-black text-slate-900 mt-0.5 tabular-nums">{{ $totalKeputusan }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Seluruh log audit keputusan</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Keputusan Disetujui</span>
                <p class="text-2xl font-black text-emerald-700 mt-0.5 tabular-nums">{{ $totalDisetujui }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Persetujuan droping & tutup kas</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Klarifikasi / Hold</span>
                <p class="text-2xl font-black text-amber-700 mt-0.5 tabular-nums">{{ $totalKlarifikasi }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Permintaan revisi selisih kas</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Keputusan Ditolak</span>
                <p class="text-2xl font-black text-rose-700 mt-0.5 tabular-nums">{{ $totalDitolak }}</p>
                <span class="text-[11px] text-slate-400 mt-1 block">Permohonan tidak memenuhi syarat</span>
            </div>
        </div>

        <!-- FORM FILTER RIWAYAT -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form action="{{ route('supervisor.riwayat-approval') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cari Referensi / Nama</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No. PK / REP / Nama..."
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Pekerjaan</label>
                    <select name="pekerjaan_type" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Jenis</option>
                        <option value="pengajuan_kas" {{ request('pekerjaan_type') === 'pengajuan_kas' ? 'selected' : '' }}>Droping Kas</option>
                        <option value="daily_report" {{ request('pekerjaan_type') === 'daily_report' ? 'selected' : '' }}>Daily Report</option>
                        <option value="transaksi" {{ request('pekerjaan_type') === 'transaksi' ? 'selected' : '' }}>Transaksi Loket</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Keputusan</label>
                    <select name="jenis_keputusan" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Keputusan</option>
                        <option value="disetujui" {{ request('jenis_keputusan') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('jenis_keputusan') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="klarifikasi_hold" {{ request('jenis_keputusan') === 'klarifikasi_hold' ? 'selected' : '' }}>Klarifikasi (Hold)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Teller Pemohon</label>
                    <select name="teller_id" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Teller</option>
                        @foreach($tellers as $t)
                            <option value="{{ $t->id }}" {{ (string) request('teller_id') === (string) $t->id ? 'selected' : '' }}>{{ $t->nama_petugas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Supervisor Pengambil</label>
                    <select name="supervisor_id" class="w-full text-xs border border-slate-300 rounded-lg px-2.5 py-2 focus:outline-none font-medium">
                        <option value="">Semua Supervisor</option>
                        @foreach($supervisors as $s)
                            <option value="{{ $s->id }}" {{ (string) request('supervisor_id') === (string) $s->id ? 'selected' : '' }}>{{ $s->nama_petugas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'pekerjaan_type', 'jenis_keputusan', 'teller_id', 'supervisor_id', 'dari_tanggal', 'sampai_tanggal']))
                    <a href="{{ route('supervisor.riwayat-approval') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-2 px-2.5 rounded-lg text-xs transition">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABEL AUDIT RIWAYAT KEPUTUSAN -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">
                    Menampilkan {{ $histories->total() }} catatan keputusan otorisasi
                </span>
                <span class="text-slate-500">Jejak audit permanen & timestamp nyata</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Waktu Keputusan</th>
                            <th class="py-3 px-4">Jenis & No. Ref</th>
                            <th class="py-3 px-4">Teller Pemohon</th>
                            <th class="py-3 px-4">Supervisor Penanggung Jawab</th>
                            <th class="py-3 px-4 text-center">Keputusan</th>
                            <th class="py-3 px-4 text-center">Perubahan Status</th>
                            <th class="py-3 px-4">Catatan / Alasan Penolakan</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($histories as $h)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 tabular-nums text-slate-500">
                                <span class="font-bold text-slate-900 block">{{ $h->created_at->format('d/m/Y') }}</span>
                                <span class="text-[10px] text-slate-400">{{ $h->created_at->format('H:i:s') }} WIB</span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-medium text-slate-700 block mb-0.5">
                                    {{ ucfirst(str_replace('_', ' ', $h->pekerjaan_type)) }}
                                </span>
                                <span class="font-mono font-bold text-slate-900 block tabular-nums">{{ $h->nomor_referensi }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $h->nama_teller }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">{{ $h->nama_supervisor }}</span>
                                <span class="text-[10px] text-slate-400">ID SPV: #{{ $h->supervisor_id }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-xs font-semibold {{ $h->jenis_keputusan === 'disetujui' ? 'text-emerald-700' : ($h->jenis_keputusan === 'ditolak' ? 'text-rose-700' : 'text-amber-700') }}">
                                    {{ ucfirst(str_replace('_', ' ', $h->jenis_keputusan)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-600">
                                <span class="text-slate-400">{{ $h->status_sebelum }}</span>
                                <span class="mx-1 font-bold text-slate-900">&rarr;</span>
                                <span class="font-bold text-slate-900">{{ $h->status_sesudah }}</span>
                            </td>
                            <td class="py-3 px-4 max-w-xs text-slate-600">
                                @if($h->catatan || $h->alasan_penolakan)
                                    <p class="truncate text-[11px] italic" title="{{ $h->catatan ?: $h->alasan_penolakan }}">
                                        "{{ $h->catatan ?: $h->alasan_penolakan }}"
                                    </p>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($h->pekerjaan_type === 'pengajuan_kas')
                                    <a href="{{ route('supervisor.pengajuan-kas.detail', $h->pekerjaan_id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs">
                                        Lihat Tiket
                                    </a>
                                @elseif($h->pekerjaan_type === 'daily_report')
                                    <a href="{{ route('supervisor.daily-report.detail', $h->pekerjaan_id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold text-xs">
                                        Lihat Dokumen
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400 italic">
                                Belum ada riwayat persetujuan yang cocok dengan kriteria filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINASI -->
            @if($histories->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $histories->links() }}
            </div>
            @endif
        </div>
    </main>
</body>
</html>

