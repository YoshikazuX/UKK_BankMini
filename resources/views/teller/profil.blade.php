<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - E-Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <x-navbar />

    <main class="max-w-2xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded shadow-sm font-bold">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="flex items-center space-x-6 mb-8 border-b border-slate-200 pb-6">
                <!-- Foto Profil -->
                <div class="relative">
                    @if($user->foto)
                        <img src="{{ asset('storage/'.$user->foto) }}" alt="Foto Profil" class="w-24 h-24 rounded-full object-cover border-4 border-slate-600 shadow-sm">
                    @else
                        <div class="w-24 h-24 rounded-full bg-slate-700 text-white flex items-center justify-center font-bold text-3xl border-4 border-slate-400 shadow-sm">
                            {{ strtoupper(substr($user->nama_petugas, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $user->nama_petugas }}</h1>
                    <p class="text-slate-500 text-sm mt-0.5">Username: <span class="font-medium text-slate-700">{{ $user->username }}</span></p>
                    <span class="inline-block bg-slate-100 text-slate-800 text-xs px-3 py-1 rounded-full font-bold mt-2 uppercase border border-slate-200">
                        Role: {{ $user->role }}
                    </span>
                </div>
            </div>

            <!-- Form Edit Mandiri (Hanya No HP & Foto) -->
            <form action="{{ url('/teller/profil/update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Nama (Terkunci / Disabled) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Nama Lengkap <span class="text-xs font-normal text-rose-500">(Hanya Admin yang dapat mengubah)</span></label>
                    <input type="text" value="{{ $user->nama_petugas }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                </div>

                <!-- Jabatan / Role (Terkunci / Disabled) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Jabatan Sistem <span class="text-xs font-normal text-rose-500">(Hanya Admin yang dapat mengubah)</span></label>
                    <input type="text" value="{{ ucfirst($user->role) }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                </div>

                <!-- Kelas (Terkunci / Disabled) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Kelas / Jurusan <span class="text-xs font-normal text-rose-500">(Hanya Admin yang dapat mengubah)</span></label>
                    <input type="text" value="{{ $user->kelas ?? 'Belum diatur' }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                </div>

                <!-- Nomor HP / Kontak (Bisa Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="no_hp">Nomor HP / WhatsApp Aktif</label>
                    <input type="text" name="no_hp" id="no_hp" value="{{ $user->no_hp }}" placeholder="Contoh: 081234567890" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <!-- Ganti Foto Profil (Bisa Diubah) -->
                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="foto">Unggah Foto Profil Baru</label>
                    <input type="file" name="foto" id="foto" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-slate-300 rounded-md">
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG. Maksimal ukuran 2MB.</p>
                </div>

                <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-6">
                    <a href="{{ url('/teller/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-4 rounded-lg shadow-sm transition text-sm">Batal</a>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition text-sm">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
