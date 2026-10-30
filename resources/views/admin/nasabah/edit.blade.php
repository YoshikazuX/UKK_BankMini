<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nasabah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans">

    <x-navbar />

    <main class="max-w-3xl mx-auto py-10 px-4">

        <!-- Notifikasi Error Validasi -->
        @if ($errors->any())
            <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded shadow-sm text-sm">
                <strong class="font-bold">Gagal memperbarui data!</strong>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Edit Data Nasabah</h1>
            <p class="text-slate-600 mb-6">Perbarui informasi profil dan akun aplikasi siswa di bawah ini.</p>

            <form action="{{ url('/admin/edit-nasabah/'.$nasabah->id) }}" method="POST">
                @csrf

                <!-- A. DATA PROFIL -->
                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">NIS</label>
                    <input type="text" name="nis" value="{{ $nasabah->nis }}" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="nama_siswa" value="{{ $nasabah->nama_siswa }}" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex gap-4 mb-6">
                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Kelas</label>
                        <input type="text" name="kelas" value="{{ $nasabah->kelas }}" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-1/2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Jurusan</label>
                        <input type="text" name="jurusan" value="{{ $nasabah->jurusan }}" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- B. DATA AKUN MOBILE -->
                <div class="bg-amber-50 p-4 rounded-md border border-amber-200 mb-6 mt-4">
                    <h3 class="font-bold text-amber-800 mb-4 border-b border-amber-200 pb-2">Data Akun Mobile (E-Kantin)</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Username Login</label>
                        <input type="text" name="username" value="{{ $nasabah->username }}" required class="w-full px-4 py-2 border border-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Password Baru (Opsional)</label>
                        <input type="password" name="password" class="w-full px-4 py-2 border border-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-amber-500" placeholder="Ketik sandi baru...">
                        <p class="text-xs text-amber-700 font-medium mt-1">*Kosongkan jika tidak ingin mengubah sandi.</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-6 rounded transition">Simpan Perubahan</button>
                    <a href="{{ url('/admin/data-nasabah') }}" class="text-slate-500 hover:text-slate-800 font-medium">Batal</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
