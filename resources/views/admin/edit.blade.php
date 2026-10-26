<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Akun Pegawai - LKM Mitra Siswa Abadi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen">

    <x-navbar />

    <main class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Edit Akun Pegawai</h1>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui data atau ubah password pegawai di bawah ini.</p>
            </div>
            <a href="{{ url('/admin/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs px-3 py-1.5 rounded-lg transition shadow-sm">
                Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 mb-6 rounded-xl text-xs shadow-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <form action="{{ url('/admin/edit-pegawai/'.$pegawai->id) }}" method="POST">
                @csrf

                <div class="mb-5">
                    <label class="block text-slate-700 text-xs font-semibold mb-1">Username Akun (Tidak dapat diubah)</label>
                    <input type="text" value="{{ $pegawai->username }}" class="bg-slate-100 border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-500 cursor-not-allowed" readonly>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-xs font-semibold mb-1" for="nama_petugas">Nama Lengkap Petugas</label>
                    <input type="text" name="nama_petugas" id="nama_petugas" value="{{ $pegawai->nama_petugas }}" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700" required>
                </div>

                <div class="mb-5">
                    <label class="block text-slate-700 text-xs font-semibold mb-1" for="password">Password Baru</label>
                    <input type="password" name="password" id="password" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700" placeholder="Kosongkan jika tidak ingin mengubah kata sandi">
                    <p class="text-[11px] text-slate-500 mt-1">*Hanya diisi jika pegawai ingin mengganti kata sandi.</p>
                </div>

                <div class="mb-8">
                    <label class="block text-slate-700 text-xs font-semibold mb-1" for="role">Jabatan (Role)</label>
                    <select name="role" id="role" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 bg-white" required>
                        <option value="teller" {{ $pegawai->role == 'teller' ? 'selected' : '' }}>Petugas Loket (Teller)</option>
                        <option value="supervisor" {{ $pegawai->role == 'supervisor' ? 'selected' : '' }}>Kepala / Supervisor</option>
                        <option value="admin" {{ $pegawai->role == 'admin' ? 'selected' : '' }}>Administrator (Admin)</option>
                    </select>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                    <a href="{{ url('/admin/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-xs px-4 py-2 rounded-lg transition">
                        Batal
                    </a>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium text-xs px-5 py-2 rounded-lg transition shadow-sm">
                        Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
