<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <!-- NAVBAR ADMIN -->
    <nav class="bg-slate-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider whitespace-nowrap shrink-0">LKM Mitra Siswa Abadi</div>
                <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white font-medium text-sm transition whitespace-nowrap focus:outline-none">&larr; Kembali ke Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="max-w-2xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded shadow-sm font-bold">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="flex items-center space-x-6 mb-8 border-b border-slate-200 pb-6">
                <!-- FOTO PROFIL -->
                <div class="relative">
                    @if($user->foto)
                        <img src="{{ asset('storage/'.$user->foto) }}" alt="Foto Profil" class="w-24 h-24 rounded-full object-cover border-4 border-slate-700 shadow-sm">
                    @else
                        <div class="w-24 h-24 rounded-full bg-slate-700 text-white flex items-center justify-center font-bold text-3xl border-4 border-slate-400 shadow-sm">
                            {{ strtoupper(substr($user->nama_petugas ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $user->nama_petugas }}</h1>
                    <p class="text-slate-500 text-sm mt-0.5">Username: <span class="font-mono text-slate-700">{{ $user->username }}</span></p>
                    <span class="inline-block bg-slate-100 text-slate-800 text-xs px-3 py-1 rounded-full font-bold mt-2 uppercase border border-slate-300">
                        Role: {{ $user->role }}
                    </span>
                </div>
            </div>

            <form action="{{ url('/admin/profil/update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Nama Lengkap (Terkunci) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nama Lengkap</label>
                    <input type="text" value="{{ $user->nama_petugas }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                </div>

                <!-- Jabatan Sistem (Terkunci) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Jabatan Sistem</label>
                    <input type="text" value="{{ strtoupper($user->role) }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                </div>

                <!-- Nomor HP / Kontak (Bisa Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="no_hp">Nomor HP / WhatsApp Aktif</label>
                    <input type="text" name="no_hp" id="no_hp" value="{{ $user->no_hp }}" placeholder="Contoh: 081234567890" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                </div>

                <!-- Email (Bisa Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="email">Alamat Email Resmi</label>
                    <input type="email" name="email" id="email" value="{{ $user->email ?? '' }}" placeholder="Contoh: admin@banksekolah.id" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-slate-500 focus:outline-none">
                </div>

                <!-- Ganti Foto Profil (Bisa Diubah) -->
                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="foto">Unggah Foto Profil Baru</label>
                    <input type="file" name="foto" id="foto" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer border border-slate-300 rounded-md">
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG. Maksimal 2MB.</p>
                </div>

                <div class="flex items-center justify-end space-x-4 border-t border-slate-200 pt-6">
                    <a href="{{ url('/admin/dashboard') }}" class="text-slate-500 hover:text-slate-700 font-medium text-sm">Batal</a>
                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-6 rounded-lg shadow transition">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
