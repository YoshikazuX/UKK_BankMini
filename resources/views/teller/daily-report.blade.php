<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Report (Tutup Kas Harian) - E-Teller</title>
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

        <!-- HEADER HALAMAN -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Daily Report Closing (Tutup Kas Harian)
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Rekonsiliasi antara catatan kas komputer dan kas fisik di laci loket teller tanggal 
                    <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</span>.
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <div class="bg-white border border-slate-200 rounded-lg px-4 py-2 text-right shadow-sm">
                    <p class="text-xs text-slate-500 font-medium">Petugas Teller</p>
                    <p class="text-sm font-semibold text-slate-800">{{ Auth::user()->nama_petugas }}</p>
                </div>
            </div>
        </div>

        <!-- 5 KARTU METRIK SALDO SISTEM -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">1. Saldo Awal Laci</p>
                    <span class="p-1.5 bg-slate-100 text-slate-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-slate-900 mt-2 tabular-nums">Rp {{ number_format($openingBalance, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Kas operasional awal</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">2. Total Setoran (+)</p>
                    <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-emerald-600 mt-2 tabular-nums">Rp {{ number_format($totalDeposit, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Uang masuk dari nasabah</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">3. Droping Kas (+)</p>
                    <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-indigo-600 mt-2 tabular-nums">Rp {{ number_format($totalCashDrop ?? 0, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Disetujui Supervisor</p>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">4. Total Penarikan (-)</p>
                    <span class="p-1.5 bg-rose-50 text-rose-600 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-rose-600 mt-2 tabular-nums">Rp {{ number_format($totalWithdrawal, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Uang tunai keluar ke nasabah</p>
            </div>

            <div class="bg-slate-900 text-white p-4 rounded-xl shadow-sm border border-slate-800">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-300">5. Saldo Komputer</p>
                    <span class="p-1.5 bg-slate-800 text-slate-300 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </span>
                </div>
                <p class="text-xl font-bold text-white mt-2 tabular-nums" id="val-system-balance" data-val="{{ $systemBalance }}">Rp {{ number_format($systemBalance, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Saldo Sistem = (1 + 2 + 3) - 4</p>
            </div>
        </div>

        <!-- FORM REKONSILIASI KAS FISIK & PERHITUNGAN LEMBARAN -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
            <!-- KALKULATOR LEMBARAN KAS FISIK -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Perhitungan Lembaran Uang Fisik di Laci
                        </h2>
                        <p class="text-xs text-slate-500">Hitung jumlah lembar tiap pecahan untuk memastikan uang fisik akurat.</p>
                    </div>
                    <button type="button" onclick="resetLembaran()" class="text-xs text-slate-500 hover:text-rose-600 underline">Reset Hitungan</button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                    @php
                        $pecahanList = [
                            ['label' => 'Rp 100.000', 'val' => 100000, 'bg' => 'bg-red-50 border-red-200 text-red-800'],
                            ['label' => 'Rp 50.000',  'val' => 50000,  'bg' => 'bg-blue-50 border-blue-200 text-blue-800'],
                            ['label' => 'Rp 20.000',  'val' => 20000,  'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800'],
                            ['label' => 'Rp 10.000',  'val' => 10000,  'bg' => 'bg-purple-50 border-purple-200 text-purple-800'],
                            ['label' => 'Rp 5.000',   'val' => 5000,   'bg' => 'bg-amber-50 border-amber-200 text-amber-800'],
                            ['label' => 'Rp 2.000',   'val' => 2000,   'bg' => 'bg-slate-100 border-slate-300 text-slate-800'],
                            ['label' => 'Rp 1.000',   'val' => 1000,   'bg' => 'bg-yellow-50 border-yellow-200 text-yellow-800'],
                            ['label' => 'Koin / Logam','val' => 1,     'bg' => 'bg-slate-50 border-slate-200 text-slate-700', 'isCoin' => true],
                        ];
                    @endphp

                    @foreach($pecahanList as $p)
                    <div class="p-3 rounded-lg border {{ $p['bg'] }} flex items-center justify-between">
                        <div class="w-32">
                            <p class="font-bold text-sm tabular-nums">{{ $p['label'] }}</p>
                            <span class="text-[10px] text-slate-500 subtotal-text tabular-nums" id="subtotal-{{ $p['val'] }}">Rp 0</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="number" min="0" placeholder="0"
                                   class="input-lembar w-24 text-right font-semibold text-sm bg-white border border-slate-300 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 tabular-nums"
                                   data-nominal="{{ $p['val'] }}"
                                   data-coin="{{ isset($p['isCoin']) ? 'true' : 'false' }}"
                                   oninput="hitungTotalKasFisik()">
                            <span class="text-xs text-slate-500 font-medium">{{ isset($p['isCoin']) ? 'Rp' : 'Lbr' }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="bg-slate-50 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between border border-slate-200">
                    <span class="text-sm font-semibold text-slate-700">Akumulasi Total Uang Fisik:</span>
                    <span class="text-2xl font-bold text-slate-900 tabular-nums" id="display-total-fisik">Rp 0</span>
                </div>
            </div>

            <!-- FORM SUBMIT DAILY REPORT -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 pb-3 mb-4 border-b border-slate-200">
                        Otorisasi Tutup Kas
                    </h2>

                    @if($myReportToday)
                    <div class="mb-4 p-4 rounded-xl {{ $myReportToday->status === 'approved' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : ($myReportToday->status === 'hold' ? 'bg-amber-50 border border-amber-200 text-amber-900' : ($myReportToday->status === 'rejected' ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-blue-50 border border-blue-200 text-blue-800')) }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-semibold">Status Laporan Hari Ini:</span>
                            @if($myReportToday->status === 'approved')
                                <span class="bg-emerald-600 text-white text-xs px-2 py-0.5 rounded font-semibold">Disetujui (Approved)</span>
                            @elseif($myReportToday->status === 'hold')
                                <span class="bg-amber-500 text-white text-xs px-2 py-0.5 rounded font-semibold">Perlu Klarifikasi / Perbaikan (Hold)</span>
                            @elseif($myReportToday->status === 'rejected')
                                <span class="bg-rose-600 text-white text-xs px-2 py-0.5 rounded font-semibold">Ditolak Supervisor</span>
                            @else
                                <span class="bg-blue-600 text-white text-xs px-2 py-0.5 rounded font-semibold">Menunggu Approval</span>
                            @endif
                        </div>
                        <p class="text-xs mt-1">Telah disubmit dengan kas fisik: <strong class="tabular-nums">Rp {{ number_format($myReportToday->physical_balance, 0, ',', '.') }}</strong></p>

                        @if(($myReportToday->status === 'hold' || $myReportToday->status === 'rejected') && ($myReportToday->catatan_spv || $myReportToday->alasan_penolakan))
                        <div class="mt-3 p-3 bg-white/80 rounded-lg border {{ $myReportToday->status === 'hold' ? 'border-amber-300' : 'border-rose-300' }}">
                            <p class="text-[11px] font-bold uppercase tracking-wider {{ $myReportToday->status === 'hold' ? 'text-amber-800' : 'text-rose-800' }}">Catatan Supervisor:</p>
                            <p class="text-xs mt-1 italic text-slate-800 font-medium">"{{ $myReportToday->catatan_spv ?: $myReportToday->alasan_penolakan }}"</p>
                            <p class="text-[11px] text-slate-600 mt-2 font-medium">⚠️ Silakan hitung ulang kas fisik di laci Anda, sesuaikan input nominal di bawah, lalu klik <strong>"Perbarui Laporan Tutup Kas"</strong>.</p>
                        </div>
                        @endif

                        @if($myReportToday->status === 'approved')
                            <p class="text-xs mt-2 text-emerald-700 font-medium">Laporan sudah disetujui Supervisor. Silakan cetak lembar bukti penutupan kas.</p>
                            <a href="{{ url('/daily-report/cetak/'.$myReportToday->id) }}" target="_blank" class="mt-3 inline-flex items-center text-xs font-medium bg-white hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-lg border border-slate-300 shadow-sm transition">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                Cetak Daily Report #{{ $myReportToday->id }}
                            </a>
                        @endif
                    </div>
                    @endif

                    <form action="{{ url('/teller/daily-report/simpan') }}" method="POST" id="form-daily-report">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Saldo Kas Fisik (Rp) *
                            </label>
                            <input type="number" name="physical_balance" id="physical_balance" min="0" required
                                   value="{{ old('physical_balance', $myReportToday->physical_balance ?? 0) }}"
                                   oninput="updateDifferenceManual()"
                                   class="w-full font-bold text-xl text-slate-900 border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 tabular-nums">
                            <p class="text-[11px] text-slate-400 mt-1">Dapat diisi manual atau dari kalkulator lembaran di samping.</p>
                        </div>

                        <!-- KOTAK INDIKATOR SELISIH KAS -->
                        <div id="diff-card" class="mb-4 p-3.5 rounded-lg border bg-slate-50 border-slate-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-600">Status Rekonsiliasi:</span>
                                <span id="diff-status" class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-200 text-slate-700">Belum Dihitung</span>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200">
                                <span class="text-xs text-slate-500">Selisih (Fisik - Sistem):</span>
                                <span id="diff-amount" class="text-base font-bold text-slate-900 tabular-nums">Rp 0</span>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-slate-700 mb-1" id="notes-label">
                                Catatan / Keterangan Teller
                            </label>
                            <textarea name="notes" id="notes" rows="2" placeholder="Tuliskan keterangan jika ada kelebihan/kekurangan kas fisik atau hal khusus lainnya..."
                                      class="w-full text-xs text-slate-800 border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">{{ old('notes', $myReportToday->notes ?? '') }}</textarea>
                            <p id="notes-warning" class="hidden text-[11px] font-semibold text-rose-600 mt-1">
                                * Terdapat selisih kas fisik. SOP perbankan mewajibkan penjelasan penyebab selisih diisi!
                            </p>
                        </div>

                        <button type="submit" id="btn-submit-report"
                                {{ ($myReportToday && $myReportToday->status === 'approved') ? 'disabled' : '' }}
                                class="w-full bg-slate-900 hover:bg-slate-800 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition flex items-center justify-center text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            {{ $myReportToday ? 'Perbarui Laporan Tutup Kas' : 'Kirim Tutup Kas ke Supervisor' }}
                        </button>
                    </form>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 text-[11px] text-slate-400">
                    <p>Setelah disetujui Supervisor, data kas harian akan dibekukan dan tercatat permanen pada Jurnal Akuntansi.</p>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN TRANSAKSI HARI INI -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-10 overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Rincian Transaksi Loket Saya Hari Ini</h2>
                    <p class="text-xs text-slate-500">Daftar transaksi yang Anda proses pada tanggal {{ \Carbon\Carbon::today()->format('d/m/Y') }}.</p>
                </div>
                <span class="bg-slate-100 text-slate-700 text-xs font-semibold px-3 py-1 rounded-full tabular-nums">
                    {{ count($trxHariIni) }} Transaksi
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="p-3">Waktu</th>
                            <th class="p-3">ID Trx</th>
                            <th class="p-3">Jenis Transaksi</th>
                            <th class="p-3">Sandi</th>
                            <th class="p-3 text-right">Nominal (Rp)</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($trxHariIni as $t)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 text-slate-500 tabular-nums">{{ date('H:i:s', strtotime($t->created_at)) }}</td>
                            <td class="p-3 font-semibold text-slate-900 tabular-nums">#TRX-{{ str_pad($t->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td class="p-3 capitalize font-medium">
                                @if(in_array($t->jenis_transaksi, ['setor', 'koreksi_tarik']))
                                    <span class="text-emerald-700 font-semibold">{{ $t->jenis_transaksi }}</span>
                                @else
                                    <span class="text-rose-700 font-semibold">{{ $t->jenis_transaksi }}</span>
                                @endif
                            </td>
                            <td class="p-3 font-semibold tabular-nums text-slate-700">{{ $t->kode_sandi ?? '-' }}</td>
                            <td class="p-3 text-right font-semibold tabular-nums {{ in_array($t->jenis_transaksi, ['setor', 'koreksi_tarik']) ? 'text-emerald-700' : 'text-rose-700' }}">
                                Rp {{ number_format($t->nominal, 0, ',', '.') }}
                            </td>
                            <td class="p-3">
                                @if($t->status === 'sukses')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded">Sukses</span>
                                @elseif($t->status === 'pending')
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-semibold px-2 py-0.5 rounded">Pending SPV</span>
                                @else
                                    <span class="bg-rose-100 text-rose-800 text-[10px] font-semibold px-2 py-0.5 rounded capitalize">{{ $t->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-slate-400">Belum ada transaksi yang diproses di loket Anda hari ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIWAYAT DAILY REPORT SAYA -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200">
                <h2 class="text-base font-bold text-slate-900">Riwayat Daily Report Saya</h2>
                <p class="text-xs text-slate-500">Arsip laporan tutup kas harian yang pernah Anda kirimkan.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="p-3">Tanggal Laporan</th>
                            <th class="p-3 text-right">Saldo Awal</th>
                            <th class="p-3 text-right">Total Setor</th>
                            <th class="p-3 text-right">Droping Kas</th>
                            <th class="p-3 text-right">Total Tarik</th>
                            <th class="p-3 text-right">Saldo Sistem</th>
                            <th class="p-3 text-right">Kas Fisik</th>
                            <th class="p-3 text-right">Selisih</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($myReports as $rep)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 font-semibold text-slate-900 tabular-nums">{{ date('d/m/Y', strtotime($rep->report_date)) }}</td>
                            <td class="p-3 text-right tabular-nums">Rp {{ number_format($rep->opening_balance, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-emerald-600 font-semibold tabular-nums">Rp {{ number_format($rep->total_deposit, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-indigo-600 font-semibold tabular-nums">Rp {{ number_format($rep->total_cash_drop ?? 0, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-rose-600 font-semibold tabular-nums">Rp {{ number_format($rep->total_withdrawal, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-semibold text-slate-900 tabular-nums">Rp {{ number_format($rep->system_balance, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-semibold tabular-nums">Rp {{ number_format($rep->physical_balance, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-semibold tabular-nums {{ $rep->difference == 0 ? 'text-emerald-600' : ($rep->difference > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                Rp {{ number_format($rep->difference, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                @if($rep->status === 'approved')
                                    <span class="bg-emerald-100 text-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded">Approved</span>
                                @elseif($rep->status === 'hold')
                                    <span class="bg-amber-100 text-amber-800 text-[10px] font-semibold px-2 py-0.5 rounded">Hold</span>
                                @else
                                    <span class="bg-blue-100 text-blue-800 text-[10px] font-semibold px-2 py-0.5 rounded">Pending</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ url('/daily-report/cetak/'.$rep->id) }}" target="_blank"
                                   class="inline-flex items-center text-xs font-medium text-slate-700 hover:bg-slate-50 bg-white px-2.5 py-1 rounded-lg border border-slate-300 transition shadow-sm">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    Cetak
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">Belum ada riwayat daily report yang tercatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const systemBalance = {{ $systemBalance }};

        function hitungTotalKasFisik() {
            let total = 0;
            const inputs = document.querySelectorAll('.input-lembar');

            inputs.forEach(input => {
                const nominal = parseInt(input.getAttribute('data-nominal'));
                const isCoin = input.getAttribute('data-coin') === 'true';
                const count = parseInt(input.value) || 0;

                let subtotal = isCoin ? count : (nominal * count);
                total += subtotal;

                const subLabel = document.getElementById('subtotal-' + nominal);
                if (subLabel) {
                    subLabel.innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
                }
            });

            document.getElementById('display-total-fisik').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            document.getElementById('physical_balance').value = total;

            evaluasiSelisih(total);
        }

        function updateDifferenceManual() {
            const fisik = parseInt(document.getElementById('physical_balance').value) || 0;
            evaluasiSelisih(fisik);
        }

        function evaluasiSelisih(fisik) {
            const diff = fisik - systemBalance;
            const diffStatus = document.getElementById('diff-status');
            const diffAmount = document.getElementById('diff-amount');
            const diffCard = document.getElementById('diff-card');
            const notesField = document.getElementById('notes');
            const notesLabel = document.getElementById('notes-label');
            const notesWarning = document.getElementById('notes-warning');

            diffAmount.innerText = (diff >= 0 ? '+ ' : '- ') + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(diff));

            if (diff === 0) {
                diffStatus.className = 'text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800';
                diffStatus.innerText = 'SEIMBANG (Rp 0)';
                diffCard.className = 'mb-4 p-3.5 rounded-lg border bg-emerald-50 border-emerald-200';
                if (notesField) notesField.removeAttribute('required');
                if (notesLabel) notesLabel.innerHTML = 'Catatan / Keterangan Teller';
                if (notesWarning) notesWarning.classList.add('hidden');
            } else if (diff > 0) {
                diffStatus.className = 'text-xs font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-800';
                diffStatus.innerText = 'LEBIH FISIK (SURPLUS)';
                diffCard.className = 'mb-4 p-3.5 rounded-lg border bg-blue-50 border-blue-200';
                if (notesField) notesField.setAttribute('required', 'true');
                if (notesLabel) notesLabel.innerHTML = 'Catatan / Keterangan Teller <span class="text-rose-500 font-bold">* (Wajib Diisi Karena Ada Selisih)</span>';
                if (notesWarning) notesWarning.classList.remove('hidden');
            } else {
                diffStatus.className = 'text-xs font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800';
                diffStatus.innerText = 'KURANG FISIK (DEFISIT)';
                diffCard.className = 'mb-4 p-3.5 rounded-lg border bg-rose-50 border-rose-200';
                if (notesField) notesField.setAttribute('required', 'true');
                if (notesLabel) notesLabel.innerHTML = 'Catatan / Keterangan Teller <span class="text-rose-500 font-bold">* (Wajib Diisi Karena Ada Selisih)</span>';
                if (notesWarning) notesWarning.classList.remove('hidden');
            }
        }

        function resetLembaran() {
            document.querySelectorAll('.input-lembar').forEach(i => i.value = '');
            document.querySelectorAll('.subtotal-text').forEach(s => s.innerText = 'Rp 0');
            hitungTotalKasFisik();
        }

        // Jalankan evaluasi awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const initialFisik = parseInt(document.getElementById('physical_balance').value) || 0;
            evaluasiSelisih(initialFisik);
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
