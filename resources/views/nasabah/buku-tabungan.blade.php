<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tabungan Nasabah - {{ $nasabah->nama_siswa }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
        .tabel-buku {
            border-collapse: collapse;
            width: 100%;
        }
        .tabel-buku th {
            background-color: #1e3a8a;
            color: white;
            border: 1px solid #172554;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            padding: 8px 10px;
        }
        .tabel-buku td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
            font-size: 0.8125rem;
        }
        .tabel-buku tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .tabel-buku tr:hover {
            background-color: #eff6ff;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .buku-container { box-shadow: none; border: none; margin: 0; padding: 0; width: 100%; }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 min-h-screen flex flex-col">

    <!-- NAVBAR KONSISTEN (SESUAI SS) -->
    <nav class="bg-slate-800 text-white shadow-md relative no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="text-lg xl:text-xl font-bold tracking-wider whitespace-nowrap shrink-0 mr-4">LKM Mitra Siswa Abadi</div>
                @if(Auth::user()->role === 'teller')
                    <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/teller/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/teller/riwayat') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Riwayat Saya</a>
                        <a href="{{ url('/teller/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Tutup Kas</a>
                        <a href="{{ url('/teller/pengajuan-kas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Pengajuan Kas</a>
                        <a href="{{ url('/teller/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                            <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                            Absensi Avalis
                        </a>
                @elseif(Auth::user()->role === 'supervisor')
                    <div class="flex items-center space-x-2.5 lg:space-x-3.5 xl:space-x-5 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/supervisor/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/supervisor/laporan') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Laporan Transaksi</a>
                        <a href="{{ url('/supervisor/daily-report') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Daily Report</a>
                        <a href="{{ url('/supervisor/pengajuan-kas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Pengajuan Kas</a>
                        <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                        <a href="{{ url('/laporan/saldo-produk') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Produk</a>
                        <a href="{{ url('/laporan/tahunan') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Tahunan</a>
                        <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Data Teller</a>
                        <a href="{{ url('/supervisor/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Absensi</a>
                @elseif(Auth::user()->role === 'nasabah')
                    <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/nasabah/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/buku-tabungan/'.$nasabah->id) }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Buku Tabungan</a>
                @else
                    <div class="flex items-center space-x-3 lg:space-x-5 xl:space-x-6 text-xs xl:text-sm font-medium">
                        <a href="{{ url('/admin/dashboard') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Dashboard</a>
                        <a href="{{ url('/admin/data-nasabah') }}" class="text-white font-medium underline decoration-2 underline-offset-4 whitespace-nowrap focus:outline-none">Manajemen Nasabah</a>
                        <a href="{{ url('/avalis/petugas') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Petugas Avalis</a>
                        <a href="{{ url('/laporan/jurnal') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jurnal</a>
                        <a href="{{ url('/admin/audit') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Jejak Audit</a>
                        <a href="{{ url('/admin/absensi-avalis') }}" class="text-slate-300 hover:text-white transition font-medium flex items-center whitespace-nowrap focus:outline-none">
                            <svg class="w-4 h-4 mr-1 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 14h-2v-4H6v-2h4V7h2v4h4v2h-4v4z"/></svg>
                            Absensi
                        </a>
                        <a href="{{ url('/admin/konfigurasi') }}" class="text-slate-300 hover:text-white transition font-medium whitespace-nowrap focus:outline-none">Konfigurasi</a>
                @endif

                    <!-- PROFIL DROPDOWN -->
                    <div class="relative ml-2 shrink-0">
                        <button onclick="toggleDropdown()" id="profile-btn" class="flex items-center space-x-2 focus:outline-none bg-slate-700 hover:bg-slate-600 py-1.5 px-3 rounded-full transition border border-slate-600 whitespace-nowrap">
                            @if(Auth::user()->foto ?? false)
                                <img src="{{ asset('storage/'.Auth::user()->foto) }}" alt="Foto" class="w-7 h-7 rounded-full object-cover">
                            @else
                                <div class="w-7 h-7 rounded-full bg-slate-500 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr(Auth::user()->nama_petugas ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-sm font-medium hidden sm:inline whitespace-nowrap">{{ Auth::user()->nama_petugas ?? 'Administrator Utama' }}</span>
                            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl py-2 text-slate-700 border border-slate-200 z-50">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs text-slate-400 uppercase tracking-wider font-bold">Masuk Sebagai</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->nama_petugas ?? 'Administrator Utama' }}</p>
                                <span class="inline-block bg-slate-100 text-slate-800 text-xs px-2 py-0.5 rounded-full font-bold mt-1 uppercase">{{ (Auth::user()->role ?? 'admin') === 'admin' ? 'ADMINISTRATOR' : strtoupper(Auth::user()->role ?? 'ADMINISTRATOR') }}</span>
                            </div>
                            <a href="{{ url((Auth::user()->role ?? 'admin') === 'supervisor' ? '/supervisor/profil' : ((Auth::user()->role ?? 'admin') === 'teller' ? '/teller/profil' : '/admin/profil')) }}" class="px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700 flex items-center font-medium">
                                <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg> Lihat Profil
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 flex items-center font-bold">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg> Keluar Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- KONTEN BUKU TABUNGAN NASABAH -->
    <main class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-600 text-emerald-800 p-4 mb-5 rounded shadow-sm no-print">
            {{ session('success') }}
        </div>
        @endif

        <!-- TOP BAR: TOMBOL KEMBALI & CETAK BUKU -->
        <div class="mb-5 flex flex-wrap justify-between items-center gap-3 no-print">
            <a href="javascript:history.back()" class="text-slate-600 hover:text-slate-900 text-sm font-semibold flex items-center">
                &larr; Kembali ke Daftar
            </a>

            <div class="flex items-center space-x-2">
                @if($role === 'teller')
                    <a href="{{ url('/teller/transaksi/'.$nasabah->id) }}" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buka Form Transaksi
                    </a>
                @endif

                <button onclick="window.print()" class="bg-blue-800 hover:bg-blue-900 text-white font-bold text-xs px-4 py-2 rounded-lg shadow transition flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Lembar Buku Tabungan
                </button>
            </div>
        </div>

        <!-- BUKU TABUNGAN CONTAINER -->
        <div class="buku-container bg-white rounded-xl shadow-lg border border-slate-300 overflow-hidden">

            <!-- HEADER BUKU TABUNGAN (WARNA BIRU KHAS BUKU TABUNGAN FISIK) -->
            <div class="bg-blue-900 text-white p-6 border-b-4 border-amber-400">
                <div class="flex flex-wrap justify-between items-start gap-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="bg-amber-400 text-blue-950 font-black text-xs px-2.5 py-0.5 rounded uppercase tracking-wider">BUKU TABUNGAN NASABAH</span>
                            <span class="text-blue-200 text-xs">Buku Besar Pembantu</span>
                        </div>
                        <h1 class="text-2xl font-black tracking-wide mt-1 uppercase">{{ $nasabah->nama_siswa }}</h1>
                        <p class="text-blue-200 text-xs mt-0.5">LKM MITRA SISWA ABADI • SMK MITRA SISWA ABADI</p>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-blue-300 font-medium">Saldo Tersedia Saat Ini</p>
                        <p class="text-2xl font-black font-mono text-amber-300">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</p>
                        <span class="inline-block bg-blue-800 text-blue-100 text-[11px] px-2.5 py-0.5 rounded font-mono mt-1 border border-blue-700">
                            No. Rek: {{ $nasabah->no_rekening ?? $nasabah->nis }}
                        </span>
                    </div>
                </div>

                <!-- KARTU DATA IDENTITAS NASABAH -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-4 border-t border-blue-800/80 text-xs">
                    <div>
                        <p class="text-blue-300">Nomor Induk Siswa (NIS)</p>
                        <p class="font-bold font-mono text-sm mt-0.5">{{ $nasabah->nis }}</p>
                    </div>
                    <div>
                        <p class="text-blue-300">Kelas & Jurusan</p>
                        <p class="font-bold text-sm mt-0.5">{{ $nasabah->kelas }} ({{ $nasabah->jurusan }})</p>
                    </div>
                    <div>
                        <p class="text-blue-300">Kategori Nasabah</p>
                        <p class="font-bold text-sm mt-0.5 uppercase">{{ $nasabah->kategori ?? 'Siswa' }}</p>
                    </div>
                    <div>
                        <p class="text-blue-300">Status Akun</p>
                        <div class="mt-0.5">
                            @if(($nasabah->status ?? 'aktif') === 'aktif')
                                <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400 px-2 py-0.5 rounded text-[11px] font-bold uppercase">Siswa Aktif</span>
                            @else
                                <span class="bg-amber-500/20 text-amber-300 border border-amber-400 px-2 py-0.5 rounded text-[11px] font-bold uppercase">Alumni / Lulus</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABEL MUTASI BUKU TABUNGAN -->
            <div class="p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h2 class="font-bold text-slate-800 text-sm uppercase tracking-wider flex items-center">
                        <svg class="w-4 h-4 mr-1.5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Lembar Mutasi Transaksi (Buku Besar Rekening)
                    </h2>
                    <span class="text-xs text-slate-500 font-medium">Total: {{ count($transaksi) }} Transaksi Tercatat</span>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="tabel-buku">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">No</th>
                                <th style="width: 130px;">Tanggal & Waktu</th>
                                <th>Keterangan / Sandi</th>
                                <th style="width: 140px; text-align: right;">Penarikan (Debit)</th>
                                <th style="width: 140px; text-align: right;">Setoran (Kredit)</th>
                                <th style="width: 150px; text-align: right;">Saldo Berjalan</th>
                                <th style="width: 110px; text-align: center;">Teller</th>
                                <th class="no-print" style="width: 90px; text-align: center;">Slip</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transaksi as $index => $trx)
                            <tr>
                                <td class="text-center font-mono text-slate-500">{{ $index + 1 }}</td>
                                <td class="font-mono text-xs whitespace-nowrap">{{ date('d/m/Y H:i', strtotime($trx->created_at)) }}</td>
                                <td>
                                    @if($trx->jenis_transaksi === 'setor')
                                        <span class="font-semibold text-emerald-800">Setoran Tunai</span>
                                    @elseif($trx->jenis_transaksi === 'tarik')
                                        <span class="font-semibold text-rose-800">Penarikan Tunai</span>
                                    @else
                                        <span class="font-semibold text-amber-800">Biaya Administrasi Buku</span>
                                    @endif
                                    <span class="text-[10px] text-slate-400 block font-mono">#TRX-{{ sprintf('%06d', $trx->id) }}</span>
                                </td>

                                <!-- Mutasi Debit (Penarikan) -->
                                <td class="text-right font-mono text-rose-700">
                                    @if($trx->jenis_transaksi !== 'setor')
                                        Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- Mutasi Kredit (Setoran) -->
                                <td class="text-right font-mono text-emerald-700 font-bold">
                                    @if($trx->jenis_transaksi === 'setor')
                                        Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <!-- Saldo Berjalan -->
                                <td class="text-right font-mono font-bold text-slate-900 bg-slate-50/50">
                                    Rp {{ number_format($trx->saldo_berjalan, 0, ',', '.') }}
                                </td>

                                <!-- Petugas / Teller -->
                                <td class="text-center text-xs text-slate-600 font-medium">
                                    {{ $trx->nama_teller ?? 'Teller' }}
                                </td>

                                <!-- Tombol Cetak Slip -->
                                <td class="no-print text-center">
                                    @if($trx->jenis_transaksi === 'setor')
                                        <a href="{{ url('/teller/slip-setor/'.$trx->id) }}" target="_blank" class="text-blue-700 hover:text-blue-900 text-xs font-bold hover:underline" title="Cetak Slip Setoran">
                                            Slip Setor
                                        </a>
                                    @else
                                        <a href="{{ url('/teller/slip-tarik/'.$trx->id) }}" target="_blank" class="text-rose-700 hover:text-rose-900 text-xs font-bold hover:underline" title="Cetak Slip Penarikan">
                                            Slip Tarik
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400 italic">
                                    Belum ada catatan mutasi transaksi pada rekening ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-100 font-bold text-xs text-slate-800">
                                <td colspan="3" class="text-right py-2 pr-3 uppercase">Total Akumulasi:</td>
                                <td class="text-right text-rose-700 font-mono pr-2">Rp {{ number_format($totalTarik, 0, ',', '.') }}</td>
                                <td class="text-right text-emerald-700 font-mono pr-2">Rp {{ number_format($totalSetor, 0, ',', '.') }}</td>
                                <td class="text-right text-slate-900 font-mono font-black pr-2">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- CATATAN ATURAN SALDO MINIMAL & SOP -->
                <div class="mt-5 p-3.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-900">
                    <p class="font-bold">Informasi Ketentuan Buku Tabungan Siswa:</p>
                    <ul class="list-disc list-inside mt-1 text-[11px] text-blue-800 space-y-0.5">
                        <li>Penarikan tunai wajib menyisakan <strong>saldo minimal Rp10.000,-</strong> di dalam rekening.</li>
                        <li>Setoran dicatat pada kolom <strong>Kredit</strong> dan Penarikan dicatat pada kolom <strong>Debit</strong>.</li>
                        <li>Pastikan membawa Buku Tabungan fisik setiap kali melakukan transaksi di loket Bank Mini.</li>
                    </ul>
                </div>
            </div>

        </div>

    </main>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
        }
        window.onclick = function(event) {
            const btn = document.getElementById('profile-btn');
            const dropdown = document.getElementById('profile-dropdown');
            if (btn && dropdown && !btn.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
