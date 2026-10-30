<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Nasabah Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <x-navbar />

    <main class="max-w-3xl mx-auto py-10 px-4">

        <!-- Tampilkan pesan error jika ada data yang kurang -->
        @if ($errors->any())
            <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded shadow-sm text-sm">
                <strong class="font-bold">Gagal menyimpan data!</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Tambah Nasabah Baru</h1>
            <p class="text-slate-600 mb-6">Silakan isi formulir di bawah ini untuk mendaftarkan data profil dan akun login nasabah.</p>

            <form action="{{ url('/admin/tambah-nasabah') }}" method="POST">
                @csrf

                <!-- BAGIAN 1: DATA PROFIL SISWA -->
                <div class="mb-4 border-b border-slate-200 pb-2">
                    <h2 class="font-bold text-slate-700">A. Data Profil Siswa</h2>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">NIS (Nomor Induk Siswa)</label>
                    <input type="text" name="nis" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: 17763">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_siswa" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Dewi Handayani Nur Halimah">
                </div>

                <div class="flex gap-4 mb-6">
                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kelas</label>
                        <input type="text" name="kelas" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: XI RPL 1">
                    </div>
                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jurusan</label>
                        <input type="text" name="jurusan" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: RPL">
                    </div>
                </div>

                <!-- BAGIAN 2: DATA AKUN APLIKASI (TAMBAHAN BARU) -->
                <div class="mb-4 border-b border-slate-200 pb-2 mt-8">
                    <h2 class="font-bold text-slate-700">B. Data Akun Aplikasi Mobile</h2>
                </div>

                <div class="bg-slate-50 p-4 rounded-md border border-slate-200 mb-6">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Username Login</label>
                        <input type="text" name="username" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Contoh: dewi123">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Password Login</label>
                        <input type="password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Minimal 6 karakter">
                    </div>
                </div>

                <div class="bg-blue-50 p-4 rounded text-sm text-blue-800 mb-6 border border-blue-200">
                    <strong>Penting:</strong> Saldo awal akan otomatis <strong>Rp 0</strong>, No. Rekening otomatis terbuat, dan PIN Transaksi default adalah <strong>123456</strong>.
                </div>

                <div class="flex items-center space-x-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition">Simpan Nasabah</button>
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-500 hover:text-slate-800 font-medium">Batal</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
