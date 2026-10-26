<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Nasabah - Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans">
    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded shadow-sm font-medium flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded shadow-sm font-bold">
            {{ session('error') }}
        </div>
        @endif

        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Manajemen Nasabah & Rekening</h1>
                <p class="text-slate-600 text-sm mt-1">Format Penomoran Rekening: (Kode Jurusan - No Urut)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="bukaModalImport()" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 py-2 px-3.5 rounded-lg text-sm font-medium shadow-sm transition inline-flex items-center">
                    <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import CSV
                </button>
                <a href="{{ url('/admin/tambah-nasabah') }}" class="bg-slate-900 hover:bg-slate-800 text-white py-2 px-4 rounded-lg text-sm font-medium shadow-sm transition inline-flex items-center">
                    + Tambah Nasabah
                </a>
            </div>
        </div>

        <!-- FILTER TAB STATUS SISWA -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-wrap justify-between items-center gap-4">
            <!-- Tabs Filter Status -->
            <div class="flex space-x-2">
                <a href="{{ url('/admin/data-nasabah?status=aktif') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ (request('status', 'aktif') === 'aktif') ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Siswa Aktif ({{ $totalAktif }})
                </a>
                <a href="{{ url('/admin/data-nasabah?status=alumni') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ (request('status') === 'alumni') ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Arsip Alumni ({{ $totalAlumni }})
                </a>
                <a href="{{ url('/admin/data-nasabah?status=all') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ (request('status') === 'all') ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Data Nasabah
                </a>
            </div>

            <!-- Form Cari Cepat -->
            <form method="GET" action="{{ url('/admin/data-nasabah') }}" class="flex items-center space-x-2 text-xs">
                <input type="hidden" name="status" value="{{ request('status', 'aktif') }}">
                <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari NIS / Nama / No Rek..." class="border border-slate-300 rounded-lg px-3 py-1.5 w-52 focus:outline-none focus:ring-1 focus:ring-slate-500">
                <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-medium px-3 py-1.5 rounded-lg transition">Cari</button>
            </form>
        </div>

        <!-- TABEL DATA NASABAH -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left">
                    <thead class="bg-slate-100 text-slate-700 text-xs font-semibold">
                        <tr>
                            <th class="py-3 px-4 border-b border-slate-200">No. Rekening</th>
                            <th class="py-3 px-4 border-b border-slate-200">NIS & Nama Siswa</th>
                            <th class="py-3 px-4 border-b border-slate-200">Kelas & Jurusan</th>
                            <th class="py-3 px-4 border-b border-slate-200">Kategori</th>
                            <th class="py-3 px-4 border-b border-slate-200">Status</th>
                            <th class="py-3 px-4 border-b border-slate-200 text-right">Saldo</th>
                            <th class="py-3 px-4 border-b border-slate-200 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-700 text-xs">
                        @forelse ($nasabah as $n)
                        <tr class="hover:bg-slate-50 border-b border-slate-100 transition">
                            <!-- No Rekening -->
                            <td class="py-3 px-4 font-bold tabular-nums text-slate-900">
                                <span class="bg-slate-100 text-slate-800 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $n->no_rekening ?? $n->nis }}
                                </span>
                            </td>

                            <!-- NIS & Nama -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-sm text-slate-900 block">{{ $n->nama_siswa }}</span>
                                <span class="text-slate-500 tabular-nums text-[11px]">NIS: {{ $n->nis }}</span>
                            </td>

                            <!-- Kelas & Jurusan -->
                            <td class="py-3 px-4 font-medium">{{ $n->kelas }} - {{ $n->jurusan }}</td>

                            <!-- Kategori -->
                            <td class="py-3 px-4 text-[11px] font-semibold text-slate-600">
                                {{ $n->kategori ?? 'Siswa' }}
                            </td>

                            <!-- Status (Aktif vs Alumni) -->
                            <td class="py-3 px-4">
                                @if(($n->status ?? 'aktif') === 'aktif')
                                    <span class="bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded text-[10px]">
                                        Aktif
                                    </span>
                                @else
                                    <span class="bg-amber-100 text-amber-800 font-semibold px-2 py-0.5 rounded text-[10px]">
                                        Alumni
                                    </span>
                                @endif
                            </td>

                            <!-- Saldo -->
                            <td class="py-3 px-4 text-right font-bold text-sm text-emerald-700 tabular-nums">
                                Rp {{ number_format($n->saldo, 0, ',', '.') }}
                            </td>

                            <!-- Aksi Lengkap -->
                            <td class="py-3 px-4 text-center whitespace-nowrap space-x-1">
                                <!-- Tombol Buka Buku Tabungan -->
                                <a href="{{ url('/buku-tabungan/'.$n->id) }}" class="inline-block bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-1 px-2.5 rounded text-[11px] transition shadow-sm" title="Buku Tabungan Nasabah">
                                    Buku Tabungan
                                </a>

                                <!-- Tombol Ubah Status Alumni -->
                                <form action="{{ url('/admin/nasabah/ubah-status/'.$n->id) }}" method="POST" class="inline">
                                    @csrf
                                    @if(($n->status ?? 'aktif') === 'aktif')
                                        <input type="hidden" name="status" value="alumni">
                                        <button type="submit" onclick="return confirm('Pindahkan {{ $n->nama_siswa }} ke arsip Alumni?')" class="inline-block bg-amber-50 hover:bg-amber-100 text-amber-800 font-medium py-1 px-2 rounded text-[11px] transition border border-amber-300" title="Arsipkan sebagai Alumni">
                                            Alumni
                                        </button>
                                    @else
                                        <input type="hidden" name="status" value="aktif">
                                        <button type="submit" onclick="return confirm('Aktifkan kembali status nasabah {{ $n->nama_siswa }}?')" class="inline-block bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-medium py-1 px-2 rounded text-[11px] transition border border-emerald-300" title="Aktifkan Nasabah">
                                            Aktifkan
                                        </button>
                                    @endif
                                </form>

                                <a href="{{ url('/admin/edit-nasabah/'.$n->id) }}" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium py-1 px-2 rounded text-[11px] transition">Edit</a>
                                <a href="{{ url('/admin/hapus-nasabah/'.$n->id) }}" onclick="return confirm('Yakin ingin menghapus nasabah ini?')" class="inline-block bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium py-1 px-2 rounded text-[11px] transition">Hapus</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-500 italic">
                                Belum ada data nasabah pada kategori ini. Silakan tambahkan nasabah atau import dari file CSV.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- MODAL POP-UP IMPORT EXCEL -->
    <div id="modal-import" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="bg-slate-800 px-6 py-4 border-b border-slate-700">
                    <h3 class="text-base font-semibold text-white">
                        Import Data Nasabah (CSV)
                    </h3>
                </div>
                <form action="{{ url('/admin/import-nasabah') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="mb-4">
                        <p class="text-xs text-slate-600 mb-2">Sistem akan secara otomatis menghasilkan No. Rekening untuk setiap siswa yang di-import.</p>
                        <a href="{{ url('/admin/template-nasabah') }}" class="inline-flex items-center text-xs text-slate-700 font-medium hover:underline mb-4">
                            Unduh Template CSV Contoh
                        </a>
                    </div>
                    <div class="mb-6">
                        <label class="block text-slate-700 text-xs font-medium mb-2">Pilih File CSV (.csv / .txt):</label>
                        <input type="file" name="file_csv" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 border border-slate-300 rounded-lg p-2">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="tutupModalImport()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium rounded-lg text-xs transition">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-lg text-xs transition">Proses Import Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaModalImport() {
            document.getElementById('modal-import').classList.remove('hidden');
        }
        function tutupModalImport() {
            document.getElementById('modal-import').classList.add('hidden');
        }
    </script>
</body>
</html>
