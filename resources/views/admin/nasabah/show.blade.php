<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rekening - {{ $nasabah->nama_siswa }} | Bank Mini Sekolah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">

    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        <!-- Navigasi Breadcrumb & Kembali -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="flex items-center space-x-2 text-sm text-slate-500">
                <a href="{{ url('/admin/data-nasabah') }}" class="hover:text-slate-900 transition flex items-center gap-1 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Daftar Nasabah
                </a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">Detail Nasabah</span>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ url('/buku-tabungan/'.$nasabah->id) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium py-2 px-3.5 rounded-lg text-xs transition shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    Buka Buku Tabungan
                </a>
                <a href="{{ url('/admin/edit-nasabah/'.$nasabah->id) }}" class="bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-3.5 rounded-lg text-xs transition shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Data
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 mb-6 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        <!-- HEADER KARTU PROFIL UTAMA -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-slate-200">
                <div class="flex items-start sm:items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-bold text-2xl shadow-md shrink-0">
                        {{ strtoupper(substr($nasabah->nama_siswa, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold text-slate-900">{{ $nasabah->nama_siswa }}</h1>
                            @if($nasabah->status === 'aktif')
                                <span class="text-xs font-semibold text-emerald-700">
                                    Rekening Aktif
                                </span>
                            @elseif($nasabah->status === 'nonaktif')
                                <span class="text-xs font-semibold text-rose-700">
                                    Nonaktif
                                </span>
                            @else
                                <span class="text-xs font-semibold text-amber-700">
                                    Arsip Alumni
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-2">
                            <span>No. Rekening: <strong class="text-slate-800 font-mono text-sm">{{ $nasabah->no_rekening }}</strong></span>
                            <span>&bull;</span>
                            <span>NIS/NIP: <strong class="text-slate-700">{{ $nasabah->nis }}</strong></span>
                            <span>&bull;</span>
                            <span>Kategori: <strong class="capitalize text-slate-700">{{ $nasabah->kategori ?? 'Siswa' }}</strong></span>
                        </p>
                    </div>
                </div>

                <!-- Quick Action Status Rekening -->
                <div class="flex items-center gap-2 shrink-0">
                    @if($nasabah->status === 'aktif')
                        <button type="button" onclick="bukaModalKonfirmasi('nonaktifkan')" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold px-3 py-2 rounded-lg transition shadow-sm">
                            Nonaktifkan Rekening
                        </button>
                    @else
                        <button type="button" onclick="bukaModalKonfirmasi('aktifkan')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-semibold px-3 py-2 rounded-lg transition shadow-sm">
                            Aktifkan Rekening
                        </button>
                    @endif
                </div>
            </div>

            <!-- GRID INFORMASI: IDENTITAS & SALDO REKENING -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">

                <!-- 1. KARTU POSISI SALDO -->
                <div class="lg:col-span-1 bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-xl p-6 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-300 mb-2">
                            <span>POSISI SALDO TABUNGAN</span>
                            <span class="font-mono bg-white/10 px-2 py-0.5 rounded text-[11px]">IDR</span>
                        </div>
                        <div class="text-3xl font-extrabold tracking-tight text-white mb-4">
                            Rp {{ number_format($saldoSaatIni, 0, ',', '.') }}
                        </div>
                    </div>

                    <div class="space-y-2.5 pt-4 border-t border-white/10 text-xs">
                        <div class="flex items-center justify-between text-slate-300">
                            <span>Batas Saldo Mengendap:</span>
                            <span class="font-semibold text-white font-mono">Rp {{ number_format($minMengendap, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-300">
                            <span>Saldo Dapat Ditarik:</span>
                            <span class="font-bold text-emerald-400 font-mono text-sm">Rp {{ number_format($saldoDapatDitarik, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 italic pt-1">
                            *Saldo mengendap diatur secara sistem untuk menjaga integritas rekening dan tidak dapat ditarik.
                        </p>
                    </div>
                </div>

                <!-- 2. DATA IDENTITAS DETAIL -->
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">Nama Lengkap</span>
                        <p class="text-sm font-bold text-slate-900">{{ $nasabah->nama_siswa }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">NIS / NIP</span>
                        <p class="text-sm font-bold text-slate-900 font-mono">{{ $nasabah->nis }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">Kelas & Jurusan</span>
                        <p class="text-sm font-semibold text-slate-800">{{ $nasabah->kelas }} &bull; {{ $nasabah->jurusan }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">Kategori Nasabah</span>
                        <p class="text-sm font-semibold text-slate-800 capitalize">{{ $nasabah->kategori ?? 'Siswa' }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">Alamat Email</span>
                        <p class="text-sm font-semibold text-slate-800">{{ $nasabah->email ?: '-' }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">Nomor Telepon / WhatsApp</span>
                        <p class="text-sm font-semibold text-slate-800 font-mono">{{ $nasabah->no_hp ?: '-' }}</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">Tanggal Buka Rekening</span>
                        <p class="text-sm font-semibold text-slate-800">{{ $nasabah->created_at ? $nasabah->created_at->isoFormat('D MMMM Y, HH:mm') : '-' }} WIB</p>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-500 font-medium block mb-1">Status Keamanan Akun</span>
                        <p class="text-xs font-semibold text-emerald-700 flex items-center gap-1.5 mt-0.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            PIN & Password Terenkripsi Aman
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIWAYAT TRANSAKSI DARI TABEL SEBENARNYA -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Riwayat Transaksi Tabungan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Seluruh mutasi tercatat secara atomik di database operasional perbankan mini.</p>
                </div>
                <span class="text-xs text-slate-500 font-medium">
                    Total Transaksi: {{ $transaksi->total() }} data
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Sandi</th>
                            <th class="py-3 px-4">Jenis Transaksi</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                            <th class="py-3 px-4">Petugas / Teller</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($transaksi as $t)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                                {{ $t->created_at ? $t->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                <span class="bg-slate-100 px-1.5 py-0.5 rounded text-[11px] border border-slate-200">
                                    {{ $t->kode_sandi ?? ($t->jenis_transaksi === 'setor' ? '101' : '201') }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($t->jenis_transaksi === 'setor')
                                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        Setor Tunai
                                    </span>
                                @elseif($t->jenis_transaksi === 'tarik')
                                    <span class="inline-flex items-center gap-1 font-semibold text-rose-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        Penarikan Tunai
                                    </span>
                                @elseif(str_contains($t->jenis_transaksi, 'koreksi'))
                                    <span class="inline-flex items-center gap-1 font-semibold text-amber-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Koreksi (Void)
                                    </span>
                                @else
                                    <span class="font-semibold text-slate-800 capitalize">{{ $t->jenis_transaksi }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-sm whitespace-nowrap {{ $t->jenis_transaksi === 'setor' ? 'text-emerald-700' : 'text-slate-800' }}">
                                {{ $t->jenis_transaksi === 'setor' ? '+' : '-' }} Rp {{ number_format($t->nominal, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="font-medium text-slate-800">{{ $t->petugas->nama_petugas ?? ($t->nama_petugas ?? 'Sistem') }}</span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if($t->status === 'sukses')
                                    <span class="text-xs font-semibold text-emerald-700">Sukses</span>
                                @elseif($t->status === 'dibatalkan')
                                    <span class="text-xs font-semibold text-rose-700">Dibatalkan</span>
                                @else
                                    <span class="text-xs font-semibold text-amber-700">{{ $t->status }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500 max-w-xs truncate" title="{{ $t->alasan_batal ?: ($t->jenis_layanan ?? '-') }}">
                                {{ $t->alasan_batal ?: ($t->jenis_layanan ?? '-') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 italic">
                                Belum ada riwayat transaksi pada rekening ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION RIWAYAT TRANSAKSI -->
            @if($transaksi->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $transaksi->links() }}
            </div>
            @endif
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI TINDAKAN PENTING (FEATURE 7) -->
    <!-- ========================================================================= -->
    <div id="modal-konfirmasi" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
                <div class="p-6">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-4" id="modal-icon-container">
                        <svg class="w-6 h-6" id="modal-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900" id="modal-title">Konfirmasi Tindakan</h3>
                    <p class="text-xs text-slate-600 mt-2" id="modal-message"></p>

                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs font-mono text-slate-700 mt-4">
                        <div>Nasabah: <strong>{{ $nasabah->nama_siswa }}</strong></div>
                        <div>No Rekening: <strong>{{ $nasabah->no_rekening }}</strong></div>
                        <div>Saldo: <strong>Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</strong></div>
                    </div>
                </div>

                <form id="form-konfirmasi" method="POST" action="{{ url('/admin/nasabah/ubah-status/'.$nasabah->id) }}" class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-2">
                    @csrf
                    <input type="hidden" name="status" id="input-status" value="">
                    <button type="button" onclick="tutupModalKonfirmasi()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium rounded-lg text-xs transition shadow-sm">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-konfirmasi" class="px-4 py-2 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2">
                        <span id="btn-konfirmasi-text">Ya, Lanjutkan</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bukaModalKonfirmasi(tipe) {
            const modal = document.getElementById('modal-konfirmasi');
            const title = document.getElementById('modal-title');
            const message = document.getElementById('modal-message');
            const inputStatus = document.getElementById('input-status');
            const btn = document.getElementById('btn-submit-konfirmasi');
            const btnText = document.getElementById('btn-konfirmasi-text');
            const iconContainer = document.getElementById('modal-icon-container');

            if (tipe === 'nonaktifkan') {
                title.textContent = 'Nonaktifkan Rekening?';
                message.textContent = 'Apakah Anda yakin ingin menonaktifkan rekening milik {{ $nasabah->nama_siswa }}? Nasabah tidak dapat melakukan transaksi tarik, setor, maupun mutasi selama status nonaktif.';
                inputStatus.value = 'nonaktif';
                btn.className = 'px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2';
                btnText.textContent = 'Ya, Nonaktifkan Rekening';
                iconContainer.className = 'w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4';
            } else {
                title.textContent = 'Aktifkan Kembali Rekening?';
                message.textContent = 'Apakah Anda yakin ingin mengaktifkan kembali rekening milik {{ $nasabah->nama_siswa }}? Rekening akan dapat kembali digunakan untuk transaksi pelayanan loket dan mobile.';
                inputStatus.value = 'aktif';
                btn.className = 'px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2';
                btnText.textContent = 'Ya, Aktifkan Rekening';
                iconContainer.className = 'w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4';
            }

            modal.classList.remove('hidden');
        }

        function tutupModalKonfirmasi() {
            document.getElementById('modal-konfirmasi').classList.add('hidden');
        }

        // Cegah Double Submit & Tampilkan Loading
        document.getElementById('form-konfirmasi').addEventListener('submit', function () {
            const btn = document.getElementById('btn-submit-konfirmasi');
            const btnText = document.getElementById('btn-konfirmasi-text');
            btn.disabled = true;
            btnText.textContent = 'Memproses...';
        });
    </script>
</body>
</html>

