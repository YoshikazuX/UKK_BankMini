<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Tahunan Transaksi & Kas - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR ADMIN KONSISTEN -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>

                @if(Auth::user()->role === 'supervisor')
                    <div class="flex items-center space-x-2.5 lg:space-x-3.5 xl:space-x-5 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/supervisor/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/supervisor/laporan') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Laporan Transaksi</a>
                        <a href="{{ url('/supervisor/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Daily Report</a>
                        <a href="{{ url('/supervisor/pengajuan-kas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Pengajuan Kas</a>
                        <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                        <a href="{{ url('/laporan/saldo-produk') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Produk</a>
                        <a href="{{ url('/laporan/tahunan') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Tahunan</a>
                        <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Data Teller</a>
                        <a href="{{ url('/supervisor/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Absensi</a>
                @else
                    <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Manajemen Nasabah</a>
                        <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Petugas Avalis</a>
                        <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                        <a href="{{ url('/admin/audit') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jejak Audit</a>
                        <a href="{{ url('/admin/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                            <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                            Absensi
                        </a>
                        <a href="{{ url('/admin/konfigurasi') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Konfigurasi</a>
                @endif

                    <!-- PROFIL DROPDOWN -->
                    <div class="relative ml-2 shrink-0">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600 whitespace-nowrap">
                            @if(Auth::user()->foto)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline whitespace-nowrap">{{ Auth::user()->nama_petugas }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">{{ strtoupper(Auth::user()->role) }}</span>
                            </div>
                            <a href="{{ url(Auth::user()->role === 'supervisor' ? '/supervisor/profil' : (Auth::user()->role === 'teller' ? '/teller/profil' : '/admin/profil')) }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 flex items-center font-bold">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- HEADER & PILIHAN TAHUN -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                    <svg class="w-7 h-7 mr-2 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Laporan Rekapitulasi Tahunan Bank Mini (Tahun {{ $tahun }})
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Rekapitulasi total mutasi uang masuk, uang keluar, pendapatan administrasi buku, dan volume transaksi per bulan.
                </p>
            </div>

            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <!-- FORM FILTER TAHUN -->
                <form action="{{ url('/laporan/tahunan') }}" method="GET" class="flex items-center space-x-2">
                    <select name="tahun" onchange="this.form.submit()" class="text-xs font-bold border border-slate-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @for($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endfor
                    </select>
                </form>

                <a href="{{ url('/laporan/tahunan?tahun='.$tahun.'&export=print') }}" target="_blank"
                   class="bg-blue-800 hover:bg-blue-900 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Laporan Tahunan
                </a>
            </div>
        </div>

        @php
            $totSetorTahun = $rekapBulanan->sum('total_setor');
            $totTarikTahun = $rekapBulanan->sum('total_tarik');
            $totAdminTahun = $rekapBulanan->sum('total_admin');
            $totJmlTahun   = $rekapBulanan->sum('jumlah_transaksi');
            $netKasTahun   = $totSetorTahun - $totTarikTahun;
        @endphp

        <!-- 4 KARTU REKAP TAHUNAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Setoran 1 Tahun</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">Rp {{ number_format($totSetorTahun, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Akumulasi uang tunai disetor</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Penarikan 1 Tahun</p>
                <p class="text-2xl font-black text-rose-600 mt-1">Rp {{ number_format($totTarikTahun, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Akumulasi uang tunai ditarik</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pendapatan Biaya Admin</p>
                <p class="text-2xl font-black text-amber-600 mt-1">Rp {{ number_format($totAdminTahun, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Biaya admin buku tabungan</p>
            </div>

            <div class="bg-blue-900 text-white p-5 rounded-xl shadow-md border border-blue-800">
                <p class="text-xs font-bold text-blue-200 uppercase tracking-wider">Net Arus Kas (Setor - Tarik)</p>
                <p class="text-2xl font-black text-white mt-1">
                    {{ $netKasTahun >= 0 ? '+' : '' }}Rp {{ number_format($netKasTahun, 0, ',', '.') }}
                </p>
                <p class="text-xs text-blue-300 mt-1">{{ number_format($totJmlTahun, 0, ',', '.') }} Total Frekuensi Transaksi</p>
            </div>
        </div>

        <!-- TABEL REKAPITULASI BULAN KE BULAN -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="font-bold text-slate-800 text-sm">Rincian Perkembangan Kas & Transaksi Tiap Bulan</h2>
                <span class="text-xs font-mono font-bold text-slate-500">Tahun Buku {{ $tahun }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold text-[11px] border-b border-slate-200">
                        <tr>
                            <th class="p-3 w-12 text-center">Bulan</th>
                            <th class="p-3">Nama Bulan</th>
                            <th class="p-3 text-right">Total Setor (Rp)</th>
                            <th class="p-3 text-right">Total Tarik (Rp)</th>
                            <th class="p-3 text-right">Biaya Admin (Rp)</th>
                            <th class="p-3 text-right">Net Kas Masuk/Keluar (Rp)</th>
                            <th class="p-3 text-center">Volume Trx</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @for($m = 1; $m <= 12; $m++)
                            @php
                                $row = $rekapBulanan->firstWhere('bulan', $m);
                                $setor = $row ? $row->total_setor : 0;
                                $tarik = $row ? $row->total_tarik : 0;
                                $adm   = $row ? $row->total_admin : 0;
                                $cnt   = $row ? $row->jumlah_transaksi : 0;
                                $net   = $setor - $tarik;
                            @endphp
                            <tr class="hover:bg-slate-50 {{ $m == date('n') && $tahun == date('Y') ? 'bg-blue-50/40 font-medium' : '' }}">
                                <td class="p-3 text-center font-mono text-slate-500">{{ $m }}</td>
                                <td class="p-3 font-bold text-slate-900">
                                    {{ $namaBulan[$m] }}
                                    @if($m == date('n') && $tahun == date('Y'))
                                        <span class="ml-1 text-[10px] font-bold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded">Bulan Berjalan</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-emerald-700">
                                    {{ $setor > 0 ? 'Rp ' . number_format($setor, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-rose-700">
                                    {{ $tarik > 0 ? 'Rp ' . number_format($tarik, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono font-bold text-amber-700">
                                    {{ $adm > 0 ? 'Rp ' . number_format($adm, 0, ',', '.') : '-' }}
                                </td>
                                <td class="p-3 text-right font-mono font-black {{ $net >= 0 ? 'text-slate-800' : 'text-rose-600' }}">
                                    {{ ($setor > 0 || $tarik > 0) ? (($net >= 0 ? '+' : '') . 'Rp ' . number_format($net, 0, ',', '.')) : '-' }}
                                </td>
                                <td class="p-3 text-center font-mono text-slate-600">
                                    {{ $cnt > 0 ? number_format($cnt, 0, ',', '.') . ' Trx' : '-' }}
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot class="bg-slate-100 font-black text-slate-900 border-t-2 border-slate-300">
                        <tr>
                            <td colspan="2" class="p-3 text-right uppercase tracking-wider text-xs">Total Setahun:</td>
                            <td class="p-3 text-right font-mono text-emerald-700 text-sm">Rp {{ number_format($totSetorTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-rose-700 text-sm">Rp {{ number_format($totTarikTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-amber-700 text-sm">Rp {{ number_format($totAdminTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-blue-900 text-sm">{{ $netKasTahun >= 0 ? '+' : '' }}Rp {{ number_format($netKasTahun, 0, ',', '.') }}</td>
                            <td class="p-3 text-center font-mono text-slate-800">{{ number_format($totJmlTahun, 0, ',', '.') }} Trx</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>

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
