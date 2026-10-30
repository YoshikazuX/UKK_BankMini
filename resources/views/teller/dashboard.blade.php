<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Teller</title>
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

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <!-- Notifikasi Berhasil dengan Tombol Cetak Slip Otomatis -->
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-bold text-base">Transaksi Berhasil Tersimpan</p>
                    <p class="text-sm font-normal text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
            @if(session('slip_url'))
            <a href="{{ session('slip_url') }}" target="_blank" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs px-3.5 py-2 rounded-lg shadow-sm transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Slip {{ session('slip_jenis') ?? 'Transaksi' }}
            </a>
            @endif
        </div>
        @endif

        <!-- STATUS KAS FISIK LOKET & KAS AWAL HARI INI -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Kas Awal Hari Ini</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1 tabular-nums">Rp {{ number_format($kasAwal ?? 0, 0, ',', '.') }}</p>
                </div>
                <a href="{{ url('/teller/pengajuan-kas') }}" class="text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 px-3 py-1.5 rounded-lg transition shadow-sm">
                    Atur Kas
                </a>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <div class="flex items-center space-x-2">
                        <p class="text-xs font-semibold text-slate-500">Kas Fisik Loket Tersedia</p>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-100 text-rose-800' : (($kasTersedia ?? 0) < 100000 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                            {{ ($kasTersedia ?? 0) <= 0 ? 'Kas Habis' : (($kasTersedia ?? 0) < 100000 ? 'Menipis' : 'Aman') }}
                        </span>
                    </div>
                    <p class="text-2xl font-bold text-slate-900 mt-1 tabular-nums">Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}</p>
                </div>
                @if(($kasTersedia ?? 0) <= 0)
                    <span class="text-xs font-medium text-rose-700">Terkunci</span>
                @endif
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Permintaan Kas Baru</p>
                    <p class="text-xs text-slate-400 mt-1">Cegah kas loket minus saat antrean ramai</p>
                </div>
                <a href="{{ url('/teller/pengajuan-kas') }}" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium text-xs px-3 py-2 rounded-lg transition shadow-sm flex items-center">
                    Ajukan Kas
                </a>
            </div>
        </div>

        <!-- SECTION REKAPITULASI PRESENSI PETUGAS AVALIS HARI INI (TELLER VIEW) -->
        @if(isset($absensiSummary))
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-slate-900">Jadwal Tugas Petugas Avalis Hari Ini</h2>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                Jadwal Resmi
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar penugasan shift loket perbankan siswa tanggal {{ date('d F Y', strtotime($absensiSummary->tanggal)) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/teller/absensi-avalis') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 transition shadow-sm">
                        <span>Lihat Jadwal Presensi</span>
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

        <!-- HEADER & TOMBOL AKSI UTAMA POJOK KANAN ATAS -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Cari Nasabah</h1>
                <p class="text-slate-500 mt-1 text-sm">Ketikkan NIS atau Nama Siswa untuk mulai melayani transaksi.</p>
            </div>
            <div>
                <a href="{{ url('/teller/scan') }}" class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm transition inline-flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Scan Kartu / QR Nasabah
                </a>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6">
            <!-- Form Pencarian dengan Auto-focus -->
            <form action="{{ url('/teller/dashboard') }}" method="GET" class="flex items-center space-x-3">
                <div class="flex-grow">
                    <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Contoh: 17763 atau Dewi..." class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-slate-900 text-sm" autocomplete="off" autofocus>
                </div>
                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-5 rounded-lg transition shadow-sm text-sm">
                    Cari Nasabah
                </button>
                @if(request('cari'))
                    <a href="{{ url('/teller/dashboard') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-4 rounded-lg transition text-sm">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Kelas & Jurusan</th>
                            <th class="py-3 px-4">Saldo</th>
                            <th class="py-3 px-4 text-center">Aksi Kasir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($nasabah as $n)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-semibold text-slate-900 tabular-nums">{{ $n->nis }}</td>
                            <td class="py-3 px-4 font-medium text-slate-800">{{ $n->nama_siswa }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $n->kelas }} - {{ $n->jurusan }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900 tabular-nums">Rp {{ number_format($n->saldo, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ url('/teller/transaksi/'.$n->id) }}" class="inline-block bg-slate-900 hover:bg-slate-800 text-white font-medium py-1.5 px-3 rounded-lg transition text-xs shadow-sm">
                                        Transaksi &rarr;
                                    </a>
                                    <a href="{{ url('/buku-tabungan/'.$n->id) }}" class="inline-block bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-medium py-1.5 px-3 rounded-lg transition text-xs shadow-sm" title="Buku Tabungan / Rekening">
                                        Buku Tabungan
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400">
                                @if(request('cari'))
                                    Nasabah dengan kata kunci <strong class="text-rose-500">"{{ request('cari') }}"</strong> tidak ditemukan.
                                @else
                                    Ketik NIS atau Scan QR Code untuk menampilkan data nasabah.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Script JavaScript untuk Dropdown -->
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const btn = document.getElementById('profile-btn');
            const dropdown = document.getElementById('profile-dropdown');
            if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
