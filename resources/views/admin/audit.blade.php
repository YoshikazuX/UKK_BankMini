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

    <!-- NAVBAR ADMIN KONSISTEN (DENGAN PROFIL) -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>
                <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                    <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Manajemen Nasabah</a>
                    <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Petugas Avalis</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                    <a href="{{ url('/admin/audit') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Jejak Audit</a>
                    <a href="{{ url('/admin/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                        <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                        Absensi
                    </a>
                    <a href="{{ url('/admin/konfigurasi') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Konfigurasi</a>

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
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">ADMINISTRATOR</span>
                            </div>
                            <a href="{{ url('/admin/profil') }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 flex items-center font-bold">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between items-end mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Jejak Audit (Audit Trail)</h1>
                <p class="text-slate-600 text-sm mt-1">Pantau seluruh aktivitas transaksi. Gunakan filter spesifik untuk investigasi anomali.</p>
            </div>

            <a href="{{ url('/admin/audit/export?'.http_build_query(request()->all())) }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-5 rounded text-sm font-bold shadow transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ekspor Data (CSV)
            </a>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200 mb-8">
            <form action="{{ url('/admin/audit') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="col-span-1 md:col-span-3 lg:col-span-1">
                        <label class="block text-slate-700 text-sm font-bold mb-2">1. Target Subjek (Prioritas)</label>
                        <input type="text" name="target" value="{{ request('target') }}" placeholder="Ketik NIS atau Nama Teller..." class="w-full px-4 py-2.5 border border-slate-300 rounded focus:ring-2 focus:ring-slate-500 focus:outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2">2. Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded focus:ring-2 focus:ring-slate-500 focus:outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-slate-700 text-sm font-bold mb-2">3. Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir" value="{{ request('tgl_akhir') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded focus:ring-2 focus:ring-slate-500 focus:outline-none text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="checkbox" name="luar_jam" value="1" {{ request('luar_jam') ? 'checked' : '' }} class="w-5 h-5 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        <span class="text-sm font-bold text-rose-600 group-hover:text-rose-800 transition">Deteksi Transaksi di Luar Jam Operasional (06.00 - 15.00)</span>
                    </label>

                    <div class="space-x-2">
                        @if(request()->anyFilled(['target', 'tgl_mulai', 'tgl_akhir', 'luar_jam']))
                            <a href="{{ url('/admin/audit') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 px-6 rounded text-sm transition">Reset</a>
                        @endif
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2 px-8 rounded shadow text-sm transition">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left whitespace-nowrap">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="py-3 px-4 font-bold border-b border-slate-200 text-xs uppercase tracking-wider">Waktu Sistem</th>
                            <th class="py-3 px-4 font-bold border-b border-slate-200 text-xs uppercase tracking-wider">Petugas</th>
                            <th class="py-3 px-4 font-bold border-b border-slate-200 text-xs uppercase tracking-wider">Nasabah (NIS)</th>
                            <th class="py-3 px-4 font-bold border-b border-slate-200 text-xs uppercase tracking-wider">Aktivitas</th>
                            <th class="py-3 px-4 font-bold border-b border-slate-200 text-xs uppercase tracking-wider">Nominal</th>
                            <th class="py-3 px-4 font-bold border-b border-slate-200 text-xs uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 text-sm">
                        @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100 {{ $log->status == 'dibatalkan' ? 'bg-rose-50/30' : '' }}">
                            <td class="py-3 px-4 text-slate-500 font-mono text-xs">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $log->nama_petugas ?? 'Sistem' }}</td>
                            <td class="py-3 px-4">{{ $log->nama_siswa }} <span class="text-xs text-slate-400 font-mono">({{ $log->nis }})</span></td>
                            <td class="py-3 px-4 font-bold uppercase text-xs">{{ $log->jenis_transaksi }}</td>
                            <td class="py-3 px-4 font-mono font-bold">Rp {{ number_format($log->nominal, 0, ',', '.') }}</td>
                            <td class="py-3 px-4">
                                @if($log->status == 'dibatalkan')
                                    <span class="bg-rose-100 text-rose-700 font-bold px-2 py-1 rounded text-xs uppercase">VOID / KOREKSI</span>
                                    <div class="text-[10px] text-rose-500 mt-1 truncate max-w-[150px]" title="{{ $log->alasan_batal }}">{{ $log->alasan_batal }}</div>
                                @else
                                    <span class="bg-emerald-100 text-emerald-700 font-bold px-2 py-1 rounded text-xs uppercase">{{ $log->status }}</span>
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

    <!-- SCRIPT UNTUK DROPDOWN PROFIL -->
    <script>
        function toggleDropdown() { document.getElementById('profile-dropdown').classList.toggle('hidden'); }
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
