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

    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 relative">
        @if(session('error'))
        <!-- INDIKATOR VISUAL MERAH -->
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-3 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <p class="font-bold text-base">Transaksi Ditolak / Gagal</p>
                <p class="text-sm font-normal text-rose-700">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <div class="flex items-center justify-between pb-6 mb-6 border-b border-slate-200">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Form Transaksi Nasabah</h1>
                    <p class="text-slate-500 text-sm mt-1">Lakukan penyetoran atau penarikan kas nasabah dengan validasi akurat.</p>
                </div>
                <a href="{{ url('/teller/dashboard') }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium px-4 py-2 rounded-lg text-sm transition">
                    &larr; Kembali ke Dashboard
                </a>
            </div>

            <!-- INFO NASABAH & KAS LOKET -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-slate-500 text-xs font-semibold">Nama Siswa / Nasabah</p>
                        <p class="font-bold text-slate-900 mt-1" id="nama_nasabah">{{ $nasabah->nama_siswa }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-semibold">Nomor Rekening / NIS</p>
                        <p class="font-bold text-slate-900 tabular-nums mt-1" id="nis_nasabah">{{ $nasabah->no_rekening ?? $nasabah->nis }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-semibold">Saldo Rekening Saat Ini</p>
                        <p class="font-bold text-slate-900 text-base tabular-nums mt-1">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-slate-500 text-xs font-semibold">Kas Fisik Loket Tersedia</p>
                        <div class="flex items-center space-x-1.5 mt-1">
                            <p class="font-bold text-base tabular-nums {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-700' : 'text-slate-900' }}" id="display-kas-tersedia">
                                Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}
                            </p>
                            @if(($kasTersedia ?? 0) <= 0)
                                <span class="bg-rose-100 text-rose-800 text-[10px] font-semibold px-1.5 py-0.5 rounded">Habis</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- PERINGATAN KAS MENIPIS / HABIS DENGAN SHORTCUT PENGAJUAN KAS -->
            @if(($kasTersedia ?? 0) < 500000 || session('kas_habis'))
            <div class="p-4 mb-6 rounded-xl border flex items-center justify-between {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-amber-50 border-amber-200 text-amber-900' }}">
                <div class="flex items-center text-xs">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0 {{ ($kasTersedia ?? 0) <= 0 ? 'text-rose-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>
                        @if(($kasTersedia ?? 0) <= 0)
                            <strong>Uang Kas di Loket Habis!</strong> Penarikan tunai tidak dapat diproses sampai pengajuan kas disetujui.
                        @else
                            <strong>Kas Fisik Menipis:</strong> Tersedia <span class="tabular-nums">Rp {{ number_format($kasTersedia ?? 0, 0, ',', '.') }}</span>. Pastikan kas cukup sebelum melayani penarikan besar.
                        @endif
                    </span>
                </div>
                <a href="{{ url('/teller/pengajuan-kas') }}" class="font-medium text-xs px-3.5 py-2 rounded-lg transition shadow-sm flex items-center flex-shrink-0 ml-3 {{ ($kasTersedia ?? 0) <= 0 ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-slate-900 hover:bg-slate-800 text-white' }}">
                    Ajukan Kas &rarr;
                </a>
            </div>
            @endif

            <!-- FORM UTAMA -->
            <form id="form-transaksi" action="{{ url('/teller/transaksi/proses') }}" method="POST">
                @csrf
                <input type="hidden" name="nasabah_id" value="{{ $nasabah->id }}">
                <input type="hidden" name="idempotency_key" value="{{ \Illuminate\Support\Str::random(32) }}">

                <div class="mb-5">
                    <label class="block text-slate-700 text-sm font-semibold mb-2">Pilih Jenis Transaksi</label>
                    <select name="jenis_transaksi" id="jenis_transaksi" onchange="toggleKoreksi(); simpanDraft(); validasiSaldo();" class="border border-slate-300 rounded-lg w-full py-2.5 px-3.5 text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-base" autofocus required>
                        <option value="">-- Pilih Jenis Transaksi --</option>
                        <option value="setor">Setor Tunai [101] (Menabung)</option>
                        <option value="tarik">Tarik Tunai [201] (Ambil Uang)</option>
                        <option value="admin">Biaya Administrasi [301]</option>
                        <option value="koreksi_setor">Koreksi Setoran [102] (Batal Uang Palsu / Sobek / Salah Hitung)</option>
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
                            Otorisasi PIN Keamanan Nasabah (Wajib Penarikan Dana) *
                        </label>
                    </div>
                    <p class="text-xs text-slate-600 mb-2">
                        Minta nasabah untuk memasukkan 6 digit PIN kartu tabungan:
                    </p>
                    <div class="relative max-w-xs">
                        <input type="password" name="pin_nasabah" id="pin_nasabah" maxlength="6" placeholder="••••••"
                               class="w-full text-center tracking-[0.4em] text-xl font-medium border border-slate-300 rounded-lg p-2 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 tabular-nums">
                    </div>
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

                    <div id="pesan-error" class="hidden mt-2">
                        <p class="text-sm font-medium text-rose-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
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

                <div class="flex items-center justify-end border-t border-slate-200 pt-6">
                    <button type="button" id="btn-proses" onclick="bukaModal()" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-6 rounded-lg shadow-sm transition text-sm w-full sm:w-auto">
                        Validasi & Proses Transaksi
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- POP-UP KONFIRMASI -->
    <div id="modal-konfirmasi" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
            <div class="bg-slate-900 text-white px-6 py-4">
                <h3 class="text-base font-semibold">
                    Konfirmasi Transaksi
                </h3>
            </div>

            <div class="p-6">
                <div class="mb-4">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Nama Nasabah</p>
                    <p class="text-xl font-bold text-slate-900">{{ $nasabah->nama_siswa }}</p>
                    <p class="text-sm font-semibold text-slate-500 tabular-nums">{{ $nasabah->nis }}</p>
                </div>

                <div class="mb-4">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Jenis Transaksi</p>
                    <p class="text-base font-bold text-slate-800 capitalize" id="preview-jenis">-</p>
                </div>

                <div class="mb-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-xs font-semibold text-slate-500 mb-1">Nominal Transaksi</p>
                    <p class="text-3xl font-bold text-slate-900 mb-4 tabular-nums" id="preview-nominal">Rp 0</p>

                    <div class="border-t border-slate-200 pt-3 flex justify-between items-center">
                        <p class="text-xs font-semibold text-slate-600">Preview Saldo Akhir:</p>
                        <p class="text-base font-bold text-slate-900 tabular-nums" id="preview-saldo-akhir">Rp 0</p>
                    </div>
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="tutupModal()" class="w-1/3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2.5 px-4 rounded-lg transition text-sm">
                        Batal
                    </button>
                    <button type="button" id="btn-konfirmasi-final" onclick="submitFormFinal()" class="w-2/3 bg-slate-900 hover:bg-slate-800 text-white font-medium py-2.5 px-4 rounded-lg shadow-sm transition text-sm flex justify-center items-center">
                        Simpan Transaksi
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
