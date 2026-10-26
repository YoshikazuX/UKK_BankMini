<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Supervisor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-2xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <div class="flex items-center space-x-6 mb-8 border-b border-slate-200 pb-6">
                <!-- FOTO PROFIL -->
                <div class="relative">
                    @if($user->foto)
                        <img src="{{ asset('storage/'.$user->foto) }}" alt="Foto Profil" class="w-20 h-20 rounded-full object-cover border-2 border-slate-300 shadow-sm">
                    @else
                        <div class="w-20 h-20 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold text-2xl border-2 border-slate-200 shadow-sm">
                            {{ strtoupper(substr($user->nama_petugas ?? 'S', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $user->nama_petugas }}</h1>
                    <p class="text-slate-500 text-sm mt-0.5">Username: <span class="font-medium text-slate-700">{{ $user->username }}</span></p>
                    <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2.5 py-0.5 rounded-full font-medium mt-2 border border-slate-200">
                        Role: {{ ucfirst($user->role) }}
                    </span>
                </div>
            </div>

            <form action="{{ url('/supervisor/profil/update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Nama Lengkap (Terkunci) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-semibold mb-2">Nama Lengkap <span class="text-xs font-normal text-rose-500">(Diatur oleh Admin)</span></label>
                    <input type="text" value="{{ $user->nama_petugas }}" class="border border-slate-200 bg-slate-50 rounded-lg w-full py-2 px-3 text-slate-500 cursor-not-allowed text-sm" disabled>
                </div>

                <!-- Jabatan (Terkunci) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-semibold mb-2">Jabatan <span class="text-xs font-normal text-rose-500">(Diatur oleh Admin)</span></label>
                    <input type="text" value="{{ strtoupper($user->role) }}" class="border border-slate-200 bg-slate-50 rounded-lg w-full py-2 px-3 text-slate-500 cursor-not-allowed text-sm" disabled>
                </div>

                <!-- Kelas (Terkunci) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-semibold mb-2">Kelas <span class="text-xs font-normal text-rose-500">(Diatur oleh Admin)</span></label>
                    <input type="text" value="{{ $user->kelas ?? 'Belum diatur' }}" class="border border-slate-200 bg-slate-50 rounded-lg w-full py-2 px-3 text-slate-500 cursor-not-allowed text-sm" disabled>
                </div>

                <!-- Nomor HP / Kontak (Bisa Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-semibold mb-2" for="no_hp">Nomor HP</label>
                    <input type="text" name="no_hp" id="no_hp" value="{{ $user->no_hp }}" placeholder="Contoh: 081234567890" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-700 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm tabular-nums">
                </div>

                <!-- Ganti Foto Profil (Bisa Diubah) -->
                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-semibold mb-2" for="foto">Unggah Foto Profil Baru</label>
                    <input type="file" name="foto" id="foto" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer border border-slate-300 rounded-lg">
                    <p class="text-xs text-slate-400 mt-1">Format: JPG, PNG. Maksimal 2MB.</p>
                </div>

                <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-6">
                    <a href="{{ url('/supervisor/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition">Batal</a>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-5 rounded-lg text-sm shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
