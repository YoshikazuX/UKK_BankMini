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

        <!-- SECTION REKAPITULASI PRESENSI PETUGAS AVALIS HARI INI (SUPERVISOR MONITORING) -->
        @if(isset($absensiSummary))
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-800 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">Pemantauan Presensi Petugas Avalis Hari Ini</h2>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                Mode Pengawasan
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Jadwal shift dan status kehadiran teller operasional tanggal {{ date('d F Y', strtotime($absensiSummary->tanggal)) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/supervisor/absensi-avalis') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 transition shadow-sm">
                        <span>Pantau Absensi Lengkap</span>
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <!-- Mini Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-4">
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Petugas</span>
                    <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $absensiSummary->totalPetugas }}</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hadir</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <p class="text-xl font-bold text-emerald-700 mt-0.5">{{ $absensiSummary->totalHadir }}</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Izin</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <p class="text-xl font-bold text-amber-700 mt-0.5">{{ $absensiSummary->totalIzin }}</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tidak Masuk</span>
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    </div>
                    <p class="text-xl font-bold text-rose-700 mt-0.5">{{ $absensiSummary->totalTidakMasuk }}</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Shift Pagi</span>
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    </div>
                    <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $absensiSummary->totalShiftPagi }}</p>
                </div>
                <div class="bg-slate-50 rounded-lg p-3 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Shift Siang</span>
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    </div>
                    <p class="text-xl font-bold text-slate-900 mt-0.5">{{ $absensiSummary->totalShiftSiang }}</p>
                </div>
            </div>

            <!-- List Petugas Jaga Hari Ini -->
            @if(count($absensiSummary->daftarPetugas) > 0)
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Nama Petugas</th>
                            <th class="py-2.5 px-3">Kelas</th>
                            <th class="py-2.5 px-3">Shift Jaga</th>
                            <th class="py-2.5 px-3">Status Kehadiran</th>
                            <th class="py-2.5 px-3">Pos Tugas / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($absensiSummary->daftarPetugas as $petugas)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-2.5 px-3">
                                <div class="flex items-center space-x-2.5">
                                    @php
                                        $petugasFotoUrl = $petugas->foto_url;
                                        $initial = strtoupper(mb_substr(trim($petugas->nama), 0, 1));
                                    @endphp
                                    @if($petugasFotoUrl)
                                        <img src="{{ $petugasFotoUrl }}" alt="Foto {{ $petugas->nama }}" class="w-7 h-7 rounded-full object-cover border border-slate-200 shadow-sm shrink-0">
                                    @else
                                        <div class="w-7 h-7 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-[11px] shrink-0 shadow-sm">
                                            {{ $initial }}
                                        </div>
                                    @endif
                                    <span class="font-semibold text-slate-900">{{ $petugas->nama }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">{{ $petugas->kelas }}</td>
                            <td class="py-2.5 px-3">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full {{ str_contains($petugas->shift_jaga, 'Pagi') ? 'bg-sky-500' : 'bg-indigo-500' }}"></span>
                                    {{ str_contains($petugas->shift_jaga, 'Pagi') ? 'Shift Pagi (07.30 - 11.30)' : 'Shift Siang (11.30 - 15.30)' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                @if($petugas->status === 'Hadir')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hadir
                                    </span>
                                @elseif($petugas->status === 'Izin')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Izin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Tidak Masuk
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-500">{{ $petugas->keterangan ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

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
