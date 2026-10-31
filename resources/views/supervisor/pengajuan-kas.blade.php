<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Otorisasi Pengajuan Kas - Supervisor & Accounting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- NOTIFIKASI -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- HEADER & TOMBOL AKSI -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Otorisasi & Persetujuan Pengajuan Kas (Droping Kas)
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Kelola dan setujui permohonan penambahan uang tunai dari teller loket agar likuiditas kas operasional selalu terjaga tanpa angka minus.
                </p>
            </div>

            <!-- TOMBOL ATUR KAS AWAL HARI INI -->
            <div class="mt-4 md:mt-0 flex space-x-3">
                <button type="button" onclick="openModalKasAwal()"
                        class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Tetapkan Kas Awal Hari Ini
                </button>
            </div>
        </div>

        <!-- 3 KARTU METRIK KAS BRANKAS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Kas Awal Operasional</p>
                <p class="text-2xl font-bold text-slate-900 mt-1 tabular-nums">Rp {{ number_format($kasAwal, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Modal awal loket hari ini</p>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">Sisa Kas Fisik di Loket</p>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $kasTersedia <= 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
                        {{ $kasTersedia <= 0 ? 'Habis' : 'Tersedia' }}
                    </span>
                </div>
                <p class="text-3xl font-bold mt-2 tabular-nums text-slate-900">Rp {{ number_format($kasTersedia, 0, ',', '.') }}</p>
                <p class="text-xs mt-1 text-slate-400">Kas tunai yang dapat ditarik nasabah saat ini</p>
            </div>

            @php
                $pendingCount = $pengajuanList->where('status', 'pending')->count();
            @endphp
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">Menunggu Otorisasi</p>
                    @if($pendingCount > 0)
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                            Perlu Tindakan
                        </span>
                    @endif
                </div>
                <p class="text-3xl font-bold mt-2 tabular-nums text-slate-900">
                    {{ $pendingCount }} <span class="text-lg font-normal text-slate-500">Pengajuan</span>
                </p>
                <p class="text-xs mt-1 text-slate-400">Permintaan dana dari loket teller yang belum disetujui</p>
            </div>
        </div>

        <!-- FORM FILTER PENCARIAN -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <form action="{{ url('/supervisor/pengajuan-kas') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <select name="status" class="text-xs border border-slate-300 rounded-lg px-3 py-2 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>

                <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                       class="text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">

                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2 rounded-lg text-xs transition">
                    Filter
                </button>

                @if(request()->hasAny(['status', 'tanggal']))
                <a href="{{ url('/supervisor/pengajuan-kas') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-3 py-2 rounded-lg text-xs transition">
                    Reset
                </a>
                @endif
            </form>

            <span class="text-xs text-slate-500">Total: <strong class="tabular-nums text-slate-800">{{ $pengajuanList->total() }} Pengajuan</strong></span>
        </div>

        <!-- TABEL PENGAJUAN KAS TELLER -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="p-3">No. Pengajuan</th>
                            <th class="p-3">Petugas Teller</th>
                            <th class="p-3">Tanggal / Jam</th>
                            <th class="p-3 text-right">Dana Diajukan</th>
                            <th class="p-3">Alasan Permintaan Kas</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3">Catatan / Otorisator</th>
                            <th class="p-3 text-center">Tindakan Otorisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($pengajuanList as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 tabular-nums font-semibold text-slate-900">{{ $p->nomor_pengajuan }}</td>
                            <td class="p-3 font-semibold text-slate-900">
                                <span>{{ $p->nama_teller }}</span>
                                <span class="block text-[10px] text-slate-400 font-normal">Teller ID: #{{ $p->user_id }}</span>
                            </td>
                            <td class="p-3 tabular-nums text-slate-600">
                                {{ date('d/m/Y', strtotime($p->tanggal)) }}<br>
                                <span class="text-[10px] text-slate-400">{{ date('H:i', strtotime($p->created_at)) }} WIB</span>
                            </td>
                            <td class="p-3 text-right font-semibold tabular-nums text-slate-900 text-sm">
                                Rp {{ number_format($p->nominal, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-slate-700 max-w-xs">
                                {{ $p->alasan }}
                            </td>
                            <td class="p-3 text-center">
                                @if($p->status === 'disetujui')
                                    <span class="text-xs font-semibold text-emerald-700">Disetujui</span>
                                @elseif($p->status === 'ditolak')
                                    <span class="text-xs font-semibold text-rose-700">Ditolak</span>
                                @else
                                    <span class="text-xs font-semibold text-amber-700">Menunggu</span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-600 text-[11px]">
                                @if($p->nama_spv)
                                    <span class="font-semibold text-slate-800 block">{{ $p->nama_spv }}</span>
                                @endif
                                <span class="text-slate-500 italic">{{ $p->catatan_spv ?? '-' }}</span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Tombol Detail -->
                                    <a href="{{ route('supervisor.pengajuan-kas.detail', $p->id) }}" class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-2.5 py-1.5 rounded-lg transition text-xs shadow-sm flex items-center">
                                        Periksa
                                    </a>

                                    @if($p->status === 'pending')
                                        <!-- Tombol Setujui -->
                                        <form action="{{ url('/supervisor/pengajuan-kas/setujui/'.$p->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Setujui pengajuan kas sebesar Rp {{ number_format($p->nominal, 0, ',', '.') }}? Kas operasional loket akan otomatis bertambah.')">
                                            @csrf
                                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-2.5 py-1.5 rounded-lg transition text-xs shadow-sm flex items-center">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Setujui
                                            </button>
                                        </form>

                                        <!-- Tombol Tolak -->
                                        <button type="button" onclick="openModalTolak({{ $p->id }}, '{{ $p->nomor_pengajuan }}', {{ $p->nominal }})"
                                                class="bg-white hover:bg-slate-50 text-rose-700 border border-slate-300 font-medium px-2.5 py-1.5 rounded-lg transition text-xs">
                                            Tolak
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-400">
                                Tidak ada pengajuan kas yang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pengajuanList->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50">
                {{ $pengajuanList->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- MODAL TOLAK PENGAJUAN KAS -->
    <div id="modal-tolak" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-rose-900 text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-semibold" id="m-tolak-title">Tolak Pengajuan Kas</h3>
                <button type="button" onclick="closeModalTolak()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form id="form-tolak" method="POST" action="">
                @csrf
                <div class="p-6 space-y-4 text-xs">
                    <p class="text-slate-600" id="m-tolak-desc">Masukkan alasan penolakan permohonan dana kas ini:</p>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alasan Penolakan Supervisor * <span class="text-rose-600 font-normal">(Wajib diisi)</span></label>
                        <textarea name="alasan_penolakan" required minlength="5" rows="3" placeholder="Contoh: Kas fisik di loket saat ini dinilai masih mencukupi..."
                                  class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"></textarea>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModalTolak()" class="text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 px-5 py-2 rounded-lg shadow-sm transition">
                        Konfirmasi Tolak
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- MODAL ATUR KAS AWAL HARI INI -->
    <div id="modal-kas-awal" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
                <h3 class="text-base font-semibold">Tetapkan Kas Awal Operasional</h3>
                <button type="button" onclick="closeModalKasAwal()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form action="{{ url('/kas-awal/set') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Nominal Kas Awal Loket (Rp) *
                        </label>
                        <input type="number" name="saldo_awal" required min="0" value="{{ $kasAwal }}"
                               placeholder="Contoh: 1000000"
                               class="w-full text-base font-bold border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-slate-900 tabular-nums">
                        <p class="text-[11px] text-slate-400 mt-1">Modal awal pembukaan loket hari ini (tanggal <span class="tabular-nums">{{ date('d/m/Y') }}</span>).</p>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeModalKasAwal()" class="text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="text-xs font-medium text-white bg-slate-900 hover:bg-slate-800 px-5 py-2 rounded-lg shadow-sm transition">
                        Simpan Kas Awal
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <script>
        function openModalKasAwal() { document.getElementById('modal-kas-awal').classList.remove('hidden'); }
        function closeModalKasAwal() { document.getElementById('modal-kas-awal').classList.add('hidden'); }

        function openModalTolak(id, nomor, nominal) {
            document.getElementById('m-tolak-title').innerText = 'Tolak Pengajuan ' + nomor;
            document.getElementById('m-tolak-desc').innerText = 'Pengajuan dana sebesar Rp ' + new Intl.NumberFormat('id-ID').format(nominal) + ' akan ditolak.';
            document.getElementById('form-tolak').action = "{{ url('/supervisor/pengajuan-kas/tolak') }}/" + id;
            document.getElementById('modal-tolak').classList.remove('hidden');
        }
        function closeModalTolak() { document.getElementById('modal-tolak').classList.add('hidden'); }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModalKasAwal();
                closeModalTolak();
            }
        });

        function toggleDropdown() {
            const d = document.getElementById('profile-dropdown');
            if (d) d.classList.toggle('hidden');
        }
        window.addEventListener('click', function(e) {
            const btn = document.getElementById('profile-btn');
            const d = document.getElementById('profile-dropdown');
            if (btn && d && !btn.contains(e.target) && !d.contains(e.target)) {
                d.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
