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
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded shadow-sm font-bold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded shadow-sm">
            <div class="flex items-center gap-2 font-bold text-sm text-rose-900 mb-1">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <span>Terjadi Kesalahan Validasi:</span>
            </div>
            <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5 ml-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="flex items-center space-x-6 mb-8 border-b border-slate-200 pb-6">
                <!-- Foto Profil DENGAN PREVIEW -->
                <div class="relative shrink-0">
                    <div id="avatar-container" class="w-24 h-24 rounded-full overflow-hidden border-4 border-slate-600 shadow-sm relative bg-slate-100 flex items-center justify-center">
                        @if($user->foto)
                            <img id="avatar-preview" src="{{ asset('storage/'.$user->foto) }}" alt="Foto Profil" class="w-full h-full object-cover">
                        @else
                            <img id="avatar-preview" src="" alt="Foto Profil" class="w-full h-full object-cover hidden">
                            <div id="avatar-fallback" class="w-full h-full bg-slate-700 text-white flex items-center justify-center font-bold text-3xl">
                                {{ strtoupper(substr($user->nama_petugas, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">{{ $user->nama_petugas }}</h1>
                    <p class="text-slate-500 text-sm mt-0.5">Username: <span class="font-medium text-slate-700">{{ $user->username }}</span></p>
                    <p class="text-xs text-slate-500 font-medium mt-1">Role: <strong class="text-slate-800 uppercase">{{ $user->role }}</strong></p>
                </div>
            </div>

            <!-- Form Edit Mandiri (Hanya No HP & Foto) -->
            <form action="{{ url('/teller/profil/update') }}" method="POST" enctype="multipart/form-data" id="form-profil">
                @csrf

                <!-- Nama (Dapat Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="nama_petugas">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_petugas" id="nama_petugas" value="{{ old('nama_petugas', $user->nama_petugas) }}" required class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    @error('nama_petugas')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jabatan / Role (Terkunci / Disabled) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Jabatan Sistem <span class="text-xs font-normal text-slate-400">(Terkunci)</span></label>
                    <input type="text" value="{{ ucfirst($user->role) }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                </div>

                <!-- Kelas (Terkunci / Disabled) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Kelas / Jurusan <span class="text-xs font-normal text-slate-400">(Terkunci)</span></label>
                    <input type="text" value="{{ $user->kelas ?? 'Belum diatur' }}" class="shadow-sm border border-slate-200 bg-slate-100 rounded w-full py-2.5 px-3 text-slate-500 cursor-not-allowed" disabled>
                </div>

                <!-- Alamat Email (Dapat Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" placeholder="Contoh: teller@banksekolah.id" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nomor HP / Kontak (Bisa Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="no_hp">Nomor HP / WhatsApp Aktif</label>
                    <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $user->no_hp) }}" placeholder="Contoh: 081234567890" class="shadow-sm border border-slate-300 rounded w-full py-2.5 px-3 text-slate-700 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    @error('no_hp')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ganti Foto Profil (Bisa Diubah) -->
                <div class="mb-8">
                    <label class="block text-slate-700 text-sm font-bold mb-2" for="foto">
                        Unggah Foto Profil Baru <span class="text-slate-400 font-normal text-xs">(Hanya format PNG & JPG)</span>
                    </label>
                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        accept=".png, .jpg, .jpeg, image/png, image/jpeg"
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer border border-slate-300 rounded-md"
                    >
                    <div id="file-error" class="hidden text-xs text-rose-600 font-bold mt-1.5"></div>
                    @error('foto')
                        <p class="text-xs text-rose-600 font-bold mt-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4 inline shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                    <div class="flex items-center gap-2 mt-2 text-xs text-slate-500">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ PNG / JPG Sah</span>
                        <span>Maksimal ukuran 2MB. Terlindungi sistem pemindaian keamanan biner.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-6">
                    <a href="{{ url('/teller/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-4 rounded-lg shadow-sm transition text-sm">Batal</a>
                    <button type="submit" id="btn-submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition text-sm">
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const fotoInput = document.getElementById('foto');
        const previewImg = document.getElementById('avatar-preview');
        const fallbackAvatar = document.getElementById('avatar-fallback');
        const fileError = document.getElementById('file-error');

        fotoInput.addEventListener('change', function(e) {
            const file = this.files[0];
            fileError.classList.add('hidden');
            fileError.innerHTML = '';

            if (!file) return;

            // Validasi Ekstensi & MIME
            const allowedExtensions = ['png', 'jpg', 'jpeg'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            const allowedTypes = ['image/png', 'image/jpeg'];

            if (!allowedExtensions.includes(fileExt) || !allowedTypes.includes(file.type)) {
                fileError.innerHTML = `⚠️ <strong>Format Ditolak!</strong> File "<b>${file.name}</b>" bukan gambar PNG atau JPG yang sah. Selain format PNG dan JPG tidak diizinkan.`;
                fileError.classList.remove('hidden');
                this.value = '';
                return;
            }

            // Validasi Ukuran (Maks 2MB)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                fileError.innerHTML = `⚠️ <strong>Ukuran Terlalu Besar!</strong> Ukuran file adalah ${(file.size / 1024 / 1024).toFixed(2)} MB. Maksimal ukuran 2 MB.`;
                fileError.classList.remove('hidden');
                this.value = '';
                return;
            }

            // Live Preview
            const reader = new FileReader();
            reader.onload = function(evt) {
                previewImg.src = evt.target.result;
                previewImg.classList.remove('hidden');
                if (fallbackAvatar) fallbackAvatar.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>
