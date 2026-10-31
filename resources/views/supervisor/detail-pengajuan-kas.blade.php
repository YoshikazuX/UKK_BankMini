<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan Kas #{{ $pengajuan->nomor_pengajuan }} - Supervisor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- NOTIFIKASI -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('error') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
        </div>
        @endif

        <!-- HEADER & BREADCRUMB -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('supervisor.approval-center') }}" class="hover:text-indigo-600">&larr; Kembali ke Pusat Persetujuan</a>
                    <span>&bull;</span>
                    <a href="{{ route('supervisor.pengajuan-kas') }}" class="hover:text-indigo-600">Daftar Pengajuan Kas</a>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                        Detail Pengajuan Droping Kas
                    </h1>
                    <span class="text-xs font-semibold {{ $pengajuan->status === 'disetujui' ? 'text-emerald-700' : ($pengajuan->status === 'ditolak' ? 'text-rose-700' : 'text-amber-700') }}">
                        {{ ucfirst($pengajuan->status) }}
                    </span>
                </div>
                <p class="text-slate-500 text-xs mt-1">
                    Nomor Referensi: <strong class="text-slate-900 tabular-nums">{{ $pengajuan->nomor_pengajuan }}</strong> &bull; Diajukan pada {{ $pengajuan->created_at->format('d F Y, H:i') }} WIB
                </p>
            </div>

            <!-- TOMBOL AKSI ATAS -->
            <div class="flex items-center space-x-2">
                @if($pengajuan->status === 'pending')
                    <button type="button" onclick="openModalSetujui()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-xs shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Setujui Pengajuan Kas
                    </button>
                    <button type="button" onclick="openModalTolak()" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-lg text-xs shadow-sm transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak Pengajuan Kas
                    </button>
                @else
                    <div class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200">
                        Diproses oleh: {{ $pengajuan->approver?->nama_petugas ?? 'Supervisor' }} pada {{ $pengajuan->updated_at->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- KOLOM KIRI (2 KOLOM): INFORMASI DETAIL PENGAJUAN & KONDISI KAS TELLER -->
            <div class="lg:col-span-2 space-y-6">
                <!-- 1. KARTU INFORMASI PENGAJUAN DANA -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100">
                        Informasi Permohonan Droping Kas
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block mb-0.5">Petugas Teller Pemohon:</span>
                            <span class="font-bold text-slate-900 text-sm">{{ $teller->nama_petugas ?? '-' }}</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Username: {{ $teller->username ?? '-' }} &bull; Kelas: {{ $teller->kelas ?? '-' }}</span>
                        </div>

                        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block mb-0.5">Nominal Kas yang Diminta:</span>
                            <span class="font-black text-indigo-700 text-lg tabular-nums">Rp {{ number_format($pengajuan->nominal, 0, ',', '.') }}</span>
                            <span class="block text-[10px] text-slate-400 mt-0.5">{{ \App\Helpers\TerbilangHelper::terbilang($pengajuan->nominal) }} rupiah</span>
                        </div>

                        <div class="col-span-2 bg-slate-50 p-3.5 rounded-lg border border-slate-100">
                            <span class="text-slate-500 block mb-1">Alasan Pengajuan Tambahan Kas dari Teller:</span>
                            <p class="text-slate-800 text-sm font-medium leading-relaxed bg-white p-3 rounded border border-slate-200">
                                "{{ $pengajuan->alasan }}"
                            </p>
                        </div>

                        @if($pengajuan->catatan_spv)
                        <div class="col-span-2 {{ $pengajuan->status === 'ditolak' ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900' }} p-3.5 rounded-lg border">
                            <span class="font-bold block mb-1">Catatan Pemeriksaan Supervisor:</span>
                            <p class="text-xs leading-relaxed font-medium">
                                "{{ $pengajuan->catatan_spv }}"
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 2. KARTU EVALUASI KONDISI KAS & AKTIVITAS TRANSAKSI TELLER HARI INI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                        <span>Evaluasi Likuiditas Kas Teller pada Tanggal Pengajuan</span>
                        <span class="text-xs font-normal text-slate-500 tabular-nums">
                            {{ $pengajuan->tanggal ? $pengajuan->tanggal->format('d F Y') : '-' }}
                        </span>
                    </h2>

                    <!-- 4 METRIK KAS TELLER -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5 text-xs">
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200/70">
                            <span class="text-slate-500 block">Saldo Awal Loket:</span>
                            <span class="font-bold text-slate-900 text-sm tabular-nums mt-0.5 block">Rp {{ number_format($kasAwalLoket, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-400">Modal buka loket</span>
                        </div>
                        <div class="bg-emerald-50 p-3 rounded-lg border border-emerald-100 text-emerald-900">
                            <span class="text-emerald-700 block">Total Setoran Masuk:</span>
                            <span class="font-bold text-sm tabular-nums mt-0.5 block">Rp {{ number_format($totalSetoranMasuk, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-emerald-600">{{ $jumlahSetoran }} transaksi masuk</span>
                        </div>
                        <div class="bg-rose-50 p-3 rounded-lg border border-rose-100 text-rose-900">
                            <span class="text-rose-700 block">Total Penarikan Keluar:</span>
                            <span class="font-bold text-sm tabular-nums mt-0.5 block">Rp {{ number_format($totalPenarikanKeluar, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-rose-600">{{ $jumlahPenarikan }} penarikan nasabah</span>
                        </div>
                        <div class="bg-indigo-50 p-3 rounded-lg border border-indigo-100 text-indigo-900">
                            <span class="text-indigo-700 block">Estimasi Kas Laci:</span>
                            <span class="font-black text-sm tabular-nums mt-0.5 block">Rp {{ number_format($estimasiKasLaciTeller, 0, ',', '.') }}</span>
                            <span class="text-[10px] {{ $estimasiKasLaciTeller <= 0 ? 'text-rose-600 font-bold' : 'text-indigo-600' }}">
                                {{ $estimasiKasLaciTeller <= 0 ? 'Kas Kritis / Habis' : 'Posisi saat ini' }}
                            </span>
                        </div>
                    </div>

                    <!-- REKOMENDASI EVALUASI SUPERVISOR -->
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 text-xs">
                        <h3 class="font-bold text-slate-800 mb-1">Rekomendasi Analisis Kelayakan Droping:</h3>
                        <p class="text-slate-600 leading-relaxed">
                            @if($estimasiKasLaciTeller < 100000)
                                <span class="text-rose-600 font-semibold">Kas di loket teller dinilai menipis/habis (Rp {{ number_format($estimasiKasLaciTeller, 0, ',', '.') }}).</span> 
                                Permohonan penambahan modal kerja sebesar <strong>Rp {{ number_format($pengajuan->nominal, 0, ',', '.') }}</strong> sangat direkomendasikan untuk disetujui guna menjamin kelancaran penarikan tabungan siswa berikutnya.
                            @else
                                <span class="text-slate-700">Kas loket saat ini tercatat sebesar <strong>Rp {{ number_format($estimasiKasLaciTeller, 0, ',', '.') }}</strong>.</span> 
                                Periksa apakah ada antrean rencana penarikan tunai bernilai besar sebelum memberikan persetujuan penambahan dana kas.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- 3. TABEL RIWAYAT PENGAJUAN KAS LAIN DARI TELLER INI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                        <span>Riwayat Pengajuan Kas Sebelumnya (Oleh Teller Ini)</span>
                        <span class="text-xs text-slate-400 font-normal">Maksimal 10 riwayat terakhir</span>
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-3">No. Pengajuan</th>
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Pemeriksa / Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($riwayatSebelumnya as $r)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2 px-3 font-semibold text-slate-900">{{ $r->nomor_pengajuan }}</td>
                                    <td class="py-2 px-3 tabular-nums text-slate-500">{{ $r->tanggal ? $r->tanggal->format('d/m/Y') : '-' }}</td>
                                    <td class="py-2 px-3 text-right font-bold text-slate-800 tabular-nums">Rp {{ number_format($r->nominal, 0, ',', '.') }}</td>
                                    <td class="py-2 px-3">
                                        <span class="text-xs font-semibold {{ $r->status === 'disetujui' ? 'text-emerald-700' : ($r->status === 'ditolak' ? 'text-rose-700' : 'text-amber-700') }}">
                                            {{ ucfirst($r->status) }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-slate-500 text-[11px] truncate max-w-xs" title="{{ $r->catatan_spv }}">
                                        {{ $r->approver?->nama_petugas ? $r->approver->nama_petugas.': ' : '' }}
                                        {{ $r->catatan_spv ?: '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-400 italic">Belum ada riwayat pengajuan kas lainnya.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (1 KOLOM): JEJAK KEPUTUSAN & PANEL AKSI -->
            <div class="space-y-6">
                <!-- PANEL AKSI PERSETUJUAN / PENOLAKAN -->
                @if($pengajuan->status === 'pending')
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <h2 class="text-sm font-bold text-slate-900 pb-3 mb-3 border-b border-slate-100">
                        Aksi Keputusan Supervisor
                    </h2>
                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                        Pastikan seluruh data kas dan alasan pemohon telah diperiksa sebelum mengeksekusi otorisasi.
                    </p>

                    <div class="space-y-2.5">
                        <button type="button" onclick="openModalSetujui()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui Tambahan Kas
                        </button>
                        <button type="button" onclick="openModalTolak()" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-4 rounded-lg text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Tolak Permohonan Kas
                        </button>
                    </div>
                </div>
                @endif

                <!-- TIMELINE JEJAK KEPUTUSAN APPROVAL HISTORY -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-slate-900">Jejak Riwayat Keputusan</h2>
                        <span class="text-xs text-slate-500 font-medium">Audit Trail</span>
                    </div>

                    <div class="space-y-4">
                        @forelse($pengajuan->histories as $histori)
                        <div class="border-l-2 {{ $histori->jenis_keputusan === 'disetujui' ? 'border-emerald-500' : 'border-rose-500' }} pl-3 py-1 text-xs">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-bold text-slate-900 capitalize">{{ $histori->jenis_keputusan }}</span>
                                <span class="text-[10px] text-slate-400 tabular-nums">{{ $histori->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600">
                                Oleh: <span class="font-semibold text-slate-800">{{ $histori->nama_supervisor }}</span>
                            </p>
                            <p class="text-[10px] text-slate-500 mt-1">Status: <span class="font-mono">{{ $histori->status_sebelum }}</span> &rarr; <span class="font-mono font-bold">{{ $histori->status_sesudah }}</span></p>
                            @if($histori->catatan)
                            <div class="bg-slate-50 p-2 rounded border border-slate-200 mt-1.5 text-[11px] text-slate-700 italic">
                                "{{ $histori->catatan }}"
                            </div>
                            @endif
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Belum ada catatan keputusan. Pengajuan masih pending.</p>
                        @endforelse
                    </div>
                </div>

                <!-- PENGAJUAN PENDING LAINNYA DARI TELLER INI -->
                @if($pengajuanPendingLain->count() > 0)
                <div class="bg-amber-50 rounded-xl border border-amber-200 p-4 text-xs">
                    <h3 class="font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Perhatian Supervisor:
                    </h3>
                    <p class="text-amber-800 leading-relaxed">
                        Teller ini masih memiliki {{ $pengajuanPendingLain->count() }} pengajuan kas lain yang juga berstatus pending.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </main>

    <!-- MODAL 1: SETUJUI PENGAJUAN KAS -->
    <div id="modal-setujui" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Konfirmasi Persetujuan Droping Kas
                    </h3>
                    <button type="button" onclick="closeModalSetujui()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('supervisor.pengajuan-kas.setujui', $pengajuan->id) }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4 text-xs">
                        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                            <div class="flex justify-between mb-1">
                                <span class="text-slate-500">Nomor Pengajuan:</span>
                                <span class="font-bold text-slate-900 tabular-nums">{{ $pengajuan->nomor_pengajuan }}</span>
                            </div>
                            <div class="flex justify-between mb-1">
                                <span class="text-slate-500">Teller Pemohon:</span>
                                <span class="font-semibold text-slate-800">{{ $teller->nama_petugas ?? 'Teller' }}</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-slate-200">
                                <span class="text-slate-600 font-bold">Nominal Droping:</span>
                                <span class="font-black text-emerald-700 text-sm tabular-nums">Rp {{ number_format($pengajuan->nominal, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Catatan Otorisasi Supervisor (Opsional)</label>
                            <textarea name="catatan_spv" rows="3" placeholder="Contoh: Disetujui untuk pemenuhan likuiditas loket teller pagi..."
                                      class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">Disetujui untuk pemenuhan likuiditas operasional loket.</textarea>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Saat disetujui, kas fisik di brankas akan otomatis bertambah dan tercatat di jurnal akuntansi.
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeModalSetujui()" class="text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                            Batal
                        </button>
                        <button type="submit" class="text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-5 py-2 rounded-lg shadow-sm transition">
                            Ya, Sahkan & Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: TOLAK PENGAJUAN KAS (WAJIB ALASAN) -->
    <div id="modal-tolak" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
                <div class="bg-rose-900 text-white px-6 py-4 flex items-center justify-between">
                    <h3 class="text-sm font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Penolakan Pengajuan Kas
                    </h3>
                    <button type="button" onclick="closeModalTolak()" class="text-rose-300 hover:text-white text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('supervisor.pengajuan-kas.tolak', $pengajuan->id) }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-4 text-xs">
                        <div class="bg-rose-50 p-3.5 rounded-lg border border-rose-100 text-rose-950">
                            <div class="flex justify-between mb-1">
                                <span class="text-rose-700">Teller Pemohon:</span>
                                <span class="font-bold">{{ $teller->nama_petugas ?? 'Teller' }}</span>
                            </div>
                            <div class="flex justify-between mb-1">
                                <span class="text-rose-700">Nominal Diajukan:</span>
                                <span class="font-black tabular-nums">Rp {{ number_format($pengajuan->nominal, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-1 pt-1 border-t border-rose-200">
                                <span class="text-rose-700 block mb-0.5">Alasan Pemohon:</span>
                                <p class="italic text-rose-900">"{{ $pengajuan->alasan }}"</p>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-800 mb-1">
                                Alasan Penolakan Supervisor * <span class="text-rose-600 font-normal">(Wajib Diisi)</span>
                            </label>
                            <textarea name="alasan_penolakan" rows="3" required minlength="5" placeholder="Masukkan alasan penolakan agar teller memahami penyebabnya..."
                                      class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600"></textarea>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Alasan ini akan tercatat dalam jejak audit dan langsung ditampilkan kepada Teller pemohon.
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeModalTolak()" class="text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                            Batal
                        </button>
                        <button type="submit" class="text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 px-5 py-2 rounded-lg shadow-sm transition">
                            Konfirmasi Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModalSetujui() {
            document.getElementById('modal-setujui').classList.remove('hidden');
        }
        function closeModalSetujui() {
            document.getElementById('modal-setujui').classList.add('hidden');
        }

        function openModalTolak() {
            document.getElementById('modal-tolak').classList.remove('hidden');
        }
        function closeModalTolak() {
            document.getElementById('modal-tolak').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModalSetujui();
                closeModalTolak();
            }
        });
    </script>
</body>
</html>

