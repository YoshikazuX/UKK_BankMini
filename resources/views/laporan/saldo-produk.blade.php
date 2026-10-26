<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saldo Simpanan Per Produk - Bank Mini</title>
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
                        <a href="{{ url('/laporan/saldo-produk') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Produk</a>
                        <a href="{{ url('/laporan/tahunan') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Tahunan</a>
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
        <!-- HEADER & CETAK -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center">
                    <svg class="w-7 h-7 mr-2 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Laporan Saldo Simpanan Per Produk
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Klasifikasi total simpanan dana pihak ketiga antara produk Tabungan Siswa dan Tabungan Guru/Karyawan.
                </p>
            </div>

            <div class="mt-4 md:mt-0">
                <a href="{{ url('/laporan/saldo-produk?export=print') }}" target="_blank"
                   class="bg-blue-800 hover:bg-blue-900 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Laporan Produk (Print)
                </a>
            </div>
        </div>

        <!-- 3 KARTU SUMMARY PRODUK -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-blue-950 text-white p-6 rounded-xl shadow-md border border-blue-900">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-blue-300 uppercase tracking-wider">Grand Total Saldo Simpanan</p>
                    <span class="p-2 bg-blue-800 text-blue-200 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-black text-white mt-2">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
                <p class="text-xs text-blue-300 mt-1">{{ count($siswaList) + count($guruList) }} Total Rekening Nasabah Terdaftar</p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Produk 1</span>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Tabungan Siswa</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ count($siswaList) }} Siswa</span>
                </div>
                <p class="text-2xl font-black text-emerald-700 mt-2">Rp {{ number_format($totalSiswaSaldo, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">
                    Porsi: {{ $grandTotal > 0 ? round(($totalSiswaSaldo / $grandTotal) * 100, 1) : 0 }}% dari total DPK
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="bg-purple-100 text-purple-800 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Produk 2</span>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mt-1">Tabungan Guru & Karyawan</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">{{ count($guruList) }} Nasabah</span>
                </div>
                <p class="text-2xl font-black text-purple-700 mt-2">Rp {{ number_format($totalGuruSaldo, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">
                    Porsi: {{ $grandTotal > 0 ? round(($totalGuruSaldo / $grandTotal) * 100, 1) : 0 }}% dari total DPK
                </p>
            </div>
        </div>

        <!-- 2 TABEL DAFTAR REKENING PER PRODUK -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- PRODUK TABUNGAN SISWA -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-emerald-50 border-b border-emerald-100 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-emerald-950 text-sm">Daftar Nasabah Tabungan Siswa</h2>
                        <p class="text-[11px] text-emerald-700">Total: {{ count($siswaList) }} Rekening Siswa</p>
                    </div>
                    <span class="font-black text-emerald-800 text-sm">Rp {{ number_format($totalSiswaSaldo, 0, ',', '.') }}</span>
                </div>

                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] sticky top-0 border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">No. Rekening</th>
                                <th class="p-2.5">Nama Siswa</th>
                                <th class="p-2.5">Kelas</th>
                                <th class="p-2.5 text-right">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($siswaList as $s)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 font-mono font-bold text-blue-900">{{ $s->no_rekening ?? $s->nis }}</td>
                                <td class="p-2.5 font-bold text-slate-900">{{ $s->nama_siswa }}</td>
                                <td class="p-2.5 text-slate-500">{{ $s->kelas }}</td>
                                <td class="p-2.5 text-right font-mono font-bold text-emerald-700">
                                    Rp {{ number_format($s->saldo, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">Belum ada nasabah kategori siswa.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PRODUK TABUNGAN GURU & KARYAWAN -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-purple-50 border-b border-purple-100 flex justify-between items-center">
                    <div>
                        <h2 class="font-bold text-purple-950 text-sm">Daftar Nasabah Tabungan Guru & Karyawan</h2>
                        <p class="text-[11px] text-purple-700">Total: {{ count($guruList) }} Rekening</p>
                    </div>
                    <span class="font-black text-purple-800 text-sm">Rp {{ number_format($totalGuruSaldo, 0, ',', '.') }}</span>
                </div>

                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-600 uppercase font-bold text-[10px] sticky top-0 border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">No. Rekening</th>
                                <th class="p-2.5">Nama Guru / Staf</th>
                                <th class="p-2.5">Unit / Jabatan</th>
                                <th class="p-2.5 text-right">Saldo (Rp)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($guruList as $g)
                            <tr class="hover:bg-slate-50">
                                <td class="p-2.5 font-mono font-bold text-purple-900">{{ $g->no_rekening ?? $g->nis }}</td>
                                <td class="p-2.5 font-bold text-slate-900">{{ $g->nama_siswa }}</td>
                                <td class="p-2.5 text-slate-500">{{ $g->kelas }}</td>
                                <td class="p-2.5 text-right font-mono font-bold text-purple-700">
                                    Rp {{ number_format($g->saldo, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-400">Belum ada nasabah kategori Guru / Karyawan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
