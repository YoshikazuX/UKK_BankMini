<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Transaksi - E-Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans" style="scrollbar-gutter: stable; overflow-y: scroll;">

    <!-- NAVBAR TELLER KONSISTEN DENGAN ADMIN -->
    <nav class="bg-slate-800 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-xl font-bold tracking-wider whitespace-nowrap shrink-0">LKM Mitra Siswa Abadi</div>
                <a href="{{ url('/teller/dashboard') }}" class="text-slate-300 hover:text-white font-medium text-sm transition whitespace-nowrap focus:outline-none">&larr; Batal & Kembali (Esc)</a>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8 relative">
        @if(session('error'))
        <!-- INDIKATOR VISUAL MERAH -->
        <div class="bg-rose-100 border-l-4 border-rose-600 text-rose-800 p-4 mb-6 rounded shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-bold text-lg">TRANSAKSI DITOLAK / GAGAL</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8">
            <div class="mb-6 border-b border-slate-200 pb-4">
                <h1 class="text-2xl font-bold text-slate-800">Form Transaksi Nasabah</h1>
            </div>

            <!-- INFO NASABAH & KAS LOKET -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-slate-500 text-xs">Nama Siswa / Nasabah</p>
                        <p class="font-bold text-base text-emerald-900" id="nama_nasabah">{{ $nasabah->nama_siswa }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Nomor Rekening / NIS</p>
                        <p class="font-bold text-base text-emerald-900 font-mono" id="nis_nasabah">{{ $nasabah->no_rekening ?? $nasabah->nis }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Saldo Rekening Saat Ini</p>
                        <p class="font-bold text-emerald-900 text-base">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs">Kas Fisik Loket Tersedia</p>
                        <div class="flex items-center space-x-1.5">
                            <p class="font-black text-base {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-700' : 'text-slate-900' }}" id="display-kas-tersedia">
                                Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}
                            </p>
                            @if(($kasTersedia ?? 0) <= 0)
                                <span class="bg-rose-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded">HABIS</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- PERINGATAN KAS MENIPIS / HABIS DENGAN SHORTCUT PENGAJUAN KAS -->
            @if(($kasTersedia ?? 0) < 500000 || session('kas_habis'))
            <div class="p-3.5 mb-6 rounded-lg border flex items-center justify-between {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-amber-50 border-amber-300 text-amber-900' }}">
                <div class="flex items-center text-xs">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0 {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>
                        @if(($kasTersedia ?? 0) <= 0)
                            <strong>Uang Kas di Loket Habis!</strong> Penarikan tunai tidak dapat diproses sampai pengajuan kas disetujui.
                        @else
                            <strong>Kas Fisik Menipis:</strong> Tersedia Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}. Pastikan kas cukup sebelum melayani penarikan besar.
                        @endif
                    </span>
                </div>
                <a href="{{ url('/teller/pengajuan-kas') }}" class="font-bold text-xs px-3 py-1.5 rounded transition shadow-sm flex items-center flex-shrink-0 ml-3 {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-700 hover:bg-rose-800 text-white' : 'bg-amber-600 hover:bg-amber-700 text-white' }}">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Ajukan Kas &rarr;
                </a>
            </div>
            @endif

            <!-- FORM UTAMA -->
            <form id="form-transaksi" action="{{ url('/teller/transaksi/proses') }}" method="POST">
                @csrf
                <input type="hidden" name="nasabah_id" value="{{ $nasabah->id }}">
                <!-- MITIGASI TEKNIS: IDEMPOTENCY KEY UNTUK MENCEGAH TRANSAKSI GANDA SAAT KONEKSI PUTUS -->
                <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::random(32) }}">

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-bold mb-2">Pilih Jenis Transaksi (Gunakan Panah Bawah & Tab)</label>
                    <select name="jenis_transaksi" id="jenis_transaksi" onchange="toggleKoreksi(); simpanDraft(); validasiSaldo();" class="shadow-sm border border-slate-300 rounded w-full py-3 px-4 text-slate-700 font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500 text-lg" autofocus required>
                        <option value="">-- PILIH TRANSAKSI --</option>
                        <option value="setor">SETOR TUNAI [101] (Menabung)</option>
                        <option value="tarik">TARIK TUNAI [201] (Ambil Uang)</option>
                        <option value="admin">BIAYA ADMINISTRASI [301]</option>
                        <option value="koreksi_setor">⚠️ KOREKSI SETORAN [102] (Batal Uang Palsu / Sobek / Salah Hitung)</option>
                        <option value="koreksi_tarik">⚠️ KOREKSI PENARIKAN [202] (Pembatalan Salah Debet)</option>
                    </select>
                </div>

                <!-- INPUT ALASAN KOREKSI (MUNCUL OTOMATIS SAAT KOREKSI DIPILIH) -->
                <div id="kotak-alasan-koreksi" class="hidden mb-5 p-4 bg-amber-50 border border-amber-300 rounded-lg">
                    <label class="block text-amber-900 text-xs font-bold uppercase mb-1">
                        ⚠️ Alasan Koreksi Transaksi (Wajib Peraturan Audit Perbankan) *
                    </label>
                    <input type="text" name="alasan_koreksi" id="alasan_koreksi" placeholder="Contoh: Ditemukan 1 lbr uang palsu Rp 50.000 / salah input nominal nasabah"
                           class="w-full text-sm border border-amber-300 rounded-lg p-2.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <p class="text-[11px] text-amber-800 mt-1">Alasan ini akan dicetak langsung pada slip bukti koreksi dan jurnal akuntansi pembukuan.</p>
                </div>

                <!-- FORM VERIFIKASI PIN KEAMANAN NASABAH (WAJIB OTORISASI PENARIKAN TUNAI) -->
                <div id="kotak-pin-nasabah" class="hidden mb-5 p-4 bg-amber-50 border-2 border-amber-400 rounded-xl shadow-sm">
                    <div class="flex items-center space-x-2 mb-2">
                        <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <label class="block text-amber-950 text-xs font-bold uppercase tracking-wider">
                            Otorisasi PIN Keamanan Nasabah (Wajib Penarikan Dana) *
                        </label>
                    </div>
                    <p class="text-xs text-amber-800 mb-2">
                        Demi keamanan rekening, minta nasabah untuk memasukkan 6 digit PIN kartu tabungan:
                    </p>
                    <div class="relative max-w-xs">
                        <input type="password" name="pin_nasabah" id="pin_nasabah" maxlength="6" placeholder="•••••• (Default: 123456)"
                               class="w-full text-center tracking-[0.4em] text-xl font-mono font-bold border-2 border-amber-300 rounded-xl p-2.5 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1.5">*Verifikasi PIN menjamin otorisasi resmi pemilik rekening pada loket harian.</p>
                </div>

                <div class="mb-8">
                    <!-- Label dan Tombol Kalkulator -->
                    <div class="flex justify-between items-end mb-2">
                        <label class="block text-slate-700 text-sm font-bold">Nominal Uang (Rp) <span class="text-xs text-emerald-600 font-normal font-mono">[Shortcut: F2]</span></label>
                        <button type="button" onclick="toggleKalkulator()" class="text-sm font-bold text-emerald-600 hover:text-emerald-800 flex items-center transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            Bantuan Hitung Lembaran
                        </button>
                    </div>

                    <!-- Input Nominal (Font dikembalikan ke ukuran wajar) -->
                    <input type="number" name="nominal" id="nominal" min="1000" oninput="simpanDraft(); validasiSaldo();" onkeypress="cekEnter(event)" class="shadow-sm border border-slate-300 rounded w-full py-3 px-4 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-xl font-bold bg-white" required placeholder="0">

                    <div id="pesan-error" class="hidden mt-2">
                        <p class="text-sm font-bold text-rose-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span id="teks-error">Peringatan Saldo</span>
                        </p>
                    </div>
                    <p class="text-sm text-slate-500 mt-2 font-medium">✨ <strong>Tips Navigasi Cepat:</strong> Tekan <kbd class="bg-slate-200 px-2 py-0.5 rounded border border-slate-300 text-xs font-mono font-bold">F2</kbd> untuk isi nominal, <kbd class="bg-slate-200 px-2 py-0.5 rounded border border-slate-300 text-xs font-mono font-bold">Enter</kbd> untuk simpan, <kbd class="bg-slate-200 px-2 py-0.5 rounded border border-slate-300 text-xs font-mono font-bold">Esc</kbd> untuk batal.</p>

                    <!-- KALKULATOR PECAHAN (Dimunculkan kembali) -->
                    <div id="kalkulator-panel" class="hidden mt-4 p-4 border border-emerald-200 bg-emerald-50 rounded-lg">
                        <p class="text-sm font-bold text-emerald-800 mb-3 border-b border-emerald-200 pb-2">Kalkulator Pecahan Uang Fisik</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            @foreach([100000, 50000, 20000, 10000, 5000, 2000, 1000, 500] as $pecahan)
                            <div class="flex items-center space-x-2 bg-white p-2 rounded border border-emerald-100 shadow-sm">
                                <span class="text-xs font-bold text-slate-600 w-16">Rp{{ number_format($pecahan, 0, ',', '.') }}</span>
                                <span class="text-xs text-slate-400">x</span>
                                <input type="number" min="0" class="pecahan-input w-full border border-slate-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500" data-nilai="{{ $pecahan }}" oninput="hitungTotalPecahan()" placeholder="0">
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-between items-center bg-emerald-100 p-3 rounded">
                            <span class="text-sm font-bold text-emerald-800">Total Terhitung:</span>
                            <span id="total-text" class="text-lg font-bold text-emerald-900">Rp 0</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-slate-200 pt-6">
                    <button type="button" id="btn-proses" onclick="bukaModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-8 rounded-lg shadow-md transition text-lg w-full sm:w-auto">
                        Validasi & Proses (Enter)
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- POP-UP KONFIRMASI (Sesuai usulan Teller) -->
    <div id="modal-konfirmasi" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
            <div class="bg-amber-400 px-6 py-4 border-b border-amber-500">
                <h3 class="text-xl font-black text-slate-900 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    KONFIRMASI TRANSAKSI
                </h3>
            </div>

            <div class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-slate-500 mb-1">Nama Nasabah</p>
                    <p class="text-2xl font-black text-slate-800">{{ $nasabah->nama_siswa }}</p>
                    <p class="text-sm font-bold text-slate-500 font-mono">{{ $nasabah->nis }}</p>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-slate-500 mb-1">Jenis Transaksi</p>
                    <p class="text-xl font-black text-slate-800 uppercase" id="preview-jenis">-</p>
                </div>

                <div class="mb-6 p-4 bg-slate-100 rounded-lg border border-slate-300">
                    <p class="text-sm text-slate-500 mb-1">Nominal Transaksi</p>
                    <p class="text-4xl font-black text-emerald-600 mb-4" id="preview-nominal">Rp 0</p>

                    <div class="border-t border-slate-300 pt-3 flex justify-between items-center">
                        <p class="text-sm font-bold text-slate-600">PREVIEW SALDO AKHIR :</p>
                        <p class="text-lg font-black text-slate-800" id="preview-saldo-akhir">Rp 0</p>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="tutupModal()" class="w-1/3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold py-3 px-4 rounded-lg transition">
                        Batal
                    </button>
                    <button type="button" id="btn-konfirmasi-final" onclick="submitFormFinal()" class="w-2/3 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-4 rounded-lg shadow-md transition text-lg flex justify-center items-center">
                        SIMPAN SEKARANG
                    </button>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- SCRIPT GABUNGAN -->
    <script>
        const saldoAwal = {{ $nasabah->saldo }};
        const nisSaatIni = "{{ $nasabah->nis }}";
        const kasTersedia = {{ $kasTersedia ?? 0 }};

        // Fitur Auto-save
        document.addEventListener('DOMContentLoaded', function() {
            let draftNis = localStorage.getItem('draft_nis');
            if(draftNis === nisSaatIni) {
                if(localStorage.getItem('draft_jenis')) {
                    document.getElementById('jenis_transaksi').value = localStorage.getItem('draft_jenis');
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

        // Fitur Kalkulator (Dikembalikan)
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

        // Shortcut & Validasi
        function cekEnter(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!document.getElementById('btn-proses').disabled) {
                    bukaModal();
                }
            }
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID').format(angka);
        }

        function validasiSaldo() {
            const jenis = document.getElementById('jenis_transaksi').value;
            const nominal = parseInt(document.getElementById('nominal').value) || 0;
            const btnProses = document.getElementById('btn-proses');
            const pesanError = document.getElementById('pesan-error');
            const teksError = document.getElementById('teks-error');
            const batasSaldoMengendap = {{ \App\Helpers\SettingHelper::getSaldoMengendapMin() }};

            let sisaSaldoSetelahTarik = saldoAwal - nominal;

            if (jenis === 'tarik' && sisaSaldoSetelahTarik < batasSaldoMengendap) {
                teksError.innerText = "Gagal! Sisa saldo minimal nasabah harus Rp " + formatRupiah(batasSaldoMengendap) + ".";
                pesanError.classList.remove('hidden');
                btnProses.disabled = true;
                btnProses.classList.add('opacity-50', 'cursor-not-allowed');
            } else if ((jenis === 'tarik' || jenis === 'koreksi_setor') && nominal > kasTersedia) {
                // ATURAN PROTEKSI KAS MINUS
                teksError.innerHTML = "Gagal! Kas fisik loket tidak cukup (Sisa kas tersedia: Rp " + formatRupiah(kasTersedia) + "). Kas dilarang minus! <a href='{{ url('/teller/pengajuan-kas') }}' class='underline font-black text-rose-700 ml-1'>Ajukan Tambahan Kas &rarr;</a>";
                pesanError.classList.remove('hidden');
                btnProses.disabled = true;
                btnProses.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (jenis === 'koreksi_setor' && nominal > saldoAwal) {
                teksError.innerText = "Gagal! Saldo nasabah (Rp " + formatRupiah(saldoAwal) + ") tidak mencukupi untuk koreksi setoran.";
                pesanError.classList.remove('hidden');
                btnProses.disabled = true;
                btnProses.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (jenis === 'admin' && nominal > saldoAwal) {
                teksError.innerText = "Gagal! Saldo tidak cukup dipotong admin.";
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

        // Modal Konfirmasi
        function bukaModal() {
            const jenis = document.getElementById('jenis_transaksi').value;
            const nominal = parseInt(document.getElementById('nominal').value) || 0;
            const alasanInput = document.getElementById('alasan_koreksi');

            if(jenis === '' || nominal < 1000) {
                alert('Pilih jenis transaksi dan masukkan nominal minimal Rp 1.000!');
                return;
            }

            if((jenis === 'tarik' || jenis === 'koreksi_setor') && nominal > kasTersedia) {
                alert('Uang kas fisik di loket tidak mencukupi untuk penarikan sebesar Rp ' + formatRupiah(nominal) + ' (Sisa kas tersedia: Rp ' + formatRupiah(kasTersedia) + '). Kas dilarang minus! Silakan buat Pengajuan Kas.');
                return;
            }

            if((jenis === 'koreksi_setor' || jenis === 'koreksi_tarik') && !alasanInput.value.trim()) {
                alert('Peraturan Audit: Wajib mengisi alasan koreksi transaksi sebelum memproses!');
                alasanInput.focus();
                return;
            }

            let saldoAkhir = 0;
            if (jenis === 'setor') {
                saldoAkhir = saldoAwal + nominal;
                document.getElementById('preview-jenis').innerText = 'SETOR TUNAI [101]';
                document.getElementById('preview-jenis').className = 'text-xl font-black uppercase text-emerald-600';
            } else if (jenis === 'koreksi_tarik') {
                saldoAkhir = saldoAwal + nominal;
                document.getElementById('preview-jenis').innerText = 'KOREKSI PENARIKAN [202]';
                document.getElementById('preview-jenis').className = 'text-xl font-black uppercase text-emerald-600';
            } else if (jenis === 'tarik') {
                saldoAkhir = saldoAwal - nominal;
                document.getElementById('preview-jenis').innerText = 'TARIK TUNAI [201]';
                document.getElementById('preview-jenis').className = 'text-xl font-black uppercase text-rose-600';
            } else if (jenis === 'koreksi_setor') {
                saldoAkhir = saldoAwal - nominal;
                document.getElementById('preview-jenis').innerText = 'KOREKSI SETORAN [102] (UANG PALSU/ROBEK)';
                document.getElementById('preview-jenis').className = 'text-xl font-black uppercase text-rose-600';
            } else {
                saldoAkhir = saldoAwal - nominal;
                document.getElementById('preview-jenis').innerText = 'BIAYA ADMIN [301]';
                document.getElementById('preview-jenis').className = 'text-xl font-black uppercase text-amber-600';
            }

            document.getElementById('preview-nominal').innerText = 'Rp ' + formatRupiah(nominal);
            document.getElementById('preview-saldo-akhir').innerText = 'Rp ' + formatRupiah(saldoAkhir);

            document.getElementById('modal-konfirmasi').classList.remove('hidden');
            setTimeout(() => { document.getElementById('btn-konfirmasi-final').focus(); }, 100);
        }

        function tutupModal() {
            document.getElementById('modal-konfirmasi').classList.add('hidden');
            document.getElementById('nominal').focus();
        }

        function submitFormFinal() {
            localStorage.removeItem('draft_nis');
            localStorage.removeItem('draft_jenis');
            localStorage.removeItem('draft_nominal');

            document.getElementById('btn-konfirmasi-final').innerText = 'Memproses...';
            document.getElementById('btn-konfirmasi-final').disabled = true;

            document.getElementById('form-transaksi').submit();
        }

        document.addEventListener('keydown', function(event){
            // Shortcut F2 untuk fokus ke input nominal
            if(event.key === "F2") {
                event.preventDefault();
                const nom = document.getElementById('nominal');
                nom.focus();
                nom.select();
            }

            if(event.key === "Escape"){
                if(!document.getElementById('modal-konfirmasi').classList.contains('hidden')){
                    tutupModal();
                } else {
                    window.location.href = "{{ url('/teller/dashboard') }}";
                }
            }
        });
    </script>
</body>
</html>
