<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Nasabah - LKM Mitra Siswa Abadi</title>
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
        @if(!$nasabah)
            <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 mb-6 rounded shadow-sm">
                <p class="font-bold">Data Nasabah Belum Terhubung</p>
                <p class="text-sm">Akun pengguna ini belum memiliki data profil rekening nasabah.</p>
            </div>
        @else
            <!-- KARTU SAMBUTAN & SALDO -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- INFO REKENING -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden md:col-span-2 flex flex-col justify-between">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-slate-700 rounded-full opacity-20 pointer-events-none"></div>
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs uppercase tracking-widest text-slate-400 font-bold">Kartu Rekening Bank Mini</span>
                            <span class="text-xs font-semibold text-emerald-400 uppercase">{{ $nasabah->status ?? 'Aktif' }}</span>
                        </div>
                        <p class="text-2xl font-bold tracking-tight mb-1">{{ $nasabah->nama_siswa }}</p>
                        <p class="text-sm text-slate-400 tabular-nums">No. Rek: <span class="text-white font-semibold">{{ $nasabah->no_rekening }}</span> | NIS: <span class="text-white">{{ $nasabah->nis }}</span></p>
                        <p class="text-xs text-slate-400 mt-1">{{ $nasabah->kelas }} - {{ $nasabah->jurusan }}</p>
                    </div>

                    <div class="mt-8 pt-4 border-t border-slate-700/60 flex items-end justify-between">
                        <div>
                            <p class="text-xs text-slate-400 font-medium">Saldo Tabungan Saat Ini</p>
                            <p class="text-3xl font-bold text-emerald-400 mt-0.5 tabular-nums">Rp {{ number_format($nasabah->saldo ?? 0, 0, ',', '.') }}</p>
                        </div>
                        <a href="{{ url('/buku-tabungan/'.$nasabah->id) }}" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-medium px-4 py-2 rounded-lg transition shadow-sm flex items-center">
                            <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Buka Buku Tabungan
                        </a>
                    </div>
                </div>

                <!-- INFO PINTASAN & STATISTIK -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 mb-3">Informasi Nasabah</h2>
                        <dl class="space-y-2.5 text-xs">
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Kategori</dt>
                                <dd class="font-bold text-slate-800 uppercase">{{ $nasabah->kategori ?? 'Siswa' }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Kelas</dt>
                                <dd class="font-bold text-slate-800">{{ $nasabah->kelas }}</dd>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-2">
                                <dt class="text-slate-500">Jurusan</dt>
                                <dd class="font-bold text-slate-800">{{ $nasabah->jurusan }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Total Transaksi</dt>
                                <dd class="font-bold text-slate-800">{{ count($transaksi) }} kali</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <a href="{{ url('/buku-tabungan/cetak/'.$nasabah->id) }}" target="_blank" class="w-full bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-medium py-2 rounded-lg text-center block transition shadow-sm">
                            Cetak Lembar Buku
                        </a>
                    </div>
                </div>
            </div>

            <!-- TABEL 10 TRANSAKSI TERAKHIR -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">10 Mutasi Transaksi Terakhir</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-700 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Sandi</th>
                                <th class="px-6 py-3 text-right">Nominal</th>
                                <th class="px-6 py-3 text-right">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($transaksi as $t)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-3 text-xs text-slate-500 tabular-nums">{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-3">
                                        @if(strtolower($t->jenis_transaksi) === 'setor' || strtolower($t->jenis_transaksi) === 'setoran')
                                            <span class="text-xs font-semibold text-emerald-700">Setoran</span>
                                        @else
                                            <span class="text-xs font-semibold text-rose-700">Penarikan</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 tabular-nums text-xs font-medium text-slate-600">{{ $t->kode_sandi ?? '-' }}</td>
                                    <td class="px-6 py-3 text-right tabular-nums font-semibold {{ (strtolower($t->jenis_transaksi) === 'setor' || strtolower($t->jenis_transaksi) === 'setoran') ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ (strtolower($t->jenis_transaksi) === 'setor' || strtolower($t->jenis_transaksi) === 'setoran') ? '+' : '-' }} Rp {{ number_format($t->nominal ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3 text-right tabular-nums font-semibold text-slate-800">
                                        Rp {{ number_format($t->saldo_akhir ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-sm">Belum ada riwayat transaksi mutasi buku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </main>
</body>
</html>
