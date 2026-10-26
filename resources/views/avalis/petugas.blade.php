<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Petugas Avalis / Teller - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- NOTIFIKASI -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- HEADER & TOMBOL AKSI -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Master Data Petugas Avalis / Teller Loket
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Kelola basis data identitas petugas loket teller (Nama, Kelas, No. WhatsApp), tambah bulk, serta sinkronisasi otomatis ke lembar presensi.
                </p>
            </div>

            <div class="mt-4 md:mt-0 flex flex-wrap gap-2.5">
                <button type="button" onclick="openModalTambah()"
                        class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Petugas
                </button>

                <button type="button" onclick="openModalBulk()"
                        class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Tambah Bulk
                </button>

                <a href="{{ url('/absensi-avalis') }}"
                   class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                    Lembar Absensi Digital
                </a>
            </div>
        </div>

        <!-- FORM PENCARIAN -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <form action="{{ url('/avalis/petugas') }}" method="GET" class="w-full md:w-96 flex">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, kelas, atau WhatsApp..."
                       class="w-full text-xs border border-slate-300 rounded-l-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-r-lg text-xs font-bold transition">
                    Cari
                </button>
            </form>
            <div class="text-xs text-slate-500">
                Total Terdaftar: <span class="font-bold text-slate-800">{{ count($avalisList) }} Petugas Avalis</span>
            </div>
        </div>

        <!-- TABEL DATA PETUGAS AVALIS -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-700 text-xs font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-3 w-12 text-center">No</th>
                            <th class="p-3">Nama Lengkap Petugas</th>
                            <th class="p-3">Kelas / Jurusan</th>
                            <th class="p-3">Nomor WhatsApp</th>
                            <th class="p-3">Akun Login Terkait</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Tindakan / Presensi Hari Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($avalisList as $idx => $a)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 text-center text-slate-500 tabular-nums">{{ $idx + 1 }}</td>
                            <td class="p-3 font-semibold text-slate-900">
                                <div class="flex items-center space-x-2">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs">
                                        {{ strtoupper(substr($a->nama, 0, 1)) }}
                                    </div>
                                    <span>{{ $a->nama }}</span>
                                </div>
                            </td>
                            <td class="p-3 font-medium text-slate-600">
                                <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded text-[11px] font-semibold">
                                    {{ $a->kelas }}
                                </span>
                            </td>
                            <td class="p-3 tabular-nums">
                                @if($a->no_wa && $a->no_wa !== '-')
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $a->no_wa);
                                        if (str_starts_with($cleanWa, '0')) {
                                            $cleanWa = '62' . substr($cleanWa, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanWa }}?text=Halo%20{{ urlencode($a->nama) }},%20pemberitahuan%20tugas%20loket%20bank%20mini..."
                                       target="_blank" class="text-emerald-700 hover:text-emerald-900 font-medium flex items-center" title="Kirim Pesan WhatsApp">
                                        <svg class="w-3.5 h-3.5 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        {{ $a->no_wa }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-600">
                                @if($a->akun_username)
                                    <span class="font-bold text-slate-800">{{ $a->nama_akun_petugas }}</span>
                                    <span class="block text-[10px] text-slate-400">({{ $a->akun_username }})</span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum dihubungkan</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded">
                                    {{ strtoupper($a->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Tombol Auto Presensi Hari Ini -->
                                    <form action="{{ url('/avalis/petugas/sync/'.$a->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-bold px-2.5 py-1 rounded transition text-[11px] flex items-center shadow-sm" title="Presensikan Petugas ke Absensi Hari Ini">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            Hadirkan Hari Ini
                                        </button>
                                    </form>

                                    <!-- Tombol Hapus -->
                                    <a href="{{ url('/avalis/petugas/hapus/'.$a->id) }}"
                                       onclick="return confirm('Hapus data petugas avalis ini? Data absensi historis akan tetap tersimpan.')"
                                       class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold px-2 py-1 rounded transition text-[11px]">
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Belum ada data petugas Avalis. Silakan tambahkan 1 petugas atau impor data secara bulk.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL TAMBAH 1 PETUGAS AVALIS -->
    <div id="modal-tambah" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-slate-800 text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-bold">Tambah Petugas Avalis Baru</h3>
                <button type="button" onclick="closeModalTambah()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form action="{{ url('/avalis/petugas/simpan') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Nama Petugas Avalis / Siswa *</label>
                        <input type="text" name="nama" required placeholder="Contoh: Salsabila Putri"
                               class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Kelas / Jurusan *</label>
                        <input type="text" name="kelas" required placeholder="Contoh: XII AKL 1 / XII RPL 2"
                               class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Nomor WhatsApp Aktif *</label>
                        <input type="text" name="no_wa" required placeholder="Contoh: 081234567890"
                               class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Hubungkan ke Akun Login Teller (Opsional)</label>
                        <select name="user_id" class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Tidak Dihubungkan --</option>
                            @foreach($usersTeller as $u)
                                <option value="{{ $u->id }}">{{ $u->nama_petugas }} ({{ $u->username }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-2 border-t border-slate-200">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="masuk_absensi_hari_ini" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="font-bold text-slate-700">Langsung masukkan ke Lembar Absensi hari ini</span>
                        </label>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModalTambah()" class="text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 shadow-sm transition">
                        Batal
                    </button>
                    <button type="submit" class="text-xs font-medium text-white bg-slate-900 hover:bg-slate-800 px-5 py-2 rounded-lg shadow-sm transition">
                        Simpan Petugas
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- MODAL BULK IMPORT PETUGAS AVALIS -->
    <div id="modal-bulk" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="bg-blue-900 text-white px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold">Tambah Identitas Data Teller (Bulk Import)</h3>
                    <p class="text-xs text-blue-200 mt-0.5">Input banyak data teller sekaligus dari format teks / spreadsheet</p>
                </div>
                <button type="button" onclick="closeModalBulk()" class="text-blue-300 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form action="{{ url('/avalis/petugas/bulk') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4 text-xs">
                    <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg text-blue-900">
                        <p class="font-bold mb-1">Format Input (1 baris per petugas):</p>
                        <code class="block bg-white p-2 rounded border border-blue-200 text-[11px] font-mono">
                            Nama, Kelas, No WA<br>
                            Contoh:<br>
                            Ahmad Fauzi, XII RPL 1, 081298765432<br>
                            Dewi Sartika, XII AKL 2, 085712345678<br>
                            Budi Santoso, XII BDP 1, 089611223344
                        </code>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Tempel / Masukkan Baris Data *</label>
                        <textarea name="data_bulk" rows="6" required placeholder="Ahmad Fauzi, XII RPL 1, 081298765432&#10;Dewi Sartika, XII AKL 2, 085712345678"
                                  class="w-full text-xs font-mono border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600"></textarea>
                    </div>

                    <div class="pt-2 border-t border-slate-200">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="sync_ke_absensi" value="1" checked class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="font-bold text-slate-700">Otomatis sinkronkan juga ke Absensi Piket Avalis hari ini</span>
                        </label>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModalBulk()" class="text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 shadow-sm transition">
                        Batal
                    </button>
                    <button type="submit" class="text-xs font-medium text-white bg-slate-900 hover:bg-slate-800 px-5 py-2 rounded-lg shadow-sm transition">
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
</body>
</html>
