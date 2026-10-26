<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR ADMIN KONSISTEN -->
    <nav class="bg-slate-800 text-white shadow-md relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>
                <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                    <!-- Navigasi Aktif -->
                    <a href="{{ url('/admin/dashboard') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Dashboard</a>
                    <!-- Navigasi Tidak Aktif -->
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Manajemen Nasabah</a>
                    <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Petugas Avalis</a>
                    <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                    <a href="{{ url('/admin/audit') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jejak Audit</a>
                    <a href="{{ url('/admin/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                        <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                        Absensi
                    </a>
                    <a href="{{ url('/admin/konfigurasi') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Konfigurasi</a>

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

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <!-- DETEKTOR ANOMALI KESEIMBANGAN JURNAL -->
        @if(!$isBalanced)
        <div class="bg-rose-600 text-white p-5 mb-8 rounded-lg shadow-lg flex items-start border-l-8 border-rose-800 animate-pulse">
            <svg class="w-8 h-8 mr-4 text-rose-200 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <h2 class="text-xl font-black tracking-wide">PERINGATAN ANOMALI SISTEM!</h2>
                <p class="mt-1 font-medium text-rose-100">Terdeteksi ketidakseimbangan antara catatan Jurnal dan Mutasi Saldo Nasabah hari ini. Segera periksa log audit kasir yang bertugas.</p>
            </div>
        </div>
        @else
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 mb-8 rounded-lg shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="font-bold">Status Keseimbangan Keuangan: <span class="text-emerald-600">Aman & Stabil</span> (Selisih Rp 0)</p>
        </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-slate-800">Metrik Pengawasan Operasional</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Pemasukan (Debit)</p>
                <p class="text-2xl font-black text-slate-800">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                @if(isset($totalDropingKas) && $totalDropingKas > 0)
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Droping Kas: Rp {{ number_format($totalDropingKas, 0, ',', '.') }}
                    </p>
                @endif
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-slate-200">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Total Penarikan (Kredit)</p>
                <p class="text-2xl font-black text-slate-800">Rp {{ number_format($totalKredit, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-rose-200 border-l-4 border-l-amber-500">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Transaksi Di Void</p>
                <p class="text-2xl font-black text-amber-600">{{ $totalVoid }} <span class="text-sm font-normal text-slate-500">Kejadian</span></p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow-sm border border-rose-200 border-l-4 border-l-rose-500">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Kegagalan Sistem</p>
                <p class="text-2xl font-black text-rose-600">{{ $totalGagalSistem }} <span class="text-sm font-normal text-slate-500">Log Error</span></p>
            </div>
        </div>

        <!-- SECTION PERMOHONAN RESET PASSWORD PEGAWAI / AKUN -->
        @if(isset($resetRequests) && count($resetRequests) > 0)
        <div class="mb-8 bg-amber-50 border-2 border-amber-300 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-amber-500 animate-pulse"></span>
                    <h2 class="text-lg font-bold text-slate-800">Permohonan Reset Password Masuk</h2>
                    <span class="bg-amber-200 text-amber-900 text-xs font-black px-2 py-0.5 rounded-full">{{ count($resetRequests) }} Tiket</span>
                </div>
                <p class="text-xs text-slate-500">Pengguna lupa kata sandi dan meminta reset akun</p>
            </div>

            <div class="overflow-x-auto bg-white rounded-lg border border-amber-200">
                <table class="min-w-full text-left text-xs text-slate-700">
                    <thead class="bg-amber-100/70 font-bold text-slate-800">
                        <tr>
                            <th class="py-2.5 px-3">Waktu</th>
                            <th class="py-2.5 px-3">Username / Nama</th>
                            <th class="py-2.5 px-3">Saluran & Kontak</th>
                            <th class="py-2.5 px-3">Keterangan / Alasan</th>
                            <th class="py-2.5 px-3 text-center">Tindakan Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($resetRequests as $req)
                        <tr class="hover:bg-amber-50/40">
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-500">{{ \Carbon\Carbon::parse($req->created_at)->diffForHumans() }}</td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900 font-mono">{{ $req->username }}</span>
                                <div class="text-[11px] text-slate-500">{{ $req->nama_pemohon ?? '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $req->metode == 'whatsapp' ? 'bg-emerald-100 text-emerald-800' : ($req->metode == 'email' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $req->metode }}
                                </span>
                                <div class="text-[11px] font-mono text-slate-600 mt-0.5">{{ $req->kontak ?? '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 max-w-xs truncate" title="{{ $req->keterangan }}">
                                {{ $req->keterangan ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-center whitespace-nowrap space-x-1.5">
                                @php
                                    $cleanKontak = preg_replace('/[^0-9]/', '', $req->kontak ?? '');
                                    if (str_starts_with($cleanKontak, '0')) { $cleanKontak = '62' . substr($cleanKontak, 1); }
                                    $waReplyMsg = urlencode("Halo {$req->nama_pemohon}, permohonan reset password akun {$req->username} Anda di LKM Mitra Siswa Abadi telah diproses oleh Admin. Password sementara Anda adalah: bankmini" . date('Y'));
                                @endphp
                                @if(!empty($cleanKontak))
                                    <a href="https://wa.me/{{ $cleanKontak }}?text={{ $waReplyMsg }}" target="_blank" class="inline-flex items-center px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded font-bold text-[11px] transition">
                                        WA
                                    </a>
                                @endif
                                <form action="{{ url('/admin/reset-password-request/'.$req->id.'/approve') }}" method="POST" class="inline-block" onsubmit="return confirm('Reset password akun {{ $req->username }} menjadi: bankmini{{ date('Y') }}?')">
                                    @csrf
                                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-2.5 py-1 rounded font-bold text-[11px] transition shadow-sm">
                                        Reset Password
                                    </button>
                                </form>
                                <form action="{{ url('/admin/reset-password-request/'.$req->id.'/reject') }}" method="POST" class="inline-block" onsubmit="return confirm('Tolak permohonan reset ini?')">
                                    @csrf
                                    <button type="submit" class="bg-rose-100 hover:bg-rose-200 text-rose-700 px-2 py-1 rounded font-bold text-[11px] transition">
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

        <div class="flex justify-between items-center mb-4 mt-10">
            <h1 class="text-xl font-bold text-slate-800">Manajemen Hak Akses Pegawai</h1>
            <a href="{{ url('/admin/tambah-pegawai') }}" class="bg-slate-800 hover:bg-slate-900 text-white py-2 px-4 rounded text-sm font-bold shadow transition">+ Tambah Pegawai</a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
            <table class="min-w-full bg-white text-left">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm">Nama Petugas</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm">Username</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm">Jabatan</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 text-sm">
                    @foreach ($pegawai as $p)
                    <tr class="hover:bg-slate-50 border-b border-slate-100">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $p->nama_petugas }}</td>
                        <td class="py-3 px-4 font-mono text-slate-500">{{ $p->username }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 text-xs font-bold uppercase rounded
                                {{ $p->role == 'admin' ? 'bg-slate-200 text-slate-800' : ($p->role == 'supervisor' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $p->role }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center space-x-2">
                            <a href="{{ url('/admin/edit-pegawai/'.$p->id) }}" class="inline-block bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-1 px-3 rounded text-xs transition">Edit Akses</a>
                            @if(Auth::user()->id != $p->id)
                                <a href="{{ url('/admin/hapus-pegawai/'.$p->id) }}" onclick="return confirm('Peringatan: Anda akan menghapus atau memblokir pegawai ini. Lanjutkan?')" class="inline-block bg-rose-600 hover:bg-rose-700 text-white font-bold py-1 px-3 rounded text-xs transition">Blokir / Hapus</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function toggleDropdown() { document.getElementById('profile-dropdown').classList.toggle('hidden'); }
        window.addEventListener('click', function(e) {
            if (!document.getElementById('profile-btn').contains(e.target) && !document.getElementById('profile-dropdown').contains(e.target)) {
                document.getElementById('profile-dropdown').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
