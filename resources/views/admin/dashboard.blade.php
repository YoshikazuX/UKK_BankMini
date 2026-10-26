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

    <!-- NAVBAR UNIFIKASI -->
    <x-navbar />

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
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-none">
                <p class="text-sm font-medium text-slate-500 mb-1">Total Pemasukan (Debit)</p>
                <p class="text-2xl font-black text-slate-800">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
                @if(isset($totalDropingKas) && $totalDropingKas > 0)
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Droping Kas: Rp {{ number_format($totalDropingKas, 0, ',', '.') }}
                    </p>
                @endif
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-none">
                <p class="text-sm font-medium text-slate-500 mb-1">Total Penarikan (Kredit)</p>
                <p class="text-2xl font-black text-slate-800">Rp {{ number_format($totalKredit, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-none">
                <p class="text-sm font-medium text-slate-500 mb-1">Transaksi Void</p>
                <p class="text-2xl font-black {{ $totalVoid > 0 ? 'text-amber-600' : 'text-slate-900' }}">{{ $totalVoid }} <span class="text-sm font-normal text-slate-500">Kejadian</span></p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-none">
                <p class="text-sm font-medium text-slate-500 mb-1">Kegagalan Sistem</p>
                <p class="text-2xl font-black {{ $totalGagalSistem > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ $totalGagalSistem }} <span class="text-sm font-normal text-slate-500">Log Error</span></p>
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
                                <span class="font-bold text-slate-900">{{ $req->username }}</span>
                                <div class="text-[11px] text-slate-500">{{ $req->nama_pemohon ?? '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $req->metode == 'whatsapp' ? 'bg-emerald-100 text-emerald-800' : ($req->metode == 'email' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ $req->metode }}
                                </span>
                                <div class="text-[11px] tabular-nums text-slate-600 mt-0.5">{{ $req->kontak ?? '-' }}</div>
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

        <div class="bg-white rounded-xl shadow-none border border-slate-200 overflow-hidden">
            <table class="min-w-full bg-white text-left">
                <thead class="bg-slate-100 text-slate-700">
                    <tr>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm">Nama Petugas</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm">Username</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm">Jabatan</th>
                        <th class="py-3 px-4 font-bold border-b border-slate-200 text-sm text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-700 text-sm">
                    @foreach ($pegawai as $p)
                    <tr class="hover:bg-slate-50 border-b border-slate-100">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $p->nama_petugas }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $p->username }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full
                                {{ $p->role == 'admin' ? 'bg-slate-100 text-slate-800' : ($p->role == 'supervisor' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $p->role == 'admin' ? 'Admin' : ($p->role == 'supervisor' ? 'Supervisor' : 'Teller') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-2">
                            <a href="{{ url('/admin/edit-pegawai/'.$p->id) }}" class="inline-block border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-medium py-1 px-3 rounded text-xs transition">Edit Akses</a>
                            @if(Auth::user()->id != $p->id)
                                <a href="{{ url('/admin/hapus-pegawai/'.$p->id) }}" onclick="return confirm('Peringatan: Anda akan menghapus atau memblokir pegawai ini. Lanjutkan?')" class="inline-block border border-red-200 bg-white text-red-600 hover:bg-red-50 font-medium py-1 px-3 rounded text-xs transition">Blokir / Hapus</a>
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
