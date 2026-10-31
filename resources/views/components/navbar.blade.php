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

    // Data Notifikasi & Pending Tasks untuk peran Pengawasan (Supervisor / Accounting)
    $unreadNotificationCount = 0;
    $unreadNotifications = collect();
    $pendingApprovalTotal = 0;

    if ($user && in_array($role, ['supervisor', 'accounting'])) {
        $pendingPengajuan = \App\Models\PengajuanKas::where('status', 'pending')->count();
        $pendingDaily = \App\Models\DailyReport::whereIn('status', ['pending', 'hold'])->count();
        $pendingApprovalTotal = $pendingPengajuan + $pendingDaily;

        $unreadNotificationCount = $user->unreadNotifications()->count();
        $unreadNotifications = $user->unreadNotifications()->take(5)->get();
    } elseif ($user && $role === 'teller') {
        $pendingPengajuan = \App\Models\PengajuanKas::where('user_id', $user->id)->where('status', 'pending')->count();
    }
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

            <!-- Menu links according to role -->
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
            @elseif($role === 'supervisor' || $role === 'accounting')
                <div class="flex items-center space-x-1 sm:space-x-1.5 lg:space-x-2 overflow-x-auto py-1">
                    <a href="{{ url('/supervisor/dashboard') }}" class="{{ $navItemClass(request()->is('supervisor/dashboard*')) }}">Dashboard</a>
                    <a href="{{ url('/supervisor/approval-center') }}" class="{{ $navItemClass(request()->is('supervisor/approval-center*')) }} flex items-center">
                        Pusat Persetujuan
                        @if(($pendingApprovalTotal ?? 0) > 0)
                            <span class="ml-1.5 bg-amber-500 text-slate-900 text-[10px] font-black px-1.5 py-0.5 rounded-full animate-pulse">{{ $pendingApprovalTotal }}</span>
                        @endif
                    </a>
                    <a href="{{ url('/supervisor/daily-report') }}" class="{{ $navItemClass(request()->is('supervisor/daily-report*')) }}">Daily Report</a>
                    <a href="{{ url('/supervisor/pengajuan-kas') }}" class="{{ $navItemClass(request()->is('supervisor/pengajuan-kas*')) }}">Pengajuan Kas</a>
                    <a href="{{ url('/supervisor/rekonsiliasi') }}" class="{{ $navItemClass(request()->is('supervisor/rekonsiliasi*')) }}">Rekonsiliasi</a>
                    <a href="{{ url('/supervisor/monitoring-teller') }}" class="{{ $navItemClass(request()->is('supervisor/monitoring-teller*')) }}">Monitoring</a>
                    <a href="{{ url('/supervisor/riwayat-approval') }}" class="{{ $navItemClass(request()->is('supervisor/riwayat-approval*')) }}">Riwayat Otorisasi</a>
                    <a href="{{ url('/supervisor/laporan') }}" class="{{ $navItemClass(request()->is('supervisor/laporan*')) }}">Laporan</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="{{ $navItemClass(request()->is('laporan/jurnal*')) }}">Jurnal</a>
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
                    <a href="{{ url('/admin/reset-password-requests') }}" class="{{ $navItemClass(request()->is('admin/reset-password-requests*')) }}">Permohonan Reset</a>
                    <a href="{{ url('/admin/audit') }}" class="{{ $navItemClass(request()->is('admin/audit*')) }}">Jejak Audit</a>
                    <a href="{{ url('/admin/absensi-avalis') }}" class="{{ $navItemClass(request()->is('admin/absensi-avalis*') || request()->is('absensi-avalis*')) }}">Absensi</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="{{ $navItemClass(request()->is('laporan/jurnal*')) }}">Jurnal</a>
                    <a href="{{ url('/admin/konfigurasi') }}" class="{{ $navItemClass(request()->is('admin/konfigurasi*')) }}">Konfigurasi</a>
            @endif

                <div class="flex items-center space-x-2 ml-3 shrink-0">
                    <!-- NOTIFIKASI LONCENG KHUSUS SUPERVISOR / TELLER -->
                    @if(in_array($role, ['supervisor', 'accounting']))
                    <div class="relative">
                        <button onclick="toggleNavbarNotificationDropdown(event)" id="unified-notif-btn" 
                                class="relative p-1.5 rounded-full text-slate-300 hover:text-white hover:bg-slate-700 transition focus:outline-none" 
                                title="Notifikasi Pekerjaan">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unreadNotificationCount > 0)
                                <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-black leading-none text-white transform translate-x-1 -translate-y-1 bg-amber-500 rounded-full">
                                    {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                                </span>
                            @endif
                        </button>

                        <!-- DROPDOWN NOTIFIKASI -->
                        <div id="unified-notif-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl py-2 text-slate-700 border border-slate-200 z-50 overflow-hidden">
                            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-bold text-slate-900">Notifikasi Pekerjaan</span>
                                    @if($unreadNotificationCount > 0)
                                        <span class="bg-amber-100 text-amber-800 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                            {{ $unreadNotificationCount }} baru
                                        </span>
                                    @endif
                                </div>
                                @if($unreadNotificationCount > 0)
                                <form action="{{ route('supervisor.notifikasi.baca-semua') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">Tandai Baca</button>
                                </form>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                                @forelse($unreadNotifications as $notif)
                                    <a href="{{ route('supervisor.notifikasi.baca', $notif->id) }}" class="block px-4 py-3 hover:bg-slate-50 transition">
                                        <div class="flex items-start justify-between">
                                            <p class="text-xs font-bold text-slate-900">{{ $notif->data['title'] ?? 'Tugas Baru' }}</p>
                                            <span class="text-[10px] text-slate-400 whitespace-nowrap ml-2">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">{{ $notif->data['message'] ?? '-' }}</p>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <span class="inline-block text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700">
                                                {{ $notif->data['nomor_referensi'] ?? '' }}
                                            </span>
                                            <span class="text-[10px] text-slate-500">Oleh: {{ $notif->data['nama_teller'] ?? 'Teller' }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="py-6 px-4 text-center text-xs text-slate-400 italic">
                                        Tidak ada notifikasi baru yang belum dibaca.
                                    </div>
                                @endforelse
                            </div>

                            <div class="p-2 border-t border-slate-100 bg-slate-50 text-center">
                                <a href="{{ route('supervisor.notifikasi') }}" class="text-xs font-semibold text-slate-700 hover:text-slate-900 block py-1">
                                    Lihat Seluruh Notifikasi &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Profile Dropdown -->
                    <div class="relative shrink-0">
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
                                    'accounting' => 'Accounting',
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
                                $profileUrl = url('/' . ($role === 'teller' ? 'teller/profil' : ($role === 'supervisor' || $role === 'accounting' ? 'supervisor/profil' : 'admin/profil')));
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
    </div>
</nav>

<script>
    function toggleUnifiedNavbarDropdown(e) {
        if (e) e.stopPropagation();
        const dd = document.getElementById('unified-profile-dropdown');
        const notifDd = document.getElementById('unified-notif-dropdown');
        if (notifDd) notifDd.classList.add('hidden');
        if (dd) dd.classList.toggle('hidden');
    }

    function toggleNavbarNotificationDropdown(e) {
        if (e) e.stopPropagation();
        const dd = document.getElementById('unified-notif-dropdown');
        const profDd = document.getElementById('unified-profile-dropdown');
        if (profDd) profDd.classList.add('hidden');
        if (dd) dd.classList.toggle('hidden');
    }

    window.addEventListener('click', function(e) {
        const profBtn = document.getElementById('unified-profile-btn');
        const profDd = document.getElementById('unified-profile-dropdown');
        if (profBtn && profDd && !profBtn.contains(e.target) && !profDd.contains(e.target)) {
            profDd.classList.add('hidden');
        }

        const notifBtn = document.getElementById('unified-notif-btn');
        const notifDd = document.getElementById('unified-notif-dropdown');
        if (notifBtn && notifDd && !notifBtn.contains(e.target) && !notifDd.contains(e.target)) {
            notifDd.classList.add('hidden');
        }
    });
</script>
