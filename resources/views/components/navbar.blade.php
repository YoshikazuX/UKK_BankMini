@php
    $user = Auth::user();
    $role = $user->role ?? 'admin';
    // Style konsisten seluruh tautan navigasi website: font-size 14px (text-sm), tinggi baris seragam
    $navItemClass = function(bool $isActive) {
        $base = "text-sm whitespace-nowrap px-3 py-1.5 rounded-lg transition-all duration-150 focus:outline-none";
        return $isActive 
            ? "{$base} text-white bg-slate-700 font-semibold shadow-inner" 
            : "{$base} text-slate-300 hover:text-white hover:bg-slate-700/50 font-medium";
    };
@endphp

<!-- Font Plus Jakarta Sans terstandarisasi untuk navigasi website -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<nav class="bg-slate-800 text-white shadow-md relative no-print z-40" style="font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Brand Logo Resmi SMK Negeri 1 Bantul + Nama Institusi -->
            <a href="{{ url('/' . ($role === 'teller' ? 'teller/dashboard' : ($role === 'supervisor' ? 'supervisor/dashboard' : ($role === 'nasabah' ? 'nasabah/dashboard' : 'admin/dashboard')))) }}" class="flex items-center space-x-3 whitespace-nowrap shrink-0 mr-4 focus:outline-none group">
                <img src="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}" alt="Logo SMK Negeri 1 Bantul" class="w-9 h-9 object-contain shrink-0 rounded transition-transform group-hover:scale-105">
                <div class="flex flex-col">
                    <span class="text-sm sm:text-base font-bold tracking-tight text-white leading-tight">LKM Mitra Siswa Abadi</span>
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-wider uppercase">SMK Negeri 1 Bantul</span>
                </div>
            </a>

            <!-- Menu links according to role (Seluruh tautan menggunakan ukuran text-sm dan padding seragam) -->
            @if($role === 'teller')
                <div class="flex items-center space-x-1 sm:space-x-1.5 lg:space-x-2 overflow-x-auto py-1">
                    <a href="{{ url('/teller/dashboard') }}" class="{{ $navItemClass(request()->is('teller/dashboard*')) }}">Dashboard</a>
                    <a href="{{ url('/teller/riwayat') }}" class="{{ $navItemClass(request()->is('teller/riwayat*')) }}">Riwayat Saya</a>
                    <a href="{{ url('/teller/daily-report') }}" class="{{ $navItemClass(request()->is('teller/daily-report*')) }}">Tutup Kas</a>
                    <a href="{{ url('/teller/pengajuan-kas') }}" class="{{ $navItemClass(request()->is('teller/pengajuan-kas*')) }} flex items-center">
                        Pengajuan Kas
                        @if(($pendingPengajuan ?? 0) > 0)
                            <span class="ml-1.5 bg-amber-500 text-slate-900 text-[10px] font-black px-1.5 py-0.5 rounded-full">{{ $pendingPengajuan }}</span>
                        @endif
                    </a>
                    <a href="{{ url('/teller/absensi-avalis') }}" class="{{ $navItemClass(request()->is('teller/absensi-avalis*') || request()->is('absensi-avalis*')) }}">Absensi Avalis</a>
            @elseif($role === 'supervisor')
                <div class="flex items-center space-x-1 sm:space-x-1.5 lg:space-x-2 overflow-x-auto py-1">
                    <a href="{{ url('/supervisor/dashboard') }}" class="{{ $navItemClass(request()->is('supervisor/dashboard*')) }}">Dashboard</a>
                    <a href="{{ url('/supervisor/laporan') }}" class="{{ $navItemClass(request()->is('supervisor/laporan*')) }}">Laporan</a>
                    <a href="{{ url('/supervisor/daily-report') }}" class="{{ $navItemClass(request()->is('supervisor/daily-report*')) }}">Daily Report</a>
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="{{ $navItemClass(request()->is('supervisor/pengajuan-kas*')) }}">Pengajuan Kas</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="{{ $navItemClass(request()->is('laporan/jurnal*')) }}">Jurnal</a>
                    <a href="{{ url('/laporan/saldo-produk') }}" class="{{ $navItemClass(request()->is('laporan/saldo-produk*')) }}">Produk</a>
                    <a href="{{ url('/laporan/tahunan') }}" class="{{ $navItemClass(request()->is('laporan/tahunan*')) }}">Tahunan</a>
                    <a href="{{ url('/avalis/petugas') }}" class="{{ $navItemClass(request()->is('avalis/petugas*')) }}">Data Teller</a>
                    <a href="{{ url('/supervisor/absensi-avalis') }}" class="{{ $navItemClass(request()->is('supervisor/absensi-avalis*') || request()->is('absensi-avalis*')) }}">Absensi</a>
            @elseif($role === 'nasabah')
                @php
                    $navNasabah = \Illuminate\Support\Facades\DB::table('nasabah')->where('username', $user->username ?? '')->orWhere('nis', $user->username ?? '')->first() ?? \Illuminate\Support\Facades\DB::table('nasabah')->first();
                    $navNasabahId = $navNasabah->id ?? 1;
                @endphp
                <div class="flex items-center space-x-1 sm:space-x-1.5 lg:space-x-2 overflow-x-auto py-1">
                    <a href="{{ url('/nasabah/dashboard') }}" class="{{ $navItemClass(request()->is('nasabah/dashboard*')) }}">Dashboard</a>
                    <a href="{{ url('/buku-tabungan/'.$navNasabahId) }}" class="{{ $navItemClass(request()->is('buku-tabungan*')) }}">Buku Tabungan</a>
            @else
                {{-- Default Admin --}}
                <div class="flex items-center space-x-1 sm:space-x-1.5 lg:space-x-2 overflow-x-auto py-1">
                    <a href="{{ url('/admin/dashboard') }}" class="{{ $navItemClass(request()->is('admin/dashboard*')) }}">Dashboard</a>
                    <a href="{{ url('/admin/data-nasabah') }}" class="{{ $navItemClass(request()->is('admin/data-nasabah*') || request()->is('admin/*-nasabah*')) }}">Manajemen Nasabah</a>
                    <a href="{{ url('/avalis/petugas') }}" class="{{ $navItemClass(request()->is('avalis/petugas*')) }}">Petugas Avalis</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="{{ $navItemClass(request()->is('laporan/jurnal*')) }}">Jurnal</a>
                    <a href="{{ url('/admin/audit') }}" class="{{ $navItemClass(request()->is('admin/audit*')) }}">Jejak Audit</a>
                    <a href="{{ url('/admin/absensi-avalis') }}" class="{{ $navItemClass(request()->is('admin/absensi-avalis*') || request()->is('absensi-avalis*')) }}">Absensi</a>
                    <a href="{{ url('/admin/konfigurasi') }}" class="{{ $navItemClass(request()->is('admin/konfigurasi*')) }}">Konfigurasi</a>
            @endif

                <!-- Profile Dropdown -->
                <div class="relative ml-2 shrink-0">
                    <button onclick="toggleUnifiedNavbarDropdown(event)" id="unified-profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600 whitespace-nowrap cursor-pointer select-none">
                        @if($user && $user->foto)
                            <img src="{{ asset('storage/'.$user->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                        @else
                            <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                {{ strtoupper(substr($user->nama_petugas ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                        <span class="text-sm font-medium hidden sm:inline whitespace-nowrap">{{ $user->nama_petugas ?? 'User' }}</span>
                        <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div id="unified-profile-dropdown" class="hidden absolute right-0 mt-2 w-60 bg-white rounded-lg shadow-xl py-1 text-slate-700 border border-slate-200 z-50">
                        @php
                            $roleLabel = match($role) {
                                'admin' => 'Administrator',
                                'supervisor' => 'Supervisor',
                                'teller' => 'Teller',
                                'nasabah' => 'Nasabah',
                                default => ucfirst($role)
                            };
                        @endphp
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ $user->nama_petugas ?? '-' }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $user->username ?? '-' }} &bull; {{ $roleLabel }}</p>
                        </div>
                        @php
                            $profileUrl = url('/' . ($role === 'teller' ? 'teller/profil' : ($role === 'supervisor' ? 'supervisor/profil' : 'admin/profil')));
                        @endphp
                        <a href="{{ $profileUrl }}" class="px-4 py-2 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                            <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
                        </a>
                        <div class="border-t border-slate-100 my-1"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleUnifiedNavbarDropdown(e) {
        if (e) e.stopPropagation();
        const dd = document.getElementById('unified-profile-dropdown');
        if (dd) dd.classList.toggle('hidden');
    }
    window.addEventListener('click', function(e) {
        const btn = document.getElementById('unified-profile-btn');
        const dd = document.getElementById('unified-profile-dropdown');
        if (btn && dd && !btn.contains(e.target) && !dd.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });
</script>

<script>
    function toggleUnifiedNavbarDropdown(e) {
        if (e) e.stopPropagation();
        const dd = document.getElementById('unified-profile-dropdown');
        if (dd) dd.classList.toggle('hidden');
    }
    window.addEventListener('click', function(e) {
        const btn = document.getElementById('unified-profile-btn');
        const dd = document.getElementById('unified-profile-dropdown');
        if (btn && dd && !btn.contains(e.target) && !dd.contains(e.target)) {
            dd.classList.add('hidden');
        }
    });
</script>
