<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Nasabah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <!-- Notifikasi Sukses -->
        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded shadow-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <!-- KARTU UTAMA (Konsisten dengan Manajemen Pegawai) -->
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">

            <!-- Header Dalam Kartu -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-800">Manajemen Nasabah</h1>
                <p class="text-slate-600 mt-1">Kelola data akun siswa dan pantau saldo di sini.</p>
            </div>

            <!-- Tombol Tambah Warna Biru -->
            <div class="mb-6">
                <a href="{{ url('/admin/tambah-nasabah') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-bold transition">
                    + Tambah Nasabah Baru
                </a>
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full bg-white">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 text-left text-sm font-bold">NIS</th>
                            <th class="py-3 px-4 text-left text-sm font-bold">Nama Siswa</th>
                            <th class="py-3 px-4 text-left text-sm font-bold">Kelas & Jurusan</th>
                            <th class="py-3 px-4 text-left text-sm font-bold">Saldo</th>
                            <th class="py-3 px-4 text-center text-sm font-bold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 text-sm">
                        @forelse ($nasabah as $n)
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="py-3 px-4 text-slate-500">{{ $n->nis }}</td>
                            <td class="py-3 px-4 font-bold text-slate-800">{{ $n->nama_siswa }}</td>
                            <td class="py-3 px-4">{{ $n->kelas }} - {{ $n->jurusan }}</td>
                            <td class="py-3 px-4 font-medium text-emerald-600">Rp {{ number_format($n->saldo, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <!-- Tombol Kotak Edit (Kuning) dan Hapus (Merah) -->
                                    <a href="{{ url('/admin/edit-nasabah/'.$n->id) }}" class="bg-amber-500 hover:bg-amber-600 text-white py-1.5 px-3 rounded font-medium text-xs">Edit</a>
                                    <a href="{{ url('/admin/hapus-nasabah/'.$n->id) }}" class="bg-rose-500 hover:bg-rose-600 text-white py-1.5 px-3 rounded font-medium text-xs" onclick="return confirm('Yakin ingin menghapus data nasabah ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500 italic">Belum ada data nasabah di dalam sistem.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
