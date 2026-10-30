<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Administrator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <x-navbar />

    <main class="max-w-2xl mx-auto py-10 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-lg shadow-sm font-medium flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-center gap-2 font-semibold text-sm text-rose-900 mb-1">
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

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <div class="flex items-center space-x-6 mb-8 border-b border-slate-200 pb-6">
                <!-- FOTO PROFIL DENGAN PREVIEW INTERAKTIF -->
                <div class="relative shrink-0">
                    <div id="avatar-container" class="w-24 h-24 rounded-full overflow-hidden border-4 border-slate-700 shadow-sm relative bg-slate-100 flex items-center justify-center">
                        @if($user->foto)
                            <img id="avatar-preview" src="{{ asset('storage/'.$user->foto) }}" alt="Foto Profil" class="w-full h-full object-cover">
                        @else
                            <img id="avatar-preview" src="" alt="Foto Profil" class="w-full h-full object-cover hidden">
                            <div id="avatar-fallback" class="w-full h-full bg-slate-700 text-white flex items-center justify-center font-bold text-3xl">
                                {{ strtoupper(substr($user->nama_petugas ?? 'A', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">{{ $user->nama_petugas }}</h1>
                    <p class="text-slate-500 text-sm mt-0.5">Username: <span class="font-medium text-slate-700">{{ $user->username }}</span></p>
                    <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2.5 py-0.5 rounded-full font-medium mt-2 capitalize border border-slate-200">
                        {{ $user->role }}
                    </span>
                </div>
            </div>

            <form action="{{ url('/admin/profil/update') }}" method="POST" enctype="multipart/form-data" id="form-profil">
                @csrf

                <!-- Nama Lengkap (Terkunci) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Nama Lengkap</label>
                    <input type="text" value="{{ $user->nama_petugas }}" class="border border-slate-200 bg-slate-100 rounded-lg w-full py-2 px-3 text-slate-500 cursor-not-allowed text-sm" disabled>
                </div>

                <!-- Jabatan Sistem (Terkunci) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2">Jabatan Sistem</label>
                    <input type="text" value="{{ strtoupper($user->role) }}" class="border border-slate-200 bg-slate-100 rounded-lg w-full py-2 px-3 text-slate-500 cursor-not-allowed text-sm" disabled>
                </div>

                <!-- Nomor HP / Kontak (Bisa Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2" for="no_hp">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp', $user->no_hp) }}" placeholder="Contoh: 081234567890" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm tabular-nums">
                    @error('no_hp')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email (Bisa Diubah) -->
                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-medium mb-2" for="email">Alamat Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" placeholder="Contoh: admin@banksekolah.id" class="border border-slate-300 rounded-lg w-full py-2 px-3 text-slate-900 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-sm">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ganti Foto Profil (Hanya PNG & JPG, Proteksi Anti-Malware) -->
                <div class="mb-8">
                    <label class="block text-slate-700 text-xs font-semibold mb-2" for="foto">
                        Unggah Foto Profil Baru <span class="text-slate-400 font-normal">(PNG / JPG murni)</span>
                    </label>
                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        accept=".png, .jpg, .jpeg, image/png, image/jpeg"
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer border border-slate-300 rounded-lg @error('foto') border-rose-500 @enderror"
                    >
                    <div id="file-error" class="hidden text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1"></div>
                    @error('foto')
                        <p class="text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1">
                            <svg class="w-4 h-4 inline shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                    <div class="flex items-center gap-2 mt-2 text-xs text-slate-500">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ PNG / JPG Sah</span>
                        <span>Maksimal 2MB. Dilindungi pemindai sanitasi & anti-malware sistem.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 border-t border-slate-200 pt-6">
                    <a href="{{ url('/admin/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-4 rounded-lg text-sm transition shadow-sm">Batal</a>
                    <button type="submit" id="btn-submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-5 rounded-lg shadow-sm text-sm transition flex items-center gap-2">
                        <span>Simpan Perubahan</span>
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
        const btnSubmit = document.getElementById('btn-submit');

        fotoInput.addEventListener('change', function(e) {
            const file = this.files[0];
            fileError.classList.add('hidden');
            fileError.innerHTML = '';

            if (!file) return;

            // Validasi Ekstensi & MIME di sisi klien
            const allowedExtensions = ['png', 'jpg', 'jpeg'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            const allowedTypes = ['image/png', 'image/jpeg'];

            if (!allowedExtensions.includes(fileExt) || !allowedTypes.includes(file.type)) {
                fileError.innerHTML = `⚠️ <strong>Format Ditolak!</strong> File "<b>${file.name}</b>" bukan gambar PNG atau JPG yang sah. File selain PNG dan JPG dilarang keras untuk menjaga keamanan sistem.`;
                fileError.classList.remove('hidden');
                this.value = '';
                return;
            }

            // Validasi Ukuran (Maks 2MB = 2 * 1024 * 1024 bytes)
            const maxSize = 2 * 1024 * 1024;
            if (file.size > maxSize) {
                fileError.innerHTML = `⚠️ <strong>Ukuran Terlalu Besar!</strong> Ukuran file adalah ${(file.size / 1024 / 1024).toFixed(2)} MB. Maksimal ukuran yang diizinkan adalah 2 MB.`;
                fileError.classList.remove('hidden');
                this.value = '';
                return;
            }

            // Tampilkan Live Preview Gambar
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
