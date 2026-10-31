<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pegawai & Hak Akses - Bank Mini Sekolah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { scrollbar-gutter: stable; overflow-y: scroll; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pegawai & Hak Akses</h1>
                    <span class="text-xs font-medium text-slate-500">
                        ({{ $pegawai->total() }} Akun)
                    </span>
                </div>
                <p class="text-slate-500 text-sm mt-1">
                    Kelola kredensial petugas, hak akses RBAC (Teller, Supervisor, Admin, Avalis), dan status keaktifan akun.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/admin/dashboard') }}" class="px-3.5 py-2 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 transition shadow-sm">
                    &larr; Kembali ke Dashboard
                </a>
                <a href="{{ url('/admin/tambah-pegawai') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-slate-900 hover:bg-slate-800 text-white transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Pegawai Baru</span>
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        <!-- FILTER & PENCARIAN -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form action="{{ url('/admin/data-pegawai') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Nama / Username</label>
                    <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Ketik kata kunci..." class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jabatan / Role</label>
                    <select name="role" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none">
                        <option value="">Semua Peran</option>
                        <option value="teller" {{ request('role') === 'teller' ? 'selected' : '' }}>Teller</option>
                        <option value="supervisor" {{ request('role') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="customer_service" {{ request('role') === 'customer_service' ? 'selected' : '' }}>Customer Service</option>
                        <option value="accounting" {{ request('role') === 'accounting' ? 'selected' : '' }}>Accounting</option>
                        <option value="avalis" {{ request('role') === 'avalis' ? 'selected' : '' }}>Avalis</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status Akun</label>
                    <select name="status" class="w-full text-xs rounded-lg border border-slate-300 px-3 py-2 bg-white focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2 px-3 rounded-lg text-xs transition shadow-sm">
                        Filter
                    </button>
                    @if(request()->hasAny(['cari', 'role', 'status']))
                        <a href="{{ url('/admin/data-pegawai') }}" class="px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-600 text-xs rounded-lg transition font-medium">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABEL PEGAWAI -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Nama Petugas</th>
                            <th class="py-3 px-4">Username & Kontak</th>
                            <th class="py-3 px-4">Jabatan (Role)</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Status Akun</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($pegawai as $p)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    @php
                                        $initial = strtoupper(mb_substr(trim($p->nama_petugas), 0, 1));
                                    @endphp
                                    @if($p->foto_url)
                                        <img src="{{ $p->foto_url }}" alt="{{ $p->nama_petugas }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-xs shrink-0 shadow-sm">
                                            {{ $initial }}
                                        </div>
                                    @endif
                                    <div>
                                        <span class="font-bold text-slate-900 block">{{ $p->nama_petugas }}</span>
                                        <span class="text-[11px] text-slate-400">ID: #{{ $p->id }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono text-slate-800 font-medium block">{{ $p->username }}</span>
                                <span class="text-[11px] text-slate-500">{{ $p->no_hp ?? '-' }} &bull; {{ $p->email ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-xs font-medium text-slate-800">
                                    {{ ucfirst($p->role) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $p->kelas ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if(($p->status ?? 'aktif') === 'aktif')
                                    <span class="text-xs font-semibold text-emerald-700">Aktif</span>
                                @else
                                    <span class="text-xs font-semibold text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ url('/admin/edit-pegawai/'.$p->id) }}" class="inline-block border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 font-medium py-1 px-3 rounded-lg text-xs transition shadow-sm">
                                    Edit Akses
                                </a>
                                @if(Auth::id() != $p->id)
                                    <button type="button" onclick="openModalHapusPegawai('{{ $p->id }}', '{{ addslashes($p->nama_petugas) }}', '{{ addslashes($p->username) }}', '{{ ucfirst($p->role) }}')" class="inline-block border border-rose-200 bg-white text-rose-600 hover:bg-rose-50 font-medium py-1 px-3 rounded-lg text-xs transition shadow-sm">
                                        Blokir / Hapus
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                Tidak ada data pegawai yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pegawai->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $pegawai->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- MODAL KONFIRMASI HAPUS / BLOKIR PEGAWAI -->
    <div id="modalHapusPegawai" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 hidden p-4 overflow-y-auto" onclick="handlePegawaiBackdropClick(event)">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-md w-full overflow-hidden p-6 text-left transform transition-all" onclick="event.stopPropagation()">
                <div class="flex items-center space-x-3 text-rose-600 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Konfirmasi Hapus / Blokir Pegawai</h3>
                        <p class="text-xs text-slate-500">Tindakan ini memerlukan perhatian khusus</p>
                    </div>
                </div>

                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 mb-4 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Petugas:</span>
                        <strong class="text-slate-800" id="hapus_pegawai_nama">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Username:</span>
                        <span class="text-slate-800 font-mono font-medium" id="hapus_pegawai_user">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Hak Akses:</span>
                        <span class="text-slate-800 font-medium" id="hapus_pegawai_role">-</span>
                    </div>
                </div>

                <p class="text-xs text-rose-700 bg-rose-50 border border-rose-200 rounded-lg p-3 mb-5 leading-relaxed">
                    <strong>Peringatan Konsekuensi:</strong> Akun pegawai ini akan dihapus dari sistem dan seluruh sesi login akan ditutup. Pegawai tidak akan dapat mengakses sistem kembali. Tindakan ini dicatat dalam audit trail.
                </p>

                <form id="formHapusPegawai" method="GET" onsubmit="handleHapusPegawaiSubmit(this)">
                    <div class="flex items-center justify-end gap-2.5">
                        <button type="button" onclick="closeModalHapusPegawai()" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold rounded-lg text-xs transition">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitHapusPegawai" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs shadow-sm transition">
                            <span id="btnHapusPegawaiText">Ya, Hapus Pegawai</span>
                            <svg id="btnHapusPegawaiSpinner" class="w-3.5 h-3.5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModalHapusPegawai(id, nama, username, role) {
            document.getElementById('formHapusPegawai').action = "{{ url('/admin/hapus-pegawai') }}/" + id;
            document.getElementById('hapus_pegawai_nama').textContent = nama;
            document.getElementById('hapus_pegawai_user').textContent = username;
            document.getElementById('hapus_pegawai_role').textContent = role;
            document.getElementById('modalHapusPegawai').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
        function closeModalHapusPegawai() {
            document.getElementById('modalHapusPegawai').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
        function handlePegawaiBackdropClick(event) {
            if (event.target.id === 'modalHapusPegawai') {
                closeModalHapusPegawai();
            }
        }
        function handleHapusPegawaiSubmit(form) {
            const btn = document.getElementById('btnSubmitHapusPegawai');
            const spinner = document.getElementById('btnHapusPegawaiSpinner');
            const text = document.getElementById('btnHapusPegawaiText');
            btn.disabled = true;
            spinner.classList.remove('hidden');
            text.textContent = 'Memproses...';
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModalHapusPegawai();
            }
        });
    </script>
</body>
</html>

