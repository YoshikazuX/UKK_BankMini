<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Nasabah - LKM Mitra Siswa Abadi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen">

    <x-navbar />

    <main class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- HEADER HALAMAN -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Edit Data Nasabah</h1>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi profil dan identitas nasabah di bawah ini.</p>
            </div>
            <a href="{{ url('/admin/data-nasabah') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs px-3 py-1.5 rounded-lg transition shadow-sm">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 mb-6 rounded-xl text-xs">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <form action="{{ url('/admin/edit-nasabah/'.$nasabah->id) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                    <input type="text" value="{{ $nasabah->no_rekening ?? $nasabah->nis }}" class="bg-slate-100 border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-500 cursor-not-allowed tabular-nums" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_siswa" value="{{ old('nama_siswa', $nasabah->nama_siswa) }}" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700" required>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kelas</label>
                        <input type="text" name="kelas" value="{{ old('kelas', $nasabah->kelas) }}" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jurusan</label>
                        <input type="text" name="jurusan" value="{{ old('jurusan', $nasabah->jurusan) }}" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700" required>
                    </div>
                </div>

                <div class="flex justify-end items-center gap-2 pt-4 border-t border-slate-100">
                    <a href="{{ url('/admin/data-nasabah') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs px-4 py-2 rounded-lg transition">
                        Batal
                    </a>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs px-4 py-2 rounded-lg transition shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
