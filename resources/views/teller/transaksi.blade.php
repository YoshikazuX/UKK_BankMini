<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Transaksi - E-Teller Bank Mini</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 relative">

        <!-- NOTIFIKASI ERROR / PENOLAKAN TRANSAKSI -->
        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-900 p-4 mb-6 rounded-r-xl shadow-sm flex items-start justify-between">
            <div class="flex items-start">
                <svg class="w-6 h-6 mr-3 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-bold text-base text-rose-900">Transaksi Ditolak / Gagal Diproses</p>
                    <p class="text-sm font-normal text-rose-700 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700 font-bold ml-4">&times;</button>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-900 p-4 mb-6 rounded-r-xl shadow-sm flex items-start justify-between">
            <div class="flex items-start">
                <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-bold text-base text-emerald-900">Transaksi Berhasil</p>
                    <p class="text-sm font-normal text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-700 font-bold ml-4">&times;</button>
        </div>
        @endif

        <!-- HEADER FORM -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Form Layanan Loket Teller</h1>
                <p class="text-slate-500 text-sm mt-1">Verifikasi identitas dan saldo rekening nasabah sebelum mengeksekusi transaksi.</p>
            </div>
            <a href="{{ url('/teller/dashboard') }}" class="inline-flex items-center self-start sm:self-auto bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm">
                &larr; Kembali ke Dashboard
            </a>
        </div>

        <!-- 1. DETAIL REKENING LENGKAP SEBELUM TRANSAKSI (SOP PERBANKAN) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-slate-900 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 text-white font-bold flex items-center justify-center text-sm border border-slate-700 shrink-0">
                        {{ strtoupper(mb_substr($nasabah->nama_siswa, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-white">{{ $nasabah->nama_siswa }}</h2>
                            @if($nasabah->status === 'aktif')
                                <span class="text-xs font-medium text-emerald-400">Rekening Aktif</span>
                            @else
                                <span class="text-xs font-medium text-rose-400">Nonaktif / Ditangguhkan</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">Verifikasi data rekening pemilik buku tabungan Bank Mini Sekolah</p>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold block">Kategori Nasabah</span>
                    <span class="text-xs font-semibold text-slate-200 uppercase inline-block mt-0.5">
                        {{ $nasabah->kategori === 'guru_karyawan' || $nasabah->kategori === 'guru' ? 'Guru & Karyawan' : 'Siswa Sekolah' }}
                    </span>
                </div>
            </div>

            <!-- GRID DETAIL IDENTITAS & SALDO REKENING -->
            <div class="p-6 bg-slate-50 border-b border-slate-200">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                        <p class="text-slate-500 text-xs font-semibold">Nomor Rekening</p>
                        <p class="font-bold text-slate-900 text-base mt-1 tabular-nums">{{ $nasabah->no_rekening }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">NIS: {{ $nasabah->nis }}</p>
                    </div>

                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                        <p class="text-slate-500 text-xs font-semibold">Kelas & Jurusan</p>
                        <p class="font-bold text-slate-900 text-base mt-1">{{ $nasabah->kelas }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $nasabah->jurusan }}</p>
                    </div>

                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm">
                        <p class="text-slate-500 text-xs font-semibold">Saldo Buku Tabungan</p>
                        <p class="font-extrabold text-slate-900 text-lg mt-1 tabular-nums text-slate-900">
                            Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Saldo terdaftar di sistem</p>
                    </div>

                    <div class="bg-emerald-50/70 p-3.5 rounded-xl border border-emerald-200 shadow-sm">
                        <p class="text-emerald-900 text-xs font-bold">Saldo Siap Ditarik</p>
                        <p class="font-black text-emerald-700 text-lg mt-1 tabular-nums">
                            Rp {{ number_format($saldoTersedia, 0, ',', '.') }}
                        </p>
                        <p class="text-[11px] text-emerald-800 mt-0.5">
                            Saldo Mengendap: Rp {{ number_format($minSaldoMengendap, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- INFO TAMBAHAN PEMBUKAAN REKENING & KAS TELLER -->
                <div class="mt-4 pt-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-600 gap-2">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Tanggal Buka Rekening: <strong class="text-slate-800 font-semibold">{{ $nasabah->created_at ? $nasabah->created_at->format('d F Y') : '-' }}</strong></span>
                    </div>

                    <div class="flex items-center space-x-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                        <span>Kas Fisik Loket Teller: <strong class="text-slate-900 font-bold tabular-nums">Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- RIWAYAT TRANSAKSI TERAKHIR (INFORMATIF & MINIMALIS) -->
            <div class="p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        5 Riwayat Transaksi Terakhir Rekening Ini
                    </h3>
                    <a href="{{ url('/buku-tabungan/'.$nasabah->id) }}" target="_blank" class="text-xs font-semibold text-slate-700 hover:text-slate-900 underline">
                        Buka Buku Tabungan &rarr;
                    </a>
                </div>

                @if(isset($riwayatTransaksi) && count($riwayatTransaksi) > 0)
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2 px-3">Waktu</th>
                                <th class="py-2 px-3">Kode Ref</th>
                                <th class="py-2 px-3">Jenis Transaksi</th>
                                <th class="py-2 px-3">Sandi</th>
                                <th class="py-2 px-3 text-right">Nominal</th>
                                <th class="py-2 px-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($riwayatTransaksi as $rt)
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2 px-3 text-slate-500 tabular-nums">{{ $rt->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-2 px-3 font-semibold text-slate-800 tabular-nums">#TRX-{{ str_pad($rt->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="py-2 px-3 capitalize font-medium">
                                    {{ $rt->jenis_transaksi === 'setor' ? 'Setoran Tunai' : ($rt->jenis_transaksi === 'tarik' ? 'Penarikan Tunai' : $rt->jenis_transaksi) }}
                                </td>
                                <td class="py-2 px-3 font-semibold tabular-nums text-slate-600">{{ $rt->kode_sandi ?? '-' }}</td>
                                <td class="py-2 px-3 text-right font-bold tabular-nums {{ in_array($rt->jenis_transaksi, ['setor', 'koreksi_tarik']) ? 'text-emerald-700' : 'text-rose-700' }}">
                                    Rp {{ number_format($rt->nominal, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-center">
                                    @if($rt->status === 'sukses')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Sukses</span>
                                    @elseif($rt->status === 'dibatalkan')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800">Dibatalkan</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 capitalize">{{ $rt->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-xs text-slate-400 py-3 text-center bg-slate-50 rounded-lg border border-slate-100">
                    Belum ada riwayat transaksi pada rekening ini.
                </p>
                @endif
            </div>
        </div>

        <!-- PERINGATAN REKENING NONAKTIF -->
        @if($nasabah->status !== 'aktif')
        <div class="p-5 mb-8 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3">
            <svg class="w-6 h-6 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h4 class="font-bold text-sm">Rekening Tidak Dapat Melakukan Transaksi Finansial</h4>
                <p class="text-xs mt-1 text-rose-700">Status rekening nasabah saat ini adalah <strong>{{ strtoupper($nasabah->status) }}</strong>. Form transaksi dinonaktifkan sesuai SOP keamanan Bank Mini Sekolah. Nasabah dipersilakan menghubungi Customer Service untuk permohonan reaktivasi.</p>
            </div>
        </div>
        @endif

        <!-- PERINGATAN KAS MENIPIS / HABIS DENGAN SHORTCUT PENGAJUAN KAS -->
        @if(($kasTersedia ?? 0) < 500000 || session('kas_habis'))
        <div class="p-4 mb-6 rounded-xl border flex items-center justify-between {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-amber-50 border-amber-200 text-amber-900' }}">
            <div class="flex items-center text-xs">
                <svg class="w-5 h-5 mr-2 flex-shrink-0 {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>
                    @if(($kasTersedia ?? 0) <= 0)
                        <strong>Uang Kas di Loket Habis!</strong> Penarikan tunai tidak dapat diproses sampai pengajuan droping kas disetujui.
                    @else
                        <strong>Kas Fisik Loket Menipis:</strong> Tersedia <span class="tabular-nums font-bold">Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}</span>. Pastikan kas cukup sebelum melayani penarikan tunai.
                    @endif
                </span>
            </div>
            <a href="{{ url('/teller/pengajuan-kas') }}" class="font-medium text-xs px-3.5 py-2 rounded-lg transition shadow-sm flex items-center flex-shrink-0 ml-3 {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white' }}">
                Ajukan Droping Kas &rarr;
            </a>
        </div>
        @endif

        <!-- FORM TRANSAKSI UTAMA -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 {{ $nasabah->status !== 'aktif' ? 'opacity-60 pointer-events-none' : '' }}">
            <div class="pb-4 mb-6 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Formulir Eksekusi Transaksi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pilih jenis transaksi dan masukkan nominal uang fisik yang diverifikasi teller.</p>
            </div>

            <form id="form-transaksi" action="{{ url('/teller/transaksi/proses') }}" method="POST">
                @csrf
                <input type="hidden" name="nasabah_id" value="{{ $nasabah->id }}">
                <input type="hidden" name="idempotency_key" id="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-semibold mb-2">Pilih Jenis Transaksi</label>
                    <select name="jenis_transaksi" id="jenis_transaksi" onchange="toggleKoreksi(); simpanDraft(); validasiSaldo();" class="border border-slate-300 rounded-lg w-full py-2.5 px-3.5 text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-base" autofocus required>
                        <option value="">-- Pilih Jenis Transaksi --</option>
                        <option value="setor">Setoran Tunai [101] (Nasabah Menabung)</option>
                        <option value="tarik">Penarikan Tunai [201] (Pengambilan Uang)</option>
                        <option value="admin">Biaya Administrasi Buku [301]</option>
                        <option value="koreksi_setor">Koreksi Setoran [102] (Batal Uang Palsu / Robek / Salah Hitung)</option>
                        <option value="koreksi_tarik">Koreksi Penarikan [202] (Pembatalan Salah Debet)</option>
                    </select>
                </div>

                <!-- INPUT ALASAN KOREKSI (MUNCUL OTOMATIS SAAT KOREKSI DIPILIH) -->
                <div id="kotak-alasan-koreksi" class="hidden mb-5 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <label class="block text-amber-950 text-xs font-semibold mb-1">
                        Alasan Koreksi Transaksi *
                    </label>
                    <input type="text" name="alasan_koreksi" id="alasan_koreksi" placeholder="Contoh: Ditemukan 1 lbr uang palsu Rp 50.000 / salah input nominal nasabah"
                           class="w-full text-sm border border-slate-300 rounded-lg p-2.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                    <p class="text-[11px] text-amber-800 mt-1">Alasan ini akan dicetak langsung pada slip bukti koreksi dan jurnal akuntansi pembukuan.</p>
                </div>

                <!-- FORM VERIFIKASI PIN KEAMANAN NASABAH (WAJIB OTORISASI PENARIKAN TUNAI) -->
                <div id="kotak-pin-nasabah" class="hidden mb-5 p-4 bg-slate-50 border border-slate-300 rounded-xl">
                    <div class="flex items-center space-x-2 mb-2">
                        <svg class="w-5 h-5 text-slate-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <label class="block text-slate-800 text-xs font-semibold">
                            Otorisasi PIN Keamanan Nasabah (Wajib Penarikan Tunai) *
                        </label>
                    </div>
                    <p class="text-xs text-slate-600 mb-2">
                        Minta nasabah untuk memasukkan 6 digit PIN kartu tabungan secara rahasia:
                    </p>
                    <div class="relative max-w-xs">
                        <input type="password" name="pin_nasabah" id="pin_nasabah" maxlength="6" placeholder="••••••"
                               class="w-full text-center tracking-[0.4em] text-xl font-medium border border-slate-300 rounded-lg p-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 tabular-nums">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">*PIN default nasabah baru: 123456</p>
                </div>

                <div class="mb-8">
                    <!-- Label dan Tombol Kalkulator -->
                    <div class="flex justify-between items-end mb-2">
                        <label class="block text-slate-700 text-sm font-semibold">Nominal Uang (Rp)</label>
                        <button type="button" onclick="toggleKalkulator()" class="text-sm font-medium text-slate-700 hover:text-slate-900 flex items-center transition">
                            <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Bantuan Hitung Lembaran
                        </button>
                    </div>

                    <!-- Input Nominal -->
                    <input type="number" name="nominal" id="nominal" min="1000" oninput="simpanDraft(); validasiSaldo();" onkeypress="cekEnter(event)" class="border border-slate-300 rounded-lg w-full py-2.5 px-4 text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-lg font-bold bg-white tabular-nums" required placeholder="0">

                    <!-- PESAN ERROR REAL-TIME VALIDASI SALDO & KAS -->
                    <div id="pesan-error" class="hidden mt-2 p-3 bg-rose-50 border border-rose-200 rounded-lg">
                        <p class="text-xs font-semibold text-rose-700 flex items-start">
                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span id="teks-error">Peringatan Saldo</span>
                        </p>
                    </div>

                    <!-- KALKULATOR PECAHAN -->
                    <div id="kalkulator-panel" class="hidden mt-4 p-4 border border-slate-200 bg-slate-50 rounded-xl">
                        <p class="text-xs font-semibold text-slate-700 mb-3 border-b border-slate-200 pb-2">Kalkulator Pecahan Uang Fisik</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach([100000, 50000, 20000, 10000, 5000, 2000, 1000, 500] as $pecahan)
                            <div class="flex items-center space-x-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
                                <span class="text-xs font-semibold text-slate-600 w-16 tabular-nums">Rp{{ number_format($pecahan, 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-400">x</span>
                                <input type="number" min="0" class="pecahan-input w-full border border-slate-300 rounded-lg px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-slate-900 tabular-nums" data-nilai="{{ $pecahan }}" oninput="hitungTotalPecahan()" placeholder="0">
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-between items-center bg-white border border-slate-200 p-3 rounded-lg">
                            <span class="text-sm font-semibold text-slate-700">Total Terhitung:</span>
                            <span id="total-text" class="text-lg font-bold text-slate-900 tabular-nums">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 pt-6">
                    <div class="text-xs text-slate-400">
                        <span class="font-semibold text-slate-600">Proteksi Idempotensi:</span> Setiap transaksi diproteksi kunci unik mencegah mutasi ganda.
                    </div>
                    <button type="button" id="btn-proses" onclick="bukaModal()" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm transition text-sm w-full sm:w-auto">
                        Validasi & Proses Transaksi
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- POP-UP MODAL KONFIRMASI TRANSAKSI -->
    <div id="modal-konfirmasi" class="hidden fixed inset-0 bg-slate-900/80 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all border border-slate-200">
                <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold">Konfirmasi Transaksi Perbankan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pastikan nominal dan identitas nasabah sesuai</p>
                    </div>
                    <span id="badge-kode-sandi" class="text-xs font-black bg-slate-800 border border-slate-700 text-emerald-400 px-2.5 py-1 rounded-md tabular-nums">
                        SANDI [---]
                    </span>
                </div>

                <div class="p-6">
                    <div class="mb-4 pb-3 border-b border-slate-100">
                        <p class="text-xs font-semibold text-slate-500 mb-0.5">Pemilik Rekening</p>
                        <p class="text-lg font-extrabold text-slate-900">{{ $nasabah->nama_siswa }}</p>
                        <p class="text-xs font-semibold text-slate-500 tabular-nums">Rekening: {{ $nasabah->no_rekening }} ({{ $nasabah->kelas }})</p>
                    </div>

                    <div class="mb-4">
                        <p class="text-xs font-semibold text-slate-500 mb-0.5">Jenis Layanan</p>
                        <p class="text-base font-bold text-slate-800 capitalize" id="preview-jenis">-</p>
                    </div>

                    <div class="mb-5 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <p class="text-xs font-semibold text-slate-500 mb-1">Nominal Transaksi Fisik</p>
                        <p class="text-3xl font-black text-slate-900 mb-4 tabular-nums" id="preview-nominal">Rp 0</p>

                        <div class="border-t border-slate-200 pt-3 space-y-1.5 text-xs">
                            <div class="flex justify-between items-center text-slate-600">
                                <span>Saldo Rekening Saat Ini:</span>
                                <span class="font-semibold tabular-nums">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center text-slate-900 font-bold">
                                <span>Perkiraan Saldo Akhir:</span>
                                <span class="tabular-nums text-sm font-extrabold" id="preview-saldo-akhir">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button type="button" onclick="tutupModal()" class="w-1/3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-4 rounded-lg transition text-xs">
                            Batal
                        </button>
                        <button type="button" id="btn-konfirmasi-final" onclick="submitFormFinal()" class="w-2/3 inline-flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition text-xs">
                            <span id="btn-konfirmasi-text">Simpan & Cetak Slip</span>
                            <svg id="btn-konfirmasi-spinner" class="w-4 h-4 animate-spin hidden text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PENANGANAN KONEKSI TERPUTUS / STATUS IDEMPOTENCY -->
    <div id="modal-koneksi-terputus" class="hidden fixed inset-0 bg-slate-900/80 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden p-6 border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 text-center mb-1">Koneksi Terputus / Belum Menerima Respons</h3>
                <p class="text-xs text-slate-500 text-center mb-4">
                    Permintaan transaksi telah dikirim. Server mungkin telah berhasil mencatat transaksi ini. <strong>Jangan mengulangi transaksi baru secara sembarangan</strong> untuk menghindari mutasi ganda.
                </p>

                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs mb-5">
                    <p class="text-slate-500 font-medium">Kunci Idempotensi Permintaan:</p>
                    <p class="font-mono text-[11px] text-slate-800 break-all mt-0.5" id="display-lost-key">-</p>
                </div>

                <div class="space-y-2">
                    <button type="button" onclick="cekStatusTransaksiTerakhir()" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg transition text-xs flex items-center justify-center gap-2">
                        <span>Periksa Status Transaksi Terakhir</span>
                    </button>
                    <button type="button" onclick="tutupModalKoneksi()" class="w-full bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2 px-4 rounded-lg transition text-xs">
                        Tutup & Coba Lagi Nanti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT VALIDASI & FITUR PERBANKAN -->
    <script>
        const saldoAwal = {{ (int) $nasabah->saldo }};
        const nisSaatIni = "{{ $nasabah->nis }}";
        const kasTersedia = {{ (int) ($kasTersedia ?? 0) }};
        const batasSaldoMengendap = {{ (int) $minSaldoMengendap }};
        const saldoTersedia = Math.max(0, saldoAwal - batasSaldoMengendap);
        let isSubmitting = false;

        // Fitur Auto-save draft
        document.addEventListener('DOMContentLoaded', function() {
            let draftNis = localStorage.getItem('draft_nis');
            if(draftNis === nisSaatIni) {
                if(localStorage.getItem('draft_jenis')) {
                    document.getElementById('jenis_transaksi').value = localStorage.getItem('draft_jenis');
                    toggleKoreksi();
                }
                if(localStorage.getItem('draft_nominal')) {
                    document.getElementById('nominal').value = localStorage.getItem('draft_nominal');
                }
            }
            validasiSaldo();
        });

        function simpanDraft() {
            localStorage.setItem('draft_nis', nisSaatIni);
            localStorage.setItem('draft_jenis', document.getElementById('jenis_transaksi').value);
            localStorage.setItem('draft_nominal', document.getElementById('nominal').value);
        }

        // Fitur Bantuan Hitung Lembaran
        function toggleKalkulator() {
            const panel = document.getElementById('kalkulator-panel');
            const nominalInput = document.getElementById('nominal');

            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                nominalInput.setAttribute('readonly', true);
                nominalInput.classList.add('bg-slate-100');
            } else {
                panel.classList.add('hidden');
                nominalInput.removeAttribute('readonly');
                nominalInput.classList.remove('bg-slate-100');

                document.querySelectorAll('.pecahan-input').forEach(input => input.value = '');
                document.getElementById('total-text').innerText = 'Rp 0';
                validasiSaldo();
                simpanDraft();
            }
        }

        function hitungTotalPecahan() {
            let total = 0;
            const inputs = document.querySelectorAll('.pecahan-input');

            inputs.forEach(input => {
                const nilai = parseInt(input.getAttribute('data-nilai'));
                const lembar = parseInt(input.value) || 0;
                total += (nilai * lembar);
            });

            document.getElementById('total-text').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);

            if(total > 0) {
                document.getElementById('nominal').value = total;
            } else {
                document.getElementById('nominal').value = '';
            }

            validasiSaldo();
            simpanDraft();
        }

        // Toggle Tampilan Input Alasan Koreksi & Otorisasi PIN Nasabah
        function toggleKoreksi() {
            const jenis = document.getElementById('jenis_transaksi').value;
            const box = document.getElementById('kotak-alasan-koreksi');
            const alasanInput = document.getElementById('alasan_koreksi');
            const boxPin = document.getElementById('kotak-pin-nasabah');
            const pinInput = document.getElementById('pin_nasabah');

            if (jenis === 'koreksi_setor' || jenis === 'koreksi_tarik') {
                box.classList.remove('hidden');
                alasanInput.setAttribute('required', 'true');
                alasanInput.focus();
            } else {
                box.classList.add('hidden');
                alasanInput.removeAttribute('required');
                alasanInput.value = '';
            }

            // Otorisasi Form Verifikasi PIN Keamanan Nasabah (Wajib Khusus Penarikan Tunai)
            if (jenis === 'tarik') {
                boxPin.classList.remove('hidden');
                pinInput.setAttribute('required', 'true');
            } else {
                boxPin.classList.add('hidden');
                pinInput.removeAttribute('required');
                pinInput.value = '';
            }
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // Validasi Saldo & Kas Fisik Real-time
        function validasiSaldo() {
            const jenis = document.getElementById('jenis_transaksi').value;
            const nominal = parseInt(document.getElementById('nominal').value) || 0;
            const btnProses = document.getElementById('btn-proses');
            const pesanError = document.getElementById('pesan-error');
            const teksError = document.getElementById('teks-error');

            if (jenis === 'tarik' && nominal > saldoTersedia) {
                teksError.innerText = "Penarikan ditolak. Saldo tersedia nasabah hanya Rp " + formatRupiah(saldoTersedia) + ".";
                pesanError.classList.remove('hidden');
                btnProses.disabled = true;
                btnProses.classList.add('opacity-50', 'cursor-not-allowed');
            } else if ((jenis === 'tarik' || jenis === 'koreksi_setor') && nominal > kasTersedia) {
                teksError.innerHTML = "Penarikan tidak dapat diproses. Kas operasional teller tidak mencukupi (Tersedia: Rp " + formatRupiah(kasTersedia) + "). Silakan lakukan pengajuan droping kas kepada Supervisor.";
                pesanError.classList.remove('hidden');
                btnProses.disabled = true;
                btnProses.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (jenis === 'koreksi_setor' && nominal > saldoAwal) {
                teksError.innerText = "Gagal! Saldo nasabah (Rp " + formatRupiah(saldoAwal) + ") tidak mencukupi untuk koreksi setoran.";
                pesanError.classList.remove('hidden');
                btnProses.disabled = true;
                btnProses.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (jenis === 'admin' && nominal > saldoAwal) {
                teksError.innerText = "Gagal! Saldo tidak cukup dipotong biaya administrasi.";
                pesanError.classList.remove('hidden');
                btnProses.disabled = true;
                btnProses.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                pesanError.classList.add('hidden');
                if(jenis !== '' && nominal >= 1000) {
                    btnProses.disabled = false;
                    btnProses.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    btnProses.disabled = true;
                    btnProses.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
        }

        // Buka Modal Konfirmasi
        function bukaModal() {
            const jenis = document.getElementById('jenis_transaksi').value;
            const nominal = parseInt(document.getElementById('nominal').value) || 0;
            const alasanInput = document.getElementById('alasan_koreksi');
            const pinInput = document.getElementById('pin_nasabah');

            if(jenis === '' || nominal < 1000) {
                alert('Pilih jenis transaksi dan masukkan nominal minimal Rp 1.000!');
                return;
            }

            if(jenis === 'tarik') {
                if(nominal > saldoTersedia) {
                    alert('Penarikan ditolak. Saldo tersedia nasabah hanya Rp ' + formatRupiah(saldoTersedia) + '.');
                    return;
                }
                if(nominal > kasTersedia) {
                    alert('Penarikan tidak dapat diproses. Kas operasional teller tidak mencukupi. Silakan lakukan pengajuan droping kas kepada Supervisor.');
                    return;
                }
                if(!pinInput.value || pinInput.value.length < 6) {
                    alert('Nasabah wajib memasukkan 6 digit PIN Keamanan sebelum penarikan diproses!');
                    pinInput.focus();
                    return;
                }
            }

            if((jenis === 'koreksi_setor' || jenis === 'koreksi_tarik') && !alasanInput.value.trim()) {
                alert('Peraturan Audit: Wajib mengisi alasan koreksi transaksi sebelum memproses!');
                alasanInput.focus();
                return;
            }

            let saldoAkhir = 0;
            let badgeSandi = 'SANDI [100]';
            if (jenis === 'setor') {
                saldoAkhir = saldoAwal + nominal;
                badgeSandi = 'SANDI [101]';
                document.getElementById('preview-jenis').innerText = 'Setoran Tunai [101]';
                document.getElementById('preview-jenis').className = 'text-base font-bold text-emerald-700';
            } else if (jenis === 'koreksi_tarik') {
                saldoAkhir = saldoAwal + nominal;
                badgeSandi = 'SANDI [202]';
                document.getElementById('preview-jenis').innerText = 'Koreksi Penarikan [202]';
                document.getElementById('preview-jenis').className = 'text-base font-bold text-emerald-700';
            } else if (jenis === 'tarik') {
                saldoAkhir = saldoAwal - nominal;
                badgeSandi = 'SANDI [201]';
                document.getElementById('preview-jenis').innerText = 'Penarikan Tunai [201]';
                document.getElementById('preview-jenis').className = 'text-base font-bold text-rose-700';
            } else if (jenis === 'koreksi_setor') {
                saldoAkhir = saldoAwal - nominal;
                badgeSandi = 'SANDI [102]';
                document.getElementById('preview-jenis').innerText = 'Koreksi Setoran [102] (Uang Palsu/Robek)';
                document.getElementById('preview-jenis').className = 'text-base font-bold text-rose-700';
            } else {
                saldoAkhir = saldoAwal - nominal;
                badgeSandi = 'SANDI [301]';
                document.getElementById('preview-jenis').innerText = 'Biaya Administrasi Buku [301]';
                document.getElementById('preview-jenis').className = 'text-base font-bold text-amber-700';
            }

            document.getElementById('badge-kode-sandi').innerText = badgeSandi;
            document.getElementById('preview-nominal').innerText = 'Rp ' + formatRupiah(nominal);
            document.getElementById('preview-saldo-akhir').innerText = 'Rp ' + formatRupiah(saldoAkhir);

            document.getElementById('modal-konfirmasi').classList.remove('hidden');
            setTimeout(() => { document.getElementById('btn-konfirmasi-final').focus(); }, 100);
        }

        function tutupModal() {
            document.getElementById('modal-konfirmasi').classList.add('hidden');
            document.getElementById('nominal').focus();
        }

        // Eksekusi Form Final dengan Idempotensi & Loading State
        function submitFormFinal() {
            if (isSubmitting) return;
            isSubmitting = true;

            const btnFinal = document.getElementById('btn-konfirmasi-final');
            const btnText = document.getElementById('btn-konfirmasi-text');
            const spinner = document.getElementById('btn-konfirmasi-spinner');
            const btnProses = document.getElementById('btn-proses');

            btnFinal.disabled = true;
            btnProses.disabled = true;
            btnText.innerText = 'Memproses Transaksi...';
            spinner.classList.remove('hidden');

            localStorage.removeItem('draft_nis');
            localStorage.removeItem('draft_jenis');
            localStorage.removeItem('draft_nominal');

            // Timer cadangan: jika koneksi macet selama 15 detik, tawarkan pengecekan status
            setTimeout(function() {
                if (isSubmitting) {
                    const currentKey = document.getElementById('idempotency_key').value;
                    document.getElementById('display-lost-key').innerText = currentKey;
                    document.getElementById('modal-koneksi-terputus').classList.remove('hidden');
                }
            }, 15000);

            document.getElementById('form-transaksi').submit();
        }

        function tutupModalKoneksi() {
            document.getElementById('modal-koneksi-terputus').classList.add('hidden');
            isSubmitting = false;
            const btnFinal = document.getElementById('btn-konfirmasi-final');
            const btnText = document.getElementById('btn-konfirmasi-text');
            const spinner = document.getElementById('btn-konfirmasi-spinner');
            btnFinal.disabled = false;
            btnText.innerText = 'Simpan & Cetak Slip';
            spinner.classList.add('hidden');
        }

        // Cek Status Transaksi ke Server via Idempotency Key
        function cekStatusTransaksiTerakhir() {
            const key = document.getElementById('idempotency_key').value;
            fetch("{{ url('/teller/transaksi/status-idempotency') }}/" + key, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    alert('Transaksi TELAH TERCATAT di server: ' + data.data.kode_referensi + ' (' + data.data.status.toUpperCase() + '). Anda akan dialihkan ke dashboard.');
                    window.location.href = "{{ url('/teller/dashboard') }}";
                } else {
                    alert('Transaksi belum tersimpan di server. Anda dapat mencoba mengirimkan kembali form.');
                    tutupModalKoneksi();
                }
            })
            .catch(err => {
                alert('Gagal menghubungi server untuk verifikasi status: ' + err.message + '. Silakan periksa koneksi internet Anda.');
            });
        }

        function cekEnter(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!document.getElementById('btn-proses').disabled) {
                    bukaModal();
                }
            }
        }

        document.addEventListener('keydown', function(event) {
            if(event.key === "F2") {
                event.preventDefault();
                const nom = document.getElementById('nominal');
                nom.focus();
                nom.select();
            }

            if(event.key === "Escape"){
                if(!document.getElementById('modal-konfirmasi').classList.contains('hidden')){
                    tutupModal();
                } else if(!document.getElementById('modal-koneksi-terputus').classList.contains('hidden')){
                    tutupModalKoneksi();
                } else {
                    window.location.href = "{{ url('/teller/dashboard') }}";
                }
            }
        });
    </script>
</body>
</html>
