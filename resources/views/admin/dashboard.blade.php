<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator - Bank Mini Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scrollbar-gutter: stable; overflow-y: scroll; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR RESPONSIF -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- HEADER ADMIN -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Administrasi & Keamanan Sistem</h1>
                <p class="text-slate-500 text-sm mt-1">
                    Tata kelola akun pegawai, manajemen data nasabah, otorisasi reset sandi, dan pemantauan sistem.
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ url('/admin/data-pegawai') }}" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-3.5 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Kelola Pegawai
                </a>
                <a href="{{ url('/admin/konfigurasi') }}" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold px-4 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Konfigurasi Sistem
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        <!-- 3 WIDGET UTAMA DASHBOARD ADMIN -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <!-- 1. Total Nasabah -->
            <a href="{{ route('admin.nasabah.index') }}" class="group bg-white p-5 rounded-xl border border-slate-200 hover:border-slate-300 shadow-sm transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Total Nasabah Terdaftar</span>
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-900 tabular-nums">
                        {{ number_format($nasabahSummary['total'], 0, ',', '.') }}
                        <span class="text-xs font-normal text-slate-500">Siswa</span>
                    </p>
                    <div class="flex items-center gap-2 mt-3 text-xs">
                        <span class="font-semibold text-emerald-700">
                            {{ $nasabahSummary['aktif'] }} Aktif
                        </span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="font-medium text-slate-500">
                            {{ $nasabahSummary['nonaktif'] }} Nonaktif
                        </span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-indigo-600 font-semibold group-hover:text-indigo-800">
                    <span>Manajemen Nasabah</span>
                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <!-- 2. Total Pegawai Aktif -->
            <a href="{{ url('/admin/data-pegawai') }}" class="group bg-white p-5 rounded-xl border border-slate-200 hover:border-slate-300 shadow-sm transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Total Pegawai Aktif</span>
                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <p class="text-2xl font-black text-slate-900 tabular-nums">
                        {{ $pegawaiSummary['total_aktif'] }}
                        <span class="text-xs font-normal text-slate-500">Pegawai Aktif</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-1.5 mt-2.5 text-xs text-slate-600">
                        <span class="font-medium">Teller: {{ $pegawaiSummary['roles']['teller'] }}</span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="font-medium">Spv: {{ $pegawaiSummary['roles']['supervisor'] }}</span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="font-medium">Adm: {{ $pegawaiSummary['roles']['admin'] }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-emerald-700 font-semibold group-hover:text-emerald-900">
                    <span>Manajemen Pegawai</span>
                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>

            <!-- 3. Permohonan Reset Password -->
            <a href="{{ route('admin.reset.index') }}" class="group bg-white p-5 rounded-xl border {{ $resetPasswordSummary['pending'] > 0 ? 'border-amber-300 ring-2 ring-amber-100' : 'border-slate-200' }} hover:border-slate-300 shadow-sm transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-slate-500 text-xs font-semibold mb-2">
                        <span>Permohonan Reset Password</span>
                        <svg class="w-5 h-5 {{ $resetPasswordSummary['pending'] > 0 ? 'text-amber-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <p class="text-2xl font-black {{ $resetPasswordSummary['pending'] > 0 ? 'text-amber-600' : 'text-slate-900' }} tabular-nums">
                            {{ $resetPasswordSummary['pending'] }}
                        </p>
                        <span class="text-xs font-semibold text-slate-500">Tiket Butuh Tindakan</span>
                    </div>
                    @if($resetPasswordSummary['latest'])
                        <p class="text-[11px] text-slate-500 mt-2 truncate">
                            Terbaru: <strong class="text-slate-700">{{ $resetPasswordSummary['latest']['username'] }}</strong> ({{ $resetPasswordSummary['latest']['time_ago'] }})
                        </p>
                    @else
                        <p class="text-[11px] text-slate-400 mt-2">Tidak ada permohonan tertunda</p>
                    @endif
                    <div class="text-[11px] text-slate-400 mt-0.5">
                        Diproses: {{ $resetPasswordSummary['processing'] }} &bull; Selesai: {{ $resetPasswordSummary['completed'] }}
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-amber-700 font-semibold group-hover:text-amber-900">
                    <span>Buka Kelola Tiket</span>
                    <svg class="w-3.5 h-3.5 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- KOLOM KIRI (2 KOLOM): TIKET RESET PASSWORD & PUSAT NAVIGASI SISTEM -->
            <div class="lg:col-span-2 space-y-8">
                <!-- TIKET RESET PASSWORD MENUNGGU TINDAKAN -->
                @if(isset($resetRequests) && count($resetRequests) > 0)
                <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-amber-100 bg-amber-50/60 flex items-center justify-between">
                        <h2 class="font-bold text-slate-900 text-sm">Permohonan Reset Password Menunggu Otorisasi</h2>
                        <a href="{{ route('admin.reset.index') }}" class="text-xs font-semibold text-amber-800 hover:text-amber-900">
                            Kelola Semua Tiket &rarr;
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-4">Waktu</th>
                                    <th class="py-2.5 px-4">Akun Pemohon</th>
                                    <th class="py-2.5 px-4">Kontak / Saluran</th>
                                    <th class="py-2.5 px-4">Keterangan</th>
                                    <th class="py-2.5 px-4 text-center">Tindakan Admin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach($resetRequests as $req)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-2.5 px-4 whitespace-nowrap text-slate-500">
                                        {{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <span class="font-bold text-slate-900">{{ $req->username }}</span>
                                        <span class="text-[11px] text-slate-500 block">{{ $req->nama_pemohon ?? '-' }}</span>
                                    </td>
                                    <td class="py-2.5 px-4">
                                        <span class="text-xs font-semibold uppercase {{ $req->metode === 'whatsapp' ? 'text-emerald-700' : 'text-blue-700' }}">
                                            {{ $req->metode }}
                                        </span>
                                        <div class="text-[11px] tabular-nums text-slate-600 mt-0.5">{{ $req->kontak ?? '-' }}</div>
                                    </td>
                                    <td class="py-2.5 px-4 text-slate-600 max-w-xs truncate" title="{{ $req->keterangan }}">
                                        {{ $req->keterangan ?? '-' }}
                                    </td>
                                    <td class="py-2.5 px-4 text-center whitespace-nowrap space-x-1.5">
                                        <form action="{{ url('/admin/reset-password-request/'.$req->id.'/approve') }}" method="POST" class="inline-block" onsubmit="return confirm('Reset password akun {{ $req->username }}?')">
                                            @csrf
                                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-2.5 py-1 rounded-lg font-semibold text-[11px] transition shadow-sm">
                                                Reset Sandi
                                            </button>
                                        </form>
                                        <form action="{{ url('/admin/reset-password-request/'.$req->id.'/reject') }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak permohonan ini?')">
                                            @csrf
                                            <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-lg font-semibold text-[11px] transition">
                                                Tolak
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- PUSAT JALAN PINTAS ADMINISTRATIF (ENTERPRISE ACTION CENTER) -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <div class="border-b border-slate-100 pb-3 mb-4">
                        <h2 class="text-sm font-bold text-slate-900">Aksi Administratif Cepat</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Navigasi langsung ke modul tata kelola data dan konfigurasi sistem.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <a href="{{ url('/admin/data-pegawai') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition flex items-center justify-between group">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block group-hover:text-emerald-700">Daftar Pegawai & Akses</span>
                                    <span class="text-[11px] text-slate-500">Kelola akun, role, dan kredensial</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ url('/admin/tambah-pegawai') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition flex items-center justify-between group">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-800 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block group-hover:text-slate-900">Tambah Akun Pegawai</span>
                                    <span class="text-[11px] text-slate-500">Registrasi petugas Teller / Supervisor</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ url('/admin/data-nasabah') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition flex items-center justify-between group">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block group-hover:text-indigo-700">Data Master Nasabah</span>
                                    <span class="text-[11px] text-slate-500">Rekening siswa, status & saldo</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>

                        <a href="{{ url('/admin/konfigurasi') }}" class="p-3.5 rounded-xl border border-slate-200 hover:border-slate-300 hover:bg-slate-50 transition flex items-center justify-between group">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block group-hover:text-amber-800">Kebijakan & Konfigurasi</span>
                                    <span class="text-[11px] text-slate-500">Saldo mengendap, scanner, format rekening</span>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (1 KOLOM): JEJAK AUDIT SISTEM & INFORMASI SERVER -->
            <div class="space-y-8">
                <!-- JEJAK AUDIT TERBARU -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                        <h2 class="text-sm font-bold text-slate-900">Jejak Audit Aktivitas</h2>
                        <a href="{{ url('/admin/audit') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Semua Log &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($recentAuditLogs as $log)
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">{{ $log->jenis_aktivitas }}</span>
                                <span class="text-[10px] text-slate-400 tabular-nums">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-600 text-[11px] mt-0.5">
                                Oleh: <strong class="text-slate-800">{{ $log->nama_user ?? 'Sistem' }}</strong> ({{ ucfirst($log->role_user ?? '-') }})
                            </p>
                            @if($log->objek_id)
                                <span class="inline-block mt-1 text-[10px] font-mono text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                    ID: {{ $log->objek_id }}
                                </span>
                            @endif
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 italic py-4 text-center">Belum ada catatan aktivitas tercatat.</p>
                        @endforelse
                    </div>
                </div>

                <!-- LINGKUNGAN SISTEM & TEKNOLOGI -->
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 text-xs space-y-2.5">
                    <h3 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-2.5 mb-3">Spesifikasi Sistem</h3>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Framework:</span>
                        <span class="font-semibold text-slate-800">Laravel v13.24 (PHP 8.5)</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Database Driver:</span>
                        <span class="font-semibold text-slate-800">{{ config('database.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Storage Driver:</span>
                        <span class="font-semibold text-slate-800">{{ config('filesystems.default') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Timezone:</span>
                        <span class="font-semibold text-slate-800">{{ config('app.timezone') }}</span>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex justify-between text-slate-400 text-[11px]">
                        <span>LKM Mitra Siswa Abadi</span>
                        <span>SMK Negeri 1 Bantul</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleDropdown() { document.getElementById('profile-dropdown')?.classList.toggle('hidden'); }
    </script>
</body>
</html>
