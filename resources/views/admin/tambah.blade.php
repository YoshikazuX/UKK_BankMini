<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pegawai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">
    <x-navbar />

    <main class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <div class="mb-6 border-b border-slate-200 pb-4">
                <h1 class="text-2xl font-bold text-slate-900">Pendaftaran Petugas Baru</h1>
                <p class="text-slate-600 text-sm mt-1">Tambahkan akun khusus untuk Admin, Teller, atau Supervisor baru di sini.</p>
            </div>

            <!-- Pesan Error Validasi -->
            @if ($errors->any())
                <div class="bg-rose-100 border-l-4 border-rose-500 text-rose-700 p-4 mb-6 rounded-lg shadow-sm text-sm font-medium">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/admin/tambah-pegawai') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Nama Lengkap Petugas</label>
                    <input type="text" name="nama_petugas" class="border border-slate-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm" required>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Username Login</label>
                    <input type="text" name="username" class="border border-slate-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm" required>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Kata Sandi Login</label>
                    <input type="password" name="password" class="border border-slate-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm" required>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-semibold mb-2">Peran & Hak Akses (Role RBAC)</label>
                    <select name="role" class="border border-slate-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm bg-white" required>
                        <option value="">-- Pilih Peran / Role --</option>
                        <option value="teller" {{ old('role') === 'teller' ? 'selected' : '' }}>Teller (Pelayanan Loket & Transaksi)</option>
                        <option value="supervisor" {{ old('role') === 'supervisor' ? 'selected' : '' }}>Supervisor (Approval & Monitoring Kas)</option>
                        <option value="customer_service" {{ old('role') === 'customer_service' ? 'selected' : '' }}>Customer Service (Registrasi & Data Nasabah)</option>
                        <option value="avalis" {{ old('role') === 'avalis' ? 'selected' : '' }}>Avalis (Piket Shift & Kunjungan Siswa)</option>
                        <option value="accounting" {{ old('role') === 'accounting' ? 'selected' : '' }}>Accounting (Jurnal & Laporan Keuangan)</option>
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Tata Kelola Akun & Sistem)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Kelas <span class="text-xs text-slate-500">(Opsional jika Admin)</span></label>
                    <input type="text" name="kelas" placeholder="Contoh: XI RPL 1" class="border border-slate-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="no_hp" placeholder="Contoh: 081234567890" class="border border-slate-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm tabular-nums">
                </div>

                <div class="mb-8">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Alamat Email</label>
                    <input type="email" name="email" placeholder="Contoh: pegawai@banksekolah.id" class="border border-slate-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                </div>

                <div class="flex justify-end items-center space-x-3 border-t border-slate-200 pt-6">
                    <a href="{{ url('/admin/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-4 rounded-lg text-sm transition shadow-sm">Batal</a>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-5 rounded-lg shadow-sm text-sm transition">
                        Simpan Petugas
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
