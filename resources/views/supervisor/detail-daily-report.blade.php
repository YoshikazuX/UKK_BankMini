<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Daily Report #REP-{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }} - Supervisor</title>
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
                    <a href="{{ route('supervisor.daily-report') }}" class="hover:text-indigo-600">Daftar Daily Report</a>
                </div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                        Detail Pemeriksaan & Rekonsiliasi Tutup Kas
                    </h1>
                    <span class="text-xs font-semibold {{ $report->status === 'approved' ? 'text-emerald-700' : ($report->status === 'rejected' ? 'text-rose-700' : ($report->status === 'hold' ? 'text-amber-700' : 'text-blue-700')) }}">
                        {{ $report->status === 'approved' ? 'Disetujui (Approved)' : ($report->status === 'rejected' ? 'Ditolak' : ($report->status === 'hold' ? 'Perlu Klarifikasi (Hold)' : 'Menunggu Review')) }}
                    </span>
                </div>
                <p class="text-slate-500 text-xs mt-1">
                    Nomor Dokumen: <strong class="text-slate-900 tabular-nums">#REP-{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</strong> &bull; Tanggal Operasional: {{ $report->report_date ? $report->report_date->format('d F Y') : '-' }} &bull; Dikirim pada {{ $report->created_at->format('d/m/Y H:i') }} WIB
                </p>
            </div>

            <!-- TOMBOL AKSI ATAS -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('daily-report.cetak', $report->id) }}" target="_blank" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-3 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5" title="Cetak Lembar Laporan Kas">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Dokumen Resmi
                </a>

                @if($report->status !== 'approved')
                    <button type="button" onclick="openModalAction('approved')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3.5 py-2 rounded-lg text-xs shadow-sm transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Sahkan & Setujui
                    </button>
                    <button type="button" onclick="openModalAction('hold')" class="bg-amber-600 hover:bg-amber-700 text-white font-bold px-3 py-2 rounded-lg text-xs shadow-sm transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Minta Klarifikasi
                    </button>
                    <button type="button" onclick="openModalAction('rejected')" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-3 py-2 rounded-lg text-xs shadow-sm transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Tolak
                    </button>
                @endif
            </div>
        </div>

        <!-- 1. TABEL REKONSILIASI KAS KOMPARATIF (SISTEM VS FISIK) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">
                        Perbandingan Kas Komparatif (Sistem vs Hasil Hitung Fisik Teller)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Memeriksa rincian pembentuk saldo kas dari saldo awal, setoran, droping kas disetujui, dan penarikan tunai.
                    </p>
                </div>

                <!-- STATUS REKONSILIASI -->
                <div>
                    @if($report->difference == 0)
                        <span class="text-xs font-bold text-emerald-700">
                            Kas Fisik SEIMBANG (Rp 0)
                        </span>
                    @elseif($report->difference > 0)
                        <span class="text-xs font-bold text-blue-700">
                            SURPLUS (+ Rp {{ number_format($report->difference, 0, ',', '.') }})
                        </span>
                    @else
                        <span class="text-xs font-bold text-rose-700">
                            DEFISIT (- Rp {{ number_format(abs($report->difference), 0, ',', '.') }})
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                <!-- A. KAS MENURUT PERHITUNGAN SISTEM KOMPUTER -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <h3 class="font-bold text-slate-900 text-sm pb-2 mb-3 border-b border-slate-200 flex items-center justify-between">
                        <span>A. Perhitungan Kas Menurut Sistem</span>
                        <span class="text-[10px] font-semibold text-slate-500 uppercase">Audit Otomatis</span>
                    </h3>

                    <div class="space-y-2.5">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-slate-600">Saldo Awal Loket:</span>
                            <span class="font-semibold text-slate-900 tabular-nums">Rp {{ number_format($report->opening_balance, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 text-emerald-700">
                            <span>(+) Total Setoran Masuk Nasabah:</span>
                            <span class="font-bold tabular-nums">Rp {{ number_format($report->total_deposit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 text-indigo-700">
                            <span>(+) Droping Kas Disetujui:</span>
                            <span class="font-bold tabular-nums">Rp {{ number_format($report->total_cash_drop, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 text-rose-700">
                            <span>(-) Total Penarikan Tunai & Biaya:</span>
                            <span class="font-bold tabular-nums">Rp {{ number_format($report->total_withdrawal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2.5 border-t-2 border-slate-300 font-bold text-slate-900 text-sm">
                            <span>Total Saldo Menurut Sistem:</span>
                            <span class="text-indigo-900 tabular-nums font-black">Rp {{ number_format($report->system_balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- B. KAS FISIK HASIL CASH OPNAME TELLER -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm pb-2 mb-3 border-b border-slate-200 flex items-center justify-between">
                            <span>B. Kas Fisik Aktual di Laci Loket</span>
                            <span class="text-[10px] font-semibold text-slate-500 uppercase">Hitungan Teller</span>
                        </h3>

                        <div class="space-y-2.5">
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-600">Petugas yang Menghitung:</span>
                                <span class="font-semibold text-slate-900">{{ $report->user->nama_petugas ?? 'Teller' }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-600">Kas Fisik Diperiksa Teller:</span>
                                <span class="font-black text-slate-900 text-sm tabular-nums">Rp {{ number_format($report->physical_balance, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-600">Status Selisih Kas:</span>
                                <span class="font-bold uppercase {{ $report->difference == 0 ? 'text-emerald-700' : ($report->difference > 0 ? 'text-blue-700' : 'text-rose-700') }}">
                                    {{ $statusRekonsiliasi }} ({{ $report->difference >= 0 ? '+' : '' }}Rp {{ number_format($report->difference, 0, ',', '.') }})
                                </span>
                            </div>
                        </div>

                        <!-- CATATAN DARI TELLER SAAT SUBMIT -->
                        <div class="mt-4 pt-3 border-t border-slate-200">
                            <span class="text-slate-500 font-semibold block mb-1">Catatan Keterangan Hasil Cash Opname Teller:</span>
                            <div class="bg-white p-2.5 rounded border border-slate-200 text-slate-800 text-[11px] leading-relaxed italic">
                                "{{ $report->notes ?: 'Tidak ada catatan tambahan dari teller (Kas fisik dinyatakan seimbang).' }}"
                            </div>
                        </div>
                    </div>

                    @if($report->catatan_spv)
                    <div class="mt-3 p-2.5 rounded bg-amber-50 border border-amber-200 text-amber-900 text-[11px]">
                        <span class="font-bold block mb-0.5">Catatan Arahan Supervisor:</span>
                        "{{ $report->catatan_spv }}"
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- KOLOM KIRI (2 KOLOM): DAFTAR TRANSAKSI PENDUKUNG PEMBENTUK LAPORAN -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-slate-900 text-sm">Daftar Transaksi Pendukung Tutup Kas</h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $transaksiSuksesCount }} transaksi sukses &bull; {{ $transaksiBatalCount }} transaksi dibatalkan (void)
                            </p>
                        </div>

                        <!-- FILTER KECIL TRANSAKSI PENDUKUNG -->
                        <form action="{{ route('supervisor.daily-report.detail', $report->id) }}" method="GET" class="flex items-center gap-2">
                            <select name="trx_jenis" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none">
                                <option value="">Semua Jenis</option>
                                <option value="setor" {{ request('trx_jenis') === 'setor' ? 'selected' : '' }}>Setoran</option>
                                <option value="tarik" {{ request('trx_jenis') === 'tarik' ? 'selected' : '' }}>Penarikan</option>
                                <option value="admin" {{ request('trx_jenis') === 'admin' ? 'selected' : '' }}>Biaya Admin</option>
                            </select>
                            <select name="trx_status" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none">
                                <option value="">Semua Status</option>
                                <option value="sukses" {{ request('trx_status') === 'sukses' ? 'selected' : '' }}>Sukses</option>
                                <option value="dibatalkan" {{ request('trx_status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                            </select>
                        </form>
                    </div>

                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 sticky top-0 z-10">
                                <tr>
                                    <th class="py-2.5 px-3">ID / Waktu</th>
                                    <th class="py-2.5 px-3">Nasabah & NIS</th>
                                    <th class="py-2.5 px-3">Jenis</th>
                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                    <th class="py-2.5 px-3">Keterangan / Void</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($transaksi as $t)
                                <tr class="hover:bg-slate-50 transition {{ $t->status === 'dibatalkan' ? 'bg-rose-50/40 text-slate-400' : '' }}">
                                    <td class="py-2.5 px-3 tabular-nums">
                                        <span class="font-bold block {{ $t->status === 'dibatalkan' ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                            #{{ $t->id }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">{{ $t->created_at ? $t->created_at->format('H:i:s') : '-' }}</span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="font-semibold block {{ $t->status === 'dibatalkan' ? 'text-slate-500' : 'text-slate-900' }}">
                                            {{ $t->nama_siswa ?? $t->nasabah?->nama_siswa }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 tabular-nums">NIS: {{ $t->nis ?? $t->nasabah?->nis }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 capitalize font-medium">
                                        {{ str_replace('_', ' ', $t->jenis_transaksi) }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-bold tabular-nums {{ $t->status === 'dibatalkan' ? 'line-through text-slate-400' : ($t->jenis_transaksi === 'setor' ? 'text-emerald-700' : 'text-rose-700') }}">
                                        Rp {{ number_format($t->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($t->status === 'dibatalkan')
                                            <span class="text-xs font-semibold text-rose-700">Dibatalkan</span>
                                        @elseif($t->status === 'sukses')
                                            <span class="text-xs font-semibold text-emerald-700">Sukses</span>
                                        @else
                                            <span class="text-xs font-semibold text-amber-700">Pending</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-[11px] text-slate-500">
                                        @if($t->status === 'dibatalkan')
                                            <span class="text-rose-600 font-semibold block">Void / Batal Transaksi</span>
                                            @if($t->ref_transaksi_id)
                                                <span class="text-[10px] text-slate-400">Ref: #{{ $t->ref_transaksi_id }}</span>
                                            @endif
                                        @else
                                            <span>Normal</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400 italic">
                                        Tidak ada transaksi pendukung yang ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN (1 KOLOM): JEJAK KEPUTUSAN & DROPING KAS TERKAIT -->
            <div class="space-y-6">
                <!-- JEJAK APPROVAL HISTORY -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-slate-900">Jejak Pemeriksaan Supervisor</h2>
                        <span class="text-xs text-slate-500 font-medium">Audit Trail</span>
                    </div>

                    <div class="space-y-4">
                        @forelse($report->histories as $h)
                        <div class="border-l-2 {{ $h->jenis_keputusan === 'disetujui' ? 'border-emerald-500' : ($h->jenis_keputusan === 'ditolak' ? 'border-rose-500' : 'border-amber-500') }} pl-3 py-1 text-xs">
                            <div class="flex items-center justify-between mb-0.5">
                                <span class="font-bold text-slate-900 capitalize">{{ str_replace('_', ' ', $h->jenis_keputusan) }}</span>
                                <span class="text-[10px] text-slate-400 tabular-nums">{{ $h->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600">
                                Pemeriksa: <span class="font-semibold text-slate-800">{{ $h->nama_supervisor }}</span>
                            </p>
                            <p class="text-[10px] text-slate-500 mt-1">Status: <span class="font-mono">{{ $h->status_sebelum }}</span> &rarr; <span class="font-mono font-bold">{{ $h->status_sesudah }}</span></p>
                            @if($h->catatan)
                            <div class="bg-slate-50 p-2 rounded border border-slate-200 mt-1.5 text-[11px] text-slate-700 italic">
                                "{{ $h->catatan }}"
                            </div>
                            @endif
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 italic text-center py-4">Belum ada catatan audit review sebelumnya.</p>
                        @endforelse
                    </div>
                </div>

                <!-- DAFTAR DROPING KAS TELLER PADA HARI INI -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <h2 class="text-sm font-bold text-slate-900 pb-3 mb-3 border-b border-slate-100">
                        Droping Kas Teller Hari Tersebut
                    </h2>

                    <div class="space-y-2 text-xs">
                        @forelse($dropingKasList as $dk)
                        <div class="p-2.5 bg-slate-50 rounded border border-slate-200 flex justify-between items-center">
                            <div>
                                <span class="font-bold text-slate-900 block">{{ $dk->nomor_pengajuan }}</span>
                                <span class="text-[10px] text-slate-400">{{ $dk->created_at->format('H:i') }} WIB</span>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-slate-900 tabular-nums block">Rp {{ number_format($dk->nominal, 0, ',', '.') }}</span>
                                <span class="text-xs font-semibold {{ $dk->status === 'disetujui' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ ucfirst($dk->status) }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <p class="text-slate-400 italic text-center py-2 text-xs">Tidak ada permohonan droping kas pada tanggal ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL TINDAKAN SUPERVISOR (APPROVED / HOLD / REJECTED) -->
    <div id="modal-action" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden">
                <div id="modal-header-bg" class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold" id="modal-title">Tindakan Otorisasi Daily Report</h3>
                        <p class="text-xs text-slate-300 mt-0.5">Dokumen: #REP-{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }} &bull; {{ $report->user->nama_petugas ?? 'Teller' }}</p>
                    </div>
                    <button type="button" onclick="closeModalAction()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
                </div>

                <form action="{{ route('supervisor.daily-report.status', $report->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" id="modal-target-status" value="">

                    <div class="p-6 space-y-4 text-xs">
                        <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-slate-500 block">Saldo Kas Sistem:</span>
                                <span class="font-bold text-slate-900 text-sm tabular-nums">Rp {{ number_format($report->system_balance, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Kas Fisik Aktual:</span>
                                <span class="font-bold text-slate-900 text-sm tabular-nums">Rp {{ number_format($report->physical_balance, 0, ',', '.') }}</span>
                            </div>
                            <div class="col-span-2 pt-2 border-t border-slate-200 flex justify-between items-center">
                                <span class="text-slate-600 font-bold">Selisih Kas Fisik:</span>
                                <span class="font-black text-sm tabular-nums {{ $report->difference == 0 ? 'text-emerald-700' : ($report->difference > 0 ? 'text-blue-700' : 'text-rose-700') }}">
                                    {{ $report->difference >= 0 ? '+' : '' }}Rp {{ number_format($report->difference, 0, ',', '.') }} ({{ $statusRekonsiliasi }})
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-800 mb-1" id="label-catatan">
                                Catatan Pemeriksaan Supervisor *
                            </label>
                            <textarea name="notes" id="input-notes" rows="3" placeholder="Masukkan arahan atau catatan instruksi untuk teller..."
                                      class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700"></textarea>
                            <p class="text-[11px] text-slate-500 mt-1" id="help-notes">
                                Catatan ini akan dicatat ke dalam audit trail dan langsung dapat dibaca oleh Teller.
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeModalAction()" class="text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                            Batal
                        </button>
                        <button type="submit" id="modal-submit-btn" class="text-xs font-bold text-white px-5 py-2 rounded-lg shadow-sm transition bg-slate-900 hover:bg-slate-800">
                            Konfirmasi Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModalAction(status) {
            document.getElementById('modal-target-status').value = status;
            const title = document.getElementById('modal-title');
            const headerBg = document.getElementById('modal-header-bg');
            const submitBtn = document.getElementById('modal-submit-btn');
            const labelCatatan = document.getElementById('label-catatan');
            const inputNotes = document.getElementById('input-notes');

            if (status === 'approved') {
                title.innerText = 'Mengesahkan / Menyetujui Daily Report';
                headerBg.className = 'bg-emerald-900 text-white px-6 py-4 flex items-center justify-between';
                submitBtn.className = 'text-xs font-bold text-white px-5 py-2 rounded-lg shadow-sm transition bg-emerald-600 hover:bg-emerald-700';
                submitBtn.innerText = 'Ya, Sahkan & Setujui Dokumen';
                labelCatatan.innerText = 'Catatan Pengesahan (Opsional):';
                inputNotes.removeAttribute('required');
                inputNotes.value = 'Tutup kas harian telah diverifikasi dan disetujui.';
            } else if (status === 'hold') {
                title.innerText = 'Permintaan Klarifikasi / Penangguhan (Hold)';
                headerBg.className = 'bg-amber-900 text-white px-6 py-4 flex items-center justify-between';
                submitBtn.className = 'text-xs font-bold text-white px-5 py-2 rounded-lg shadow-sm transition bg-amber-600 hover:bg-amber-700';
                submitBtn.innerText = 'Kirim Permintaan Klarifikasi';
                labelCatatan.innerText = 'Catatan Klarifikasi Selisih / Perbaikan * (Wajib Diisi):';
                inputNotes.setAttribute('required', 'true');
                inputNotes.value = 'Terdapat selisih kas fisik. Mohon periksa kembali transaksi dan fisik uang di laci loket.';
            } else if (status === 'rejected') {
                title.innerText = 'Penolakan Daily Report';
                headerBg.className = 'bg-rose-900 text-white px-6 py-4 flex items-center justify-between';
                submitBtn.className = 'text-xs font-bold text-white px-5 py-2 rounded-lg shadow-sm transition bg-rose-600 hover:bg-rose-700';
                submitBtn.innerText = 'Konfirmasi Penolakan Laporan';
                labelCatatan.innerText = 'Alasan Penolakan Laporan * (Wajib Diisi):';
                inputNotes.setAttribute('required', 'true');
                inputNotes.value = 'Perhitungan kas fisik tidak sesuai dengan catatan transaksi loket.';
            }

            document.getElementById('modal-action').classList.remove('hidden');
        }

        function closeModalAction() {
            document.getElementById('modal-action').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModalAction();
        });
    </script>
</body>
</html>

