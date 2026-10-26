<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Nasabah</title>
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

    <main class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="mb-6 border-b border-slate-200 pb-4">
                <h1 class="text-2xl font-bold text-slate-800">Daftarkan Nasabah Baru</h1>
                <p class="text-slate-600 text-sm mt-1">Masukkan data siswa yang akan dijadikan nasabah bank mini.</p>
            </div>

            <!-- Pesan Error Validasi -->
            @if ($errors->any())
                <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded shadow-sm text-sm font-bold">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/admin/tambah-nasabah') }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nomor Induk Siswa (NIS)</label>
                    <input type="text" name="nis" placeholder="Contoh: 17799" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700" required>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_siswa" placeholder="Contoh: Bima Revan Saputra" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700" required>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Kelas Siswa</label>
                    <input type="text" name="kelas" placeholder="Contoh: XII RPL 2" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700" required>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Kategori Nasabah</label>
                    <select name="kategori" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700 bg-white" required>
                        <option value="siswa">Siswa</option>
                        <option value="guru_karyawan">Guru / Karyawan</option>
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Jurusan</label>
                    <select name="jurusan" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 focus:ring-2 focus:ring-slate-500 focus:outline-none text-slate-700 bg-white" required>
                        <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak (Kode: RPL)</option>
                        <option value="Teknik Komputer dan Jaringan">Teknik Komputer dan Jaringan (Kode: TKJ)</option>
                        <option value="Akuntansi dan Keuangan Lembaga">Akuntansi dan Keuangan Lembaga (Kode: AK)</option>
                        <option value="Bisnis Retail">Bisnis Retail (Kode: BR)</option>
                        <option value="Bisnis Digital">Bisnis Digital (Kode: BD)</option>
                        <option value="Manajemen Perkantoran">Manajemen Perkantoran (Kode: MP)</option>
                        <option value="Desain Komunikasi Visual">Desain Komunikasi Visual (Kode: DKV)</option>
                    </select>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3.5 mb-6 text-xs text-slate-700">
                    <p class="font-semibold text-slate-800">
                        Penomoran Rekening Otomatis:
                    </p>
                    <p class="mt-0.5 text-slate-600">
                        Sistem akan menghasilkan Nomor Rekening otomatis dengan format <strong>(Kode Jurusan - No Urut)</strong>, misalnya <code>RPL-001</code>.
                    </p>
                </div>

                <div class="flex justify-end items-center space-x-3 border-t border-slate-200 pt-6">
                    <a href="{{ url('/admin/data-nasabah') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-4 rounded-lg text-sm transition shadow-sm">Batal</a>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-5 rounded-lg shadow-sm text-sm transition">
                        Simpan Nasabah
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
