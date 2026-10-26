<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pegawai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <x-navbar />

    <main class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <div class="mb-6 border-b border-slate-200 pb-4">
                <h1 class="text-2xl font-bold text-slate-900">Edit Identitas & Jabatan Pegawai</h1>
                <p class="text-slate-600 text-sm mt-1">Ubah nama lengkap, jabatan, atau informasi kontak petugas.</p>
            </div>

            <form action="{{ url('/admin/edit-pegawai/'.$pegawai->id) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Nama Lengkap Petugas</label>
                    <input type="text" name="nama_petugas" value="{{ $pegawai->nama_petugas }}" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm" required>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Username Login</label>
                    <input type="text" value="{{ $pegawai->username }}" class="border border-slate-200 bg-slate-100 rounded-lg w-full py-2 px-3 text-slate-500 cursor-not-allowed text-sm" disabled>
                    <p class="text-xs text-slate-400 mt-1">*Username tidak dapat diubah.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Jabatan</label>
                    <select name="role" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm" required>
                        <option value="teller" {{ $pegawai->role == 'teller' ? 'selected' : '' }}>Teller</option>
                        <option value="supervisor" {{ $pegawai->role == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="admin" {{ $pegawai->role == 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Kelas (Khusus Murid)</label>
                    <input type="text" name="kelas" value="{{ $pegawai->kelas }}" placeholder="Contoh: XI RPL 1" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="no_hp" value="{{ $pegawai->no_hp }}" placeholder="Contoh: 081234567890" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm tabular-nums">
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Alamat Email <span class="text-xs text-slate-500">(Untuk pemulihan kata sandi)</span></label>
                    <input type="email" name="email" value="{{ $pegawai->email ?? '' }}" placeholder="Contoh: pegawai@banksekolah.id" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                </div>

                <div class="mb-8">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Kata Sandi Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin merubah sandi" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                </div>

                <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-6">
                    <a href="{{ url('/admin/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-4 rounded-lg text-sm transition shadow-sm">Batal</a>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-5 rounded-lg shadow-sm text-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
