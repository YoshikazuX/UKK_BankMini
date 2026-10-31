<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat & Koreksi Transaksi - E-Teller</title>
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

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 p-4 mb-6 rounded-r-xl shadow-sm text-sm font-medium flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-emerald-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 font-bold ml-3">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-900 p-4 mb-6 rounded-r-xl shadow-sm text-sm font-medium flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-rose-600 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 font-bold ml-3">&times;</button>
        </div>
        @endif

        <!-- HEADER & DESKRIPSI -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat & Jurnal Transaksi Loket</h1>
                <p class="text-slate-500 text-sm mt-1">Daftar mutasi transaksi tunai yang dilayani oleh Anda. Lengkap dengan riwayat koreksi dan pembatalan (Void).</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/teller/dashboard') }}" class="inline-flex items-center text-xs font-semibold px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg shadow-sm transition">
                    + Transaksi Baru
                </a>
            </div>
        </div>

        <!-- TAB NAVIGASI: SEMUA vs RIWAYAT VOID & KOREKSI -->
        <div class="flex border-b border-slate-200 mb-6 space-x-6 text-sm font-semibold">
            <a href="{{ url('/teller/riwayat') }}?tab=semua" class="pb-3 border-b-2 flex items-center gap-2 {{ ($tab ?? 'semua') === 'semua' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <span>Semua Transaksi</span>
            </a>
            <a href="{{ url('/teller/riwayat') }}?tab=void" class="pb-3 border-b-2 flex items-center gap-2 {{ ($tab ?? 'semua') === 'void' ? 'border-rose-600 text-rose-700' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <span>Riwayat Void & Koreksi (Reversal)</span>
            </a>
        </div>

        <!-- FORM FILTER KOMPREHENSIF -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6">
            <form action="{{ url('/teller/riwayat') }}" method="GET" class="space-y-4">
                <input type="hidden" name="tab" value="{{ $tab ?? 'semua' }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Rentang Tanggal Mulai -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                    </div>

                    <!-- Rentang Tanggal Akhir -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                    </div>

                    <!-- Jenis Transaksi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Transaksi</label>
                        <select name="jenis_transaksi" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                            <option value="semua">Semua Jenis Transaksi</option>
                            <option value="setor" {{ request('jenis_transaksi') === 'setor' ? 'selected' : '' }}>Setoran Tunai [101]</option>
                            <option value="tarik" {{ request('jenis_transaksi') === 'tarik' ? 'selected' : '' }}>Penarikan Tunai [201]</option>
                            <option value="koreksi_setor" {{ request('jenis_transaksi') === 'koreksi_setor' ? 'selected' : '' }}>Koreksi Setoran [102]</option>
                            <option value="koreksi_tarik" {{ request('jenis_transaksi') === 'koreksi_tarik' ? 'selected' : '' }}>Koreksi Penarikan [202]</option>
                            <option value="admin" {{ request('jenis_transaksi') === 'admin' ? 'selected' : '' }}>Biaya Administrasi [301]</option>
                        </select>
                    </div>

                    <!-- Status Transaksi -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Status Transaksi</label>
                        <select name="status" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                            <option value="semua">Semua Status</option>
                            <option value="sukses" {{ request('status') === 'sukses' ? 'selected' : '' }}>Berhasil (Sukses)</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu Closing)</option>
                            <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                        </select>
                    </div>
                </div>

                <!-- Baris Pencarian Kata Kunci & Tombol Aksi -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                    <div class="w-full sm:w-80">
                        <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama nasabah, NIS, no rekening, #TRX..." class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                    </div>

                    <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-4 py-2 rounded-lg text-xs transition shadow-sm">
                            Terapkan Filter
                        </button>
                        <a href="{{ url('/teller/riwayat') }}{{ ($tab ?? 'semua') === 'void' ? '?tab=void' : '' }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2 rounded-lg text-xs transition shadow-sm">
                            Reset Filter
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- STATISTIK RINGKASAN HASIL FILTER -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Jumlah Transaksi Sesuai Filter</p>
                    <p class="text-xl font-bold text-slate-900 mt-0.5 tabular-nums">{{ number_format($totalFiltered ?? count($riwayat), 0, ',', '.') }} Transaksi</p>
                </div>
                <span class="p-2 bg-slate-100 text-slate-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500">Total Akumulasi Nominal (Aktif)</p>
                    <p class="text-xl font-bold text-slate-900 mt-0.5 tabular-nums">Rp {{ number_format($totalNominal ?? 0, 0, ',', '.') }}</p>
                </div>
                <span class="p-2 bg-slate-100 text-slate-700 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
        </div>

        <!-- TABEL RIWAYAT TRANSAKSI TELLER -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">ID Transaksi</th>
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">Nomor Rekening</th>
                            <th class="py-3 px-4">Nama Nasabah</th>
                            <th class="py-3 px-4">Jenis & Sandi</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Keterangan / Detail Pembatalan</th>
                            <th class="py-3 px-4 text-center">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($riwayat as $r)
                        <tr class="hover:bg-slate-50 transition {{ $r->status === 'dibatalkan' ? 'bg-rose-50/30' : '' }}">
                            <!-- ID Transaksi -->
                            <td class="py-3 px-4 font-bold text-slate-900 tabular-nums">
                                #TRX-{{ str_pad($r->id, 5, '0', STR_PAD_LEFT) }}
                                @if($r->ref_transaksi_id)
                                    <div class="text-[10px] text-slate-500 font-normal">
                                        Ref: #TRX-{{ str_pad($r->ref_transaksi_id, 5, '0', STR_PAD_LEFT) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Tanggal & Waktu -->
                            <td class="py-3 px-4 text-slate-600 tabular-nums whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y') }}
                                <span class="text-slate-400 block text-[11px]">{{ \Carbon\Carbon::parse($r->created_at)->format('H:i:s') }} WIB</span>
                            </td>

                            <!-- Nomor Rekening -->
                            <td class="py-3 px-4 font-semibold text-slate-900 tabular-nums">
                                {{ $r->nasabah?->no_rekening ?? '-' }}
                                <span class="text-slate-400 block text-[11px] font-normal">NIS: {{ $r->nasabah?->nis ?? '-' }}</span>
                            </td>

                            <!-- Nama Nasabah -->
                            <td class="py-3 px-4 font-medium text-slate-900">
                                {{ $r->nasabah?->nama_siswa ?? $r->nama_siswa ?? '-' }}
                                <span class="text-slate-400 block text-[11px] font-normal">{{ $r->nasabah?->kelas ?? '-' }}</span>
                            </td>

                            <!-- Jenis Transaksi & Sandi -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-800 capitalize">
                                        {{ match($r->jenis_transaksi) {
                                            'setor' => 'Setor Tunai',
                                            'tarik' => 'Tarik Tunai',
                                            'koreksi_setor' => 'Koreksi Setor',
                                            'koreksi_tarik' => 'Koreksi Tarik',
                                            'admin' => 'Biaya Admin',
                                            default => $r->jenis_transaksi
                                        } }}
                                    </span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                        [{{ $r->kode_sandi ?? '-' }}]
                                    </span>
                                </div>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Layanan: {{ $r->jenis_layanan ?? 'Tabungan' }}</span>
                            </td>

                            <!-- Nominal -->
                            <td class="py-3 px-4 text-right font-extrabold tabular-nums {{ in_array($r->jenis_transaksi, ['setor', 'koreksi_tarik']) ? 'text-emerald-700' : 'text-rose-700' }}">
                                Rp {{ number_format($r->nominal, 0, ',', '.') }}
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 text-center">
                                @if($r->status === 'dibatalkan')
                                    <span class="text-xs font-semibold text-rose-700">Dibatalkan</span>
                                @elseif($r->status === 'sukses')
                                    <span class="text-xs font-semibold text-emerald-700">Berhasil</span>
                                @else
                                    <span class="text-xs font-semibold text-amber-700 capitalize">{{ $r->status }}</span>
                                @endif
                            </td>

                            <!-- Keterangan / Detail Pembatalan -->
                            <td class="py-3 px-4 text-xs">
                                @if($r->status === 'dibatalkan')
                                    <div class="text-rose-700 font-semibold">
                                        Alasan Batal: <span class="font-normal">{{ $r->alasan_batal ?: '-' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        Dibatalkan: {{ $r->dibatalkan_at ? $r->dibatalkan_at->format('d/m/Y H:i') : '-' }}
                                        @if($r->petugasPembatal)
                                            oleh {{ $r->petugasPembatal->nama_petugas }}
                                        @endif
                                    </div>
                                @elseif($r->alasan_batal)
                                    <span class="text-slate-600">{{ $r->alasan_batal }}</span>
                                @elseif($r->keterangan)
                                    <span class="text-slate-600">{{ $r->keterangan }}</span>
                                @else
                                    <span class="text-slate-400 italic">Transaksi operasional reguler</span>
                                @endif
                            </td>

                            <!-- Tindakan -->
                            <td class="py-3 px-4 text-center whitespace-nowrap space-x-1">
                                <!-- Cetak Slip -->
                                @if(in_array($r->jenis_transaksi, ['setor', 'koreksi_tarik']))
                                    <a href="{{ url('/teller/slip-setor/'.$r->id) }}" target="_blank" class="inline-block bg-white hover:bg-slate-50 text-slate-700 font-medium py-1.5 px-2.5 rounded-lg text-xs transition border border-slate-300 shadow-sm" title="Cetak Slip Setoran">
                                        Slip
                                    </a>
                                @else
                                    <a href="{{ url('/teller/slip-tarik/'.$r->id) }}" target="_blank" class="inline-block bg-white hover:bg-slate-50 text-slate-700 font-medium py-1.5 px-2.5 rounded-lg text-xs transition border border-slate-300 shadow-sm" title="Cetak Slip Penarikan">
                                        Slip
                                    </a>
                                @endif

                                <!-- Tombol Void (Hanya jika belum dibatalkan, bukan transaksi koreksi, dan transaksi dari hari ini) -->
                                @php
                                    $isToday = $r->created_at->isToday();
                                    $isReversal = in_array($r->jenis_transaksi, ['koreksi_setor', 'koreksi_tarik']);
                                    $isEligibleVoid = ($r->status !== 'dibatalkan') && ! $isReversal && $isToday;
                                @endphp

                                @if($isEligibleVoid)
                                    <button onclick="bukaModalVoid({{ $r->id }}, '{{ addslashes($r->nasabah?->nama_siswa ?? $r->nama_siswa ?? 'Nasabah') }}', '{{ $r->jenis_transaksi }}', 'Rp {{ number_format($r->nominal, 0, ',', '.') }}')" class="bg-white hover:bg-slate-50 text-rose-700 font-medium py-1.5 px-2.5 rounded-lg text-xs transition shadow-sm border border-slate-300">
                                        Void
                                    </button>
                                @elseif($r->status === 'dibatalkan')
                                    <span class="text-[11px] text-slate-400 italic">Telah Di-Void</span>
                                @elseif($isReversal)
                                    <span class="text-[11px] text-slate-400 italic">Koreksi</span>
                                @else
                                    <span class="text-[11px] text-slate-400 italic" title="Hanya transaksi hari ini yang dapat di-void sebelum tutup buku">Terkunci</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <div class="max-w-sm mx-auto">
                                    <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="font-medium text-slate-600 text-sm">Tidak ada transaksi yang ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian, rentang tanggal, atau filter jenis transaksi.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($riwayat->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
                {{ $riwayat->links() }}
            </div>
            @endif
        </div>
    </main>

    <!-- POP-UP MODAL VOID TRANSAKSI RESMI -->
    <div id="modal-void" class="hidden fixed inset-0 bg-slate-900/80 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
                <div class="bg-slate-900 px-6 py-4 border-b border-slate-800 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold">Koreksi & Pembatalan (Void)</h3>
                        <p class="text-xs text-slate-400 mt-0.5">SOP Pembatalan Transaksi Finansial Teller</p>
                    </div>
                    <span class="w-8 h-8 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-xs">
                        !
                    </span>
                </div>

                <form id="form-void" method="POST" class="p-6" onsubmit="handleVoidSubmit(this)">
                    @csrf
                    <div class="mb-4 bg-rose-50 p-3.5 rounded-xl border border-rose-200 text-xs">
                        <p class="text-rose-900 font-bold mb-1">Rincian Transaksi Yang Dibatalkan:</p>
                        <p id="void-info" class="text-rose-800 font-medium"></p>
                        <p class="text-rose-600 text-[11px] mt-2 font-normal leading-relaxed">
                            Konsekuensi: Saldo nasabah akan dibalikkan, kas fisik loket disesuaikan, mutasi pembalik otomatis dibuat, dan transaksi tercatat di audit log.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-slate-700 text-xs font-semibold mb-1">Alasan Pembatalan <span class="text-rose-500">* (Wajib Audit)</span></label>
                        <input type="text" name="alasan_batal" placeholder="Contoh: Salah ketik nominal nasabah / uang nasabah robek" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-xs" required autocomplete="off">
                    </div>

                    <div class="mb-6">
                        <label class="block text-slate-700 text-xs font-semibold mb-1">PIN Otorisasi Supervisor <span class="text-rose-500 font-normal">(Wajib disahkan atasan)</span></label>
                        <input type="password" name="pin_supervisor" placeholder="Masukkan 6 Digit PIN" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-center tracking-widest text-lg font-bold tabular-nums" required>
                        <p class="text-[11px] text-slate-400 mt-1">*PIN supervisor pengesahan: 123456</p>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="tutupModalVoid()" class="w-1/3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2 rounded-lg transition text-xs">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-void" class="w-2/3 inline-flex items-center justify-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 rounded-lg shadow-sm transition text-xs">
                            <span id="btn-void-text">Eksekusi Void Transaksi</span>
                            <svg id="btn-void-spinner" class="w-3.5 h-3.5 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaModalVoid(id, nama, jenis, nominal) {
            document.getElementById('form-void').action = "{{ url('/teller/transaksi/void') }}/" + id;
            document.getElementById('void-info').innerText = "#TRX-" + String(id).padStart(5, '0') + " | " + nama + " | " + jenis.toUpperCase() + " | " + nominal;
            document.getElementById('modal-void').classList.remove('hidden');
        }

        function tutupModalVoid() {
            document.getElementById('modal-void').classList.add('hidden');
        }

        function handleVoidSubmit(form) {
            const btn = document.getElementById('btn-submit-void');
            const spinner = document.getElementById('btn-void-spinner');
            const text = document.getElementById('btn-void-text');
            if (btn && spinner && text) {
                btn.disabled = true;
                spinner.classList.remove('hidden');
                text.textContent = 'Memproses Void...';
            }
        }
    </script>
</body>
</html>
