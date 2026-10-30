<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Petugas Avalis / Teller - Bank Mini</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 min-h-screen flex flex-col antialiased">

    <!-- NAVBAR UNIFIKASI -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">
        <!-- NOTIFIKASI SUKSES -->
        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 mb-6 rounded-xl shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        <!-- NOTIFIKASI ERROR -->
        @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 mb-6 rounded-xl shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- READ-ONLY BANNER UNTUK SUPERVISOR DAN TELLER -->
        @if(!$isAdmin)
        <div class="bg-slate-100 border border-slate-200 text-slate-700 px-4 py-3 mb-6 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-900">Mode Pemantauan Master Petugas (Read-Only)</span>
                    <p class="text-xs text-slate-600 mt-0.5">Anda masuk sebagai <strong>{{ ucfirst($role) }}</strong>. Penambahan, pengeditan, sinkronisasi, dan penghapusan petugas hanya berwenang dilakukan oleh Administrator Utama.</p>
                </div>
            </div>
            <a href="{{ url('/absensi-avalis') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm">
                Lihat Lembar Absensi &rarr;
            </a>
        </div>
        @endif

        <!-- HEADER & TOMBOL AKSI -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-1.5">
                    <span>Administrasi</span>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-slate-700 font-medium">Master Avalis</span>
                </nav>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                        Master Data Petugas Avalis / Teller Loket
                    </h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80" title="Tersinkronisasi otomatis dengan data pegawai / petugas asli di sistem">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sinkron Otomatis
                    </span>
                </div>
                <p class="text-slate-500 text-sm mt-1">
                    Kelola basis data identitas petugas loket teller (Nama, Kelas, No. WhatsApp), sinkron otomatis dengan akun pegawai sistem.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if($isAdmin)
                <a href="{{ url('/avalis/petugas/sync-from-petugas') }}"
                   class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-3.5 py-2 rounded-lg text-xs sm:text-sm transition shadow-sm flex items-center gap-1.5"
                   title="Tarik data petugas dari tabel pegawai (users) secara otomatis">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Sinkronkan Petugas</span>
                </a>

                <button type="button" onclick="openModalTambah()"
                        class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2 rounded-lg text-xs sm:text-sm transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Petugas</span>
                </button>

                <button type="button" onclick="openModalBulk()"
                        class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-3.5 py-2 rounded-lg text-xs sm:text-sm transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Tambah Bulk</span>
                </button>
                @endif

                <a href="{{ url('/absensi-avalis') }}"
                   class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-3.5 py-2 rounded-lg text-xs sm:text-sm transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Lembar Absensi</span>
                </a>
            </div>
        </div>

        <!-- FORM PENCARIAN -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-sm mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <form action="{{ url('/avalis/petugas') }}" method="GET" class="w-full sm:w-96 flex">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, kelas, atau nomor WA..."
                       class="w-full text-xs sm:text-sm border border-slate-300 rounded-l-lg px-3.5 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-r-lg text-xs sm:text-sm font-semibold transition">
                    Cari
                </button>
            </form>
            <div class="text-xs text-slate-500">
                Total Terdaftar: <strong class="font-bold text-slate-800">{{ count($avalisList) }} Petugas Avalis</strong>
            </div>
        </div>

        <!-- TABEL DATA PETUGAS AVALIS -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200/90 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm border-collapse">
                    <thead class="bg-slate-50/80 text-slate-600 text-xs font-semibold border-b border-slate-200 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4 min-w-[180px]">Nama Lengkap Petugas</th>
                            <th class="py-3 px-4 w-32">Kelas / Jurusan</th>
                            <th class="py-3 px-4 w-36">Nomor WhatsApp</th>
                            <th class="py-3 px-4 min-w-[160px]">Akun Login Terkait</th>
                            <th class="py-3 px-4 text-center w-28">Status</th>
                            @if($isAdmin)
                            <th class="py-3 px-4 text-center w-48">Tindakan / Presensi Hari Ini</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($avalisList as $idx => $a)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 text-center text-slate-400 font-medium tabular-nums">{{ $idx + 1 }}</td>
                            <td class="py-3.5 px-4 font-semibold text-slate-900">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $aFotoUrl = $a->foto_url;
                                        $initial = strtoupper(mb_substr(trim($a->nama), 0, 1));
                                    @endphp
                                    @if($aFotoUrl)
                                        <img src="{{ $aFotoUrl }}"
                                             alt="Foto {{ $a->nama }}"
                                             class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-sm">
                                            {{ $initial }}
                                        </div>
                                    @endif
                                    <span>{{ $a->nama }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-600">
                                <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs font-semibold border border-slate-200/70">
                                    {{ $a->kelas }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 tabular-nums">
                                @if($a->no_wa && $a->no_wa !== '-')
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $a->no_wa);
                                        if (str_starts_with($cleanWa, '0')) {
                                            $cleanWa = '62' . substr($cleanWa, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanWa }}?text=Halo%20{{ urlencode($a->nama) }},%20pemberitahuan%20tugas%20loket%20bank%20mini..."
                                       target="_blank" class="text-slate-700 hover:text-emerald-700 font-medium flex items-center gap-1.5 transition" title="Kirim Pesan WhatsApp">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        <span>{{ $a->no_wa }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                @if($a->akun_username)
                                    <span class="font-semibold text-slate-800">{{ $a->nama_akun_petugas }}</span>
                                    <span class="block text-[11px] text-slate-400">({{ $a->akun_username }})</span>
                                @else
                                    <span class="text-slate-400 italic text-xs">Belum dihubungkan</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ strtoupper($a->status) }}
                                </span>
                            </td>
                            @if($isAdmin)
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Tombol Auto Presensi Hari Ini -->
                                    <form action="{{ url('/avalis/petugas/sync/'.$a->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold px-2.5 py-1 rounded-lg transition text-xs flex items-center shadow-sm" title="Presensikan Petugas ke Absensi Hari Ini">
                                            <svg class="w-3.5 h-3.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            Hadirkan Hari Ini
                                        </button>
                                    </form>

                                    <!-- Tombol Hapus -->
                                    <a href="{{ url('/avalis/petugas/hapus/'.$a->id) }}"
                                       onclick="return confirm('Hapus data petugas avalis ini? Data absensi historis akan tetap tersimpan.')"
                                       class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold px-2.5 py-1 rounded-lg transition text-xs">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? '7' : '6' }}" class="py-12 px-4 text-center text-slate-400">
                                <div class="max-w-xs mx-auto flex flex-col items-center">
                                    <svg class="w-10 h-10 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <p class="text-sm font-semibold text-slate-700">Belum ada data petugas</p>
                                    <p class="text-xs text-slate-400 mt-1">Silakan sinkronkan atau tambahkan petugas avalis baru.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    @if($isAdmin)
    <!-- MODAL TAMBAH 1 PETUGAS AVALIS -->
    <div id="modal-tambah" class="hidden fixed inset-0 bg-slate-900/50 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Tambah Petugas Avalis Baru</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftarkan petugas avalis atau siswa loket.</p>
                    </div>
                    <button type="button" onclick="closeModalTambah()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>

                <form action="{{ url('/avalis/petugas/simpan') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4 text-xs sm:text-sm">
                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider text-xs mb-1">Nama Petugas Avalis / Siswa <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama" required placeholder="Contoh: Salsabila Putri"
                                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider text-xs mb-1">Kelas / Jurusan <span class="text-rose-500">*</span></label>
                            <input type="text" name="kelas" required placeholder="Contoh: XII AKL 1 / XII RPL 2"
                                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider text-xs mb-1">Nomor WhatsApp Aktif <span class="text-rose-500">*</span></label>
                            <input type="text" name="no_wa" required placeholder="Contoh: 081234567890"
                                   class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900 tabular-nums">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider text-xs mb-1">Hubungkan ke Akun Pegawai (Opsional)</label>
                            <select name="user_id" class="w-full text-xs sm:text-sm border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900 bg-white">
                                <option value="">-- Tidak Dihubungkan --</option>
                                @foreach($usersTeller as $u)
                                    <option value="{{ $u->id }}">{{ $u->nama_petugas }} ({{ $u->username }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="masuk_absensi_hari_ini" value="1" checked class="rounded text-slate-900 focus:ring-slate-900">
                                <span class="font-medium text-slate-700 text-xs">Langsung masukkan ke Lembar Absensi hari ini</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end space-x-2.5">
                        <button type="button" onclick="closeModalTambah()" class="text-xs sm:text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                            Batal
                        </button>
                        <button type="submit" class="text-xs sm:text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 px-5 py-2 rounded-lg shadow-sm transition">
                            Simpan Petugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL BULK IMPORT PETUGAS AVALIS -->
    <div id="modal-bulk" class="hidden fixed inset-0 bg-slate-900/50 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full overflow-hidden border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Bulk Import Petugas Avalis</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Input banyak data teller sekaligus dari format teks.</p>
                    </div>
                    <button type="button" onclick="closeModalBulk()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>

                <form action="{{ url('/avalis/petugas/bulk') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4 text-xs sm:text-sm">
                        <div class="bg-slate-50 border border-slate-200 p-3 rounded-xl text-slate-800">
                            <p class="font-semibold text-xs mb-1">Format Input (1 baris per petugas):</p>
                            <code class="block bg-white p-2 rounded border border-slate-200 text-[11px] font-mono text-slate-700">
                                Nama, Kelas, No WA<br>
                                Contoh:<br>
                                Ahmad Fauzi, XII RPL 1, 081298765432<br>
                                Dewi Sartika, XII AKL 2, 085712345678
                            </code>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 uppercase tracking-wider text-xs mb-1">Tempel / Masukkan Baris Data <span class="text-rose-500">*</span></label>
                            <textarea name="data_bulk" rows="5" required placeholder="Ahmad Fauzi, XII RPL 1, 081298765432&#10;Dewi Sartika, XII AKL 2, 085712345678"
                                      class="w-full text-xs font-mono border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900"></textarea>
                        </div>

                        <div class="pt-2 border-t border-slate-200">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="sync_ke_absensi" value="1" checked class="rounded text-slate-900 focus:ring-slate-900">
                                <span class="font-medium text-slate-700 text-xs">Otomatis sinkronkan juga ke Absensi Piket Avalis hari ini</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 flex justify-end space-x-2.5">
                        <button type="button" onclick="closeModalBulk()" class="text-xs sm:text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                            Batal
                        </button>
                        <button type="submit" class="text-xs sm:text-sm font-medium text-white bg-slate-900 hover:bg-slate-800 px-5 py-2 rounded-lg shadow-sm transition">
                            Proses Bulk Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModalTambah() { document.getElementById('modal-tambah').classList.remove('hidden'); }
        function closeModalTambah() { document.getElementById('modal-tambah').classList.add('hidden'); }
        function openModalBulk() { document.getElementById('modal-bulk').classList.remove('hidden'); }
        function closeModalBulk() { document.getElementById('modal-bulk').classList.add('hidden'); }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModalTambah();
                closeModalBulk();
            }
        });
    </script>
    @endif
</body>
</html>