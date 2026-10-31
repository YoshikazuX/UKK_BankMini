<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Persetujuan (Approval Center) - Supervisor Bank Mini</title>
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
        <!-- NOTIFIKASI FLASH -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- HEADER PUSAT PERSETUJUAN -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Persetujuan (Approval Center)</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Satu tempat terpusat untuk seluruh permohonan kas dan laporan tutup kas yang membutuhkan verifikasi atau pengesahan Supervisor.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('supervisor.riwayat-approval') }}" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Jejak Riwayat Otorisasi
                </a>
            </div>
        </div>

        <!-- 4 KARTU RINGKASAN STATUS PUSAT PERSETUJUAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pekerjaan Pending</span>
                    <p class="text-2xl font-black text-slate-900 mt-0.5 tabular-nums">{{ $totalPekerjaanPending }}</p>
                </div>
                <div class="w-10 h-10 rounded-full {{ $totalPekerjaanPending > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }} flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Pengajuan Kas Pending</span>
                    <p class="text-2xl font-black text-amber-700 mt-0.5 tabular-nums">{{ $totalPengajuanPending }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Daily Report Menunggu</span>
                    <p class="text-2xl font-black text-blue-700 mt-0.5 tabular-nums">{{ $totalDailyReportPending }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Telah Diselesaikan</span>
                    <p class="text-2xl font-black text-emerald-700 mt-0.5 tabular-nums">{{ $totalSelesai }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
        </div>

        <!-- TABS KATEGORI PEKERJAAN -->
        @php
            $tabList = [
                'semua' => 'Semua',
                'menunggu_persetujuan' => 'Menunggu Persetujuan',
                'menunggu_verifikasi' => 'Menunggu Verifikasi',
                'memerlukan_klarifikasi' => 'Memerlukan Klarifikasi',
                'telah_disetujui' => 'Telah Disetujui',
                'ditolak' => 'Ditolak',
            ];
            $currentTab = request('kategori', 'semua');
        @endphp
        <div class="flex items-center space-x-1 border-b border-slate-200 overflow-x-auto pb-px mb-6">
            @foreach($tabList as $key => $label)
                @php
                    $isActive = $currentTab === $key;
                    $url = request()->fullUrlWithQuery(['kategori' => $key, 'page' => 1]);
                @endphp
                <a href="{{ $url }}" class="whitespace-nowrap px-4 py-2.5 text-xs font-bold border-b-2 transition {{ $isActive ? 'border-indigo-600 text-indigo-700 bg-indigo-50/50' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    {{ $label }}
                    @if($key === 'menunggu_persetujuan' && $totalPengajuanPending > 0)
                        <span class="ml-1.5 px-1.5 py-0.2 rounded-full bg-amber-500 text-slate-900 text-[10px] font-black">{{ $totalPengajuanPending }}</span>
                    @elseif($key === 'menunggu_verifikasi' && $totalDailyReportPending > 0)
                        <span class="ml-1.5 px-1.5 py-0.2 rounded-full bg-blue-500 text-white text-[10px] font-black">{{ $totalDailyReportPending }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- FORM MULTI-FILTER PENCARIAN -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form action="{{ route('supervisor.approval-center') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <input type="hidden" name="kategori" value="{{ $currentTab }}">

                <!-- 1. Search Nomor Referensi / Nama Teller -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cari Referensi / Petugas</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="No. PK / REP / Nama..."
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                </div>

                <!-- 2. Filter Jenis Pekerjaan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Pekerjaan</label>
                    <select name="jenis" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 font-medium">
                        <option value="semua">Semua Jenis Pekerjaan</option>
                        <option value="pengajuan_kas" {{ request('jenis') === 'pengajuan_kas' ? 'selected' : '' }}>Droping Kas Loket</option>
                        <option value="daily_report" {{ request('jenis') === 'daily_report' ? 'selected' : '' }}>Daily Report Tutup Kas</option>
                    </select>
                </div>

                <!-- 3. Filter Teller -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Petugas Teller</label>
                    <select name="teller_id" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 font-medium">
                        <option value="">Semua Teller</option>
                        @foreach($tellers as $t)
                            <option value="{{ $t->id }}" {{ (string) request('teller_id') === (string) $t->id ? 'selected' : '' }}>
                                {{ $t->nama_petugas }} ({{ $t->kelas ?? 'Loket' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Rentang Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                    <input type="date" name="dari_tanggal" value="{{ request('dari_tanggal') }}"
                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                </div>

                <!-- 5. Tombol Aksi Filter -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-3 rounded-lg text-xs transition flex items-center justify-center gap-1 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'jenis', 'teller_id', 'dari_tanggal', 'sampai_tanggal']))
                    <a href="{{ route('supervisor.approval-center', ['kategori' => $currentTab]) }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-2 px-3 rounded-lg text-xs transition">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABEL DAFTAR PEKERJAAN TERPADU -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-800">
                    Menampilkan {{ $pekerjaanList->total() }} pekerjaan yang sesuai
                </span>
                <span class="text-slate-500">Urutan: Pekerjaan terbaru di atas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Jenis & No. Referensi</th>
                            <th class="py-3 px-4">Petugas Pemohon</th>
                            <th class="py-3 px-4">Waktu Pengajuan</th>
                            <th class="py-3 px-4 text-right">Nominal / Kas Fisik</th>
                            <th class="py-3 px-4">Alasan / Catatan Permohonan</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi Pemeriksaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($pekerjaanList as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4">
                                <span class="text-xs font-semibold uppercase {{ $item->pekerjaan_type === 'pengajuan_kas' ? 'text-amber-700' : 'text-blue-700' }} block mb-0.5">
                                    {{ $item->pekerjaan_label }}
                                </span>
                                <span class="font-bold text-slate-900 block tabular-nums">{{ $item->nomor_referensi }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $item->nama_teller }}
                                <span class="block text-[10px] font-normal text-slate-400">Kelas: {{ $item->kelas_teller }}</span>
                            </td>
                            <td class="py-3 px-4 tabular-nums text-slate-500">
                                {{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}<br>
                                <span class="text-[10px] text-slate-400">{{ $item->created_at ? $item->created_at->format('H:i') : '' }} WIB</span>
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-slate-900 tabular-nums">
                                Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                @if(isset($item->selisih))
                                    <span class="block text-[10px] font-semibold {{ $item->selisih == 0 ? 'text-emerald-600' : ($item->selisih > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                        Selisih: {{ $item->selisih >= 0 ? '+' : '' }}Rp {{ number_format($item->selisih, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 max-w-xs text-slate-600">
                                <p class="truncate" title="{{ $item->keterangan }}">{{ $item->keterangan ?: '-' }}</p>
                                @if($item->catatan_spv)
                                    <p class="text-[10px] text-indigo-700 italic mt-0.5 truncate" title="Catatan SPV: {{ $item->catatan_spv }}">
                                        SPV: "{{ $item->catatan_spv }}"
                                    </p>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="text-xs font-semibold {{ $item->status_asli === 'disetujui' || $item->status_asli === 'approved' ? 'text-emerald-700' : ($item->status_asli === 'ditolak' || $item->status_asli === 'rejected' ? 'text-rose-700' : 'text-amber-700') }}">
                                    {{ $item->status_text }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ $item->detail_url }}" class="inline-flex items-center gap-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-1.5 px-3 rounded-lg text-xs transition shadow-sm">
                                    <span>Periksa</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="font-medium text-slate-600">Tidak ada pekerjaan yang ditemukan pada kategori atau filter ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Gunakan tab filter lain atau klik reset pencarian untuk menampilkan data.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINASI -->
            @if($pekerjaanList->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $pekerjaanList->links() }}
            </div>
            @endif
        </div>
    </main>
</body>
</html>

