<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pegawai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <nav class="bg-slate-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider whitespace-nowrap shrink-0">LKM Mitra Siswa Abadi</div>
                <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white text-sm font-medium transition whitespace-nowrap focus:outline-none">&larr; Kembali ke Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="mb-6 border-b border-slate-200 pb-4">
                <h1 class="text-2xl font-bold text-slate-800">Edit Identitas & Jabatan Pegawai</h1>
                <p class="text-slate-600 text-sm mt-1">Menu Admin untuk mengubah Nama Lengkap, Jabatan (Role), dan Kelas Petugas.</p>
            </div>

            <form action="{{ url('/admin/edit-pegawai/'.$pegawai->id) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nama Lengkap Petugas</label>
                    <input type="text" name="nama_petugas" value="{{ $pegawai->nama_petugas }}" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Username Login</label>
                    <input type="text" value="{{ $pegawai->username }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                    <p class="text-xs text-slate-400 mt-1">*Username tidak dapat diubah.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Jabatan</label>
                    <select name="role" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none" required>
                        <option value="teller" {{ $pegawai->role == 'teller' ? 'selected' : '' }}>Teller (Kasir)</option>
                        <option value="supervisor" {{ $pegawai->role == 'supervisor' ? 'selected' : '' }}>Supervisor (Atasan)</option>
                        <option value="admin" {{ $pegawai->role == 'admin' ? 'selected' : '' }}>Admin (Pengelola Sistem)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Kelas (Khusus Murid)</label>
                    <input type="text" name="kelas" value="{{ $pegawai->kelas }}" placeholder="Contoh: XI RPL 1" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nomor WhatsApp / HP</label>
                    <input type="text" name="no_hp" value="{{ $pegawai->no_hp }}" placeholder="Contoh: 081234567890" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Alamat Email <span class="text-xs font-normal text-slate-500">(Untuk pemulihan kata sandi)</span></label>
                    <input type="email" name="email" value="{{ $pegawai->email ?? '' }}" placeholder="Contoh: pegawai@banksekolah.id" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                </div>

                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Reset Password Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin merubah sandi" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-end space-x-4 border-t border-slate-200 pt-6">
                    <a href="{{ url('/admin/dashboard') }}" class="text-slate-500 hover:text-slate-700 font-medium text-sm">Batal</a>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded shadow transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
