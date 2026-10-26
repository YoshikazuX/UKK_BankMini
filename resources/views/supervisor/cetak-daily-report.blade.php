<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Daily Report #{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }} - Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: #fff; }
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 p-4 md:p-8">

    <!-- FLOATING ACTION BUTTONS (NO-PRINT) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-xl shadow border border-slate-200">
        <div class="flex items-center space-x-2">
            <span class="inline-block w-3 h-3 rounded-full {{ $report->status === 'approved' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
            <span class="text-sm font-bold text-slate-700">Daily Report #REP-{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</span>
            <span class="text-xs uppercase px-2 py-0.5 rounded font-black {{ $report->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                {{ $report->status }}
            </span>
        </div>
        <div class="flex space-x-3">
            <button onclick="window.history.back()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-4 py-2 rounded-lg text-sm transition">
                &larr; Kembali
            </button>
            <button onclick="window.print()" class="bg-slate-900 hover:bg-slate-800 text-white font-medium px-5 py-2 rounded-lg text-sm shadow transition flex items-center">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Lembar Laporan (Print)
            </button>
        </div>
    </div>

    <!-- DOKUMEN CETAK UTAMA (A4 STANDARD) -->
    <div class="max-w-4xl mx-auto bg-white p-8 md:p-12 border border-slate-300 shadow-md print:border-none print:shadow-none print:p-0">
        
        <!-- KOP SURAT RESMI -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-4 mb-6">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-slate-900 text-white rounded-lg flex items-center justify-center font-bold text-2xl tracking-tighter shadow-sm">
                    MSA
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-wide">LKM Mitra Siswa Abadi</h2>
                    <p class="text-xs font-semibold text-slate-600">Laboratorium Perbankan & Lembaga Keuangan Mikro Sekolah</p>
                    <p class="text-[11px] text-slate-500">Jl. Pendidikan No. 01, Kompleks Kampus Sekolah Kejuruan | Telp: (021) 8899-7711</p>
                </div>
            </div>
            <div class="text-right">
                <div class="inline-block border border-slate-800 px-3 py-1 text-center rounded">
                    <p class="text-[9px] font-semibold text-slate-500">Nomor Dokumen</p>
                    <p class="text-sm font-bold text-slate-900 tabular-nums">REP-{{ date('Ymd', strtotime($report->report_date)) }}-{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        <div class="text-center mb-6">
            <h1 class="text-lg font-bold text-slate-900 underline underline-offset-4">
                Berita Acara & Laporan Penutupan Kas Harian (Daily Report)
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Tanggal Operasional: <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($report->report_date)->translatedFormat('l, d F Y') }}</span>
            </p>
        </div>

        <!-- IDENTITAS PETUGAS & STATUS DOKUMEN -->
        <table class="w-full text-xs mb-6 border-collapse">
            <tbody>
                <tr>
                    <td class="w-32 py-1 font-semibold text-slate-600">Petugas Teller</td>
                    <td class="w-4 py-1">:</td>
                    <td class="py-1 font-bold text-slate-900">{{ $report->nama_teller }} (User ID: #{{ $report->user_id }})</td>
                    <td class="w-32 py-1 font-semibold text-slate-600">Status Otorisasi</td>
                    <td class="w-4 py-1">:</td>
                    <td class="py-1 font-semibold capitalize {{ $report->status === 'approved' ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $report->status }}
                    </td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-slate-600">Disahkan Oleh</td>
                    <td class="py-1">:</td>
                    <td class="py-1 font-bold text-slate-900">{{ $report->nama_approver ?? 'Menunggu Pengesahan' }}</td>
                    <td class="py-1 font-semibold text-slate-600">Waktu Closing</td>
                    <td class="py-1">:</td>
                    <td class="py-1 text-slate-700 tabular-nums">{{ date('d/m/Y H:i:s', strtotime($report->updated_at)) }} WIB</td>
                </tr>
            </tbody>
        </table>

        <!-- REKAPITULASI KAS LOKET -->
        <div class="mb-6 border border-slate-300 rounded-lg overflow-hidden">
            <div class="bg-slate-100 px-4 py-2 border-b border-slate-300 font-semibold text-xs text-slate-700">
                Ringkasan Rekonsiliasi Kas Laci Loket
            </div>
            <table class="w-full text-xs">
                <tbody class="divide-y divide-slate-200">
                    <tr>
                        <td class="p-2.5 pl-4 text-slate-600">1. Saldo Awal Kas Operasional (Opening Balance)</td>
                        <td class="p-2.5 pr-4 text-right font-bold text-slate-900 tabular-nums">Rp {{ number_format($report->opening_balance, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="p-2.5 pl-4 text-slate-600">2. Total Setoran Masuk Hari Ini (Deposit)</td>
                        <td class="p-2.5 pr-4 text-right font-bold text-emerald-700 tabular-nums">(+) Rp {{ number_format($report->total_deposit, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="p-2.5 pl-4 text-slate-600">3. Total Penarikan Tunai & Biaya Admin (Withdrawal)</td>
                        <td class="p-2.5 pr-4 text-right font-bold text-rose-700 tabular-nums">(-) Rp {{ number_format($report->total_withdrawal, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-blue-50 font-bold">
                        <td class="p-2.5 pl-4 text-blue-950">4. Saldo Buku Komputer / Sistem (System Balance) [1 + 2 - 3]</td>
                        <td class="p-2.5 pr-4 text-right text-sm text-blue-950 font-bold tabular-nums">Rp {{ number_format($report->system_balance, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="bg-slate-50 font-bold">
                        <td class="p-2.5 pl-4 text-slate-900">5. Saldo Fisik Riil di Laci Kas (Physical Balance)</td>
                        <td class="p-2.5 pr-4 text-right text-sm text-slate-900 font-bold tabular-nums">Rp {{ number_format($report->physical_balance, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="{{ $report->difference == 0 ? 'bg-emerald-50 text-emerald-900' : 'bg-rose-50 text-rose-900' }} font-bold">
                        <td class="p-2.5 pl-4">6. Selisih Kas Fisik vs Komputer (Difference) [5 - 4]</td>
                        <td class="p-2.5 pr-4 text-right text-sm font-bold tabular-nums">
                            {{ $report->difference >= 0 ? '+' : '' }}Rp {{ number_format($report->difference, 0, ',', '.') }}
                            ({{ $report->difference == 0 ? 'Seimbang / Nihil' : ($report->difference > 0 ? 'Surplus / Lebih' : 'Defisit / Kurang') }})
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($report->notes)
        <div class="mb-6 p-3 bg-slate-50 rounded border border-slate-200 text-xs">
            <span class="font-bold text-slate-700">Catatan Khusus Petugas / Supervisor:</span>
            <p class="text-slate-600 mt-0.5 italic">"{{ $report->notes }}"</p>
        </div>
        @endif

        <!-- DAFTAR TRANSAKSI YANG DIBUKUKAN PADA DOKUMEN INI -->
        <div class="mb-8">
            <h3 class="text-xs font-semibold text-slate-700 mb-2">
                Lampiran Transaksi yang Divalidasi (Total {{ count($transaksi) }} Transaksi)
            </h3>
            <table class="w-full text-left text-[11px] border border-slate-300 border-collapse">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-300">
                    <tr>
                        <th class="p-2 border border-slate-300 text-center w-8">No</th>
                        <th class="p-2 border border-slate-300">Waktu</th>
                        <th class="p-2 border border-slate-300">No. Rekening</th>
                        <th class="p-2 border border-slate-300">Nama Nasabah</th>
                        <th class="p-2 border border-slate-300 text-center">Sandi</th>
                        <th class="p-2 border border-slate-300">Jenis</th>
                        <th class="p-2 border border-slate-300 text-right">Nominal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($transaksi as $idx => $t)
                    <tr>
                        <td class="p-1.5 border border-slate-300 text-center tabular-nums">{{ $idx + 1 }}</td>
                        <td class="p-1.5 border border-slate-300 tabular-nums">{{ date('H:i:s', strtotime($t->created_at)) }}</td>
                        <td class="p-1.5 border border-slate-300 font-semibold tabular-nums">{{ $t->no_rekening ?? $t->nis }}</td>
                        <td class="p-1.5 border border-slate-300">{{ $t->nama_siswa }}</td>
                        <td class="p-1.5 border border-slate-300 text-center tabular-nums font-semibold">{{ $t->kode_sandi ?? '-' }}</td>
                        <td class="p-1.5 border border-slate-300 uppercase font-medium">{{ $t->jenis_transaksi }}</td>
                        <td class="p-1.5 border border-slate-300 text-right tabular-nums font-semibold {{ in_array($t->jenis_transaksi, ['setor', 'koreksi_tarik']) ? 'text-emerald-700' : 'text-rose-700' }}">
                            Rp {{ number_format($t->nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-4 text-center text-slate-400 border border-slate-300">Tidak ada transaksi yang tercatat dalam sesi laci ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- TANDA TANGAN 3 PIHAK -->
        <div class="grid grid-cols-3 gap-6 text-center text-xs mt-10 pt-4 border-t border-slate-200">
            <div>
                <p class="text-slate-500 mb-1">Dibuat Oleh,</p>
                <p class="font-bold text-slate-700">Petugas Teller Loket</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900 uppercase">{{ $report->nama_teller }}</p>
                </div>
                <p class="text-[10px] text-slate-400">Petugas Kasir</p>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Diperiksa & Disetujui Oleh,</p>
                <p class="font-bold text-slate-700">Supervisor / Accounting</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900 uppercase">{{ $report->nama_approver ?? '( ................................ )' }}</p>
                </div>
                <p class="text-[10px] text-slate-400">Otorisator Keuangan</p>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Mengetahui,</p>
                <p class="font-bold text-slate-700">Kepala Unit Bank Mini</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900 uppercase">( Handayani, S.Pd., M.M )</p>
                </div>
                <p class="text-[10px] text-slate-400">NIP. 19850712 201001 2 021</p>
            </div>
        </div>

        <!-- FOOTER DOKUMEN -->
        <div class="mt-8 pt-4 border-t border-dashed border-slate-300 flex justify-between text-[10px] text-slate-400">
            <span>Sistem Otomasi Perbankan Mini LKM MSA &copy; {{ date('Y') }}</span>
            <span>ID Dokumen: REP-{{ $report->id }}-{{ md5($report->id . ($report->created_at ?? $report->report_date ?? '')) }}</span>
        </div>
    </div>
</body>
</html>
