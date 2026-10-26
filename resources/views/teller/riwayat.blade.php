<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - E-Teller</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
            overflow-y: scroll;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans">

    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 p-4 mb-6 rounded-r shadow-sm text-sm font-medium">
            {{ session('error') }}
        </div>
        @endif

        <div class="mb-6 border-b border-slate-200 pb-6">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Transaksi Saya</h1>
            <p class="text-slate-500 text-sm mt-1">Gunakan tombol <strong>Koreksi/Void</strong> jika terjadi kesalahan input. Wajib menggunakan PIN Supervisor.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="min-w-full bg-white text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-600 font-semibold text-xs border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Nasabah (NIS)</th>
                        <th class="py-3 px-4">Jenis Transaksi</th>
                        <th class="py-3 px-4">Nominal</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($riwayat as $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 text-slate-600 tabular-nums">{{ \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-800">{{ $r->nama_siswa }} <br><span class="text-xs text-slate-400 font-normal tabular-nums">{{ $r->nis }}</span></td>
                        <td class="py-3 px-4 capitalize font-medium text-slate-700">{{ $r->jenis_transaksi }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900 tabular-nums">Rp {{ number_format($r->nominal, 0, ',', '.') }}</td>
                        <td class="py-3 px-4">
                            @if($r->status == 'dibatalkan')
                                <span class="bg-rose-100 text-rose-800 font-semibold px-2 py-0.5 rounded text-[10px]">Dibatalkan</span>
                                <div class="text-[11px] text-rose-600 mt-1">Alasan: {{ $r->alasan_batal }}</div>
                            @else
                                <span class="bg-emerald-100 text-emerald-800 font-semibold px-2 py-0.5 rounded text-[10px] capitalize">{{ $r->status }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap space-x-1.5">
                            @if($r->status != 'dibatalkan')
                                @if($r->jenis_transaksi === 'setor')
                                    <a href="{{ url('/teller/slip-setor/'.$r->id) }}" target="_blank" class="inline-block bg-white hover:bg-slate-50 text-slate-700 font-medium py-1.5 px-3 rounded-lg text-xs transition border border-slate-300 shadow-sm" title="Cetak Slip Setoran">
                                        Cetak Slip
                                    </a>
                                @else
                                    <a href="{{ url('/teller/slip-tarik/'.$r->id) }}" target="_blank" class="inline-block bg-white hover:bg-slate-50 text-slate-700 font-medium py-1.5 px-3 rounded-lg text-xs transition border border-slate-300 shadow-sm" title="Cetak Slip Penarikan">
                                        Cetak Slip
                                    </a>
                                @endif
                                <button onclick="bukaModalVoid({{ $r->id }}, '{{ $r->nama_siswa }}', '{{ $r->jenis_transaksi }}', 'Rp {{ number_format($r->nominal, 0, ',', '.') }}')" class="bg-white hover:bg-slate-50 text-rose-700 font-medium py-1.5 px-3 rounded-lg text-xs transition shadow-sm border border-slate-300">
                                    Void
                                </button>
                            @else
                                <span class="text-xs text-slate-400 italic">Telah Di-Void</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-slate-400">Belum ada riwayat transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>

    <!-- POP-UP MODAL VOID -->
    <div id="modal-void" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 border-b border-slate-800">
                <h3 class="text-base font-semibold text-white">
                    Koreksi / Void Transaksi
                </h3>
            </div>

            <form id="form-void" method="POST" class="p-6">
                @csrf
                <div class="mb-4 bg-rose-50 p-3 rounded-lg border border-rose-200 text-xs">
                    <p class="text-rose-800 font-semibold mb-1">Membatalkan Transaksi:</p>
                    <p id="void-info" class="text-rose-700 font-medium"></p>
                </div>

                <div class="mb-4">
                    <label class="block text-slate-700 text-xs font-semibold mb-1">Alasan Pembatalan</label>
                    <input type="text" name="alasan_batal" placeholder="Contoh: Salah input nominal uang..." class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-xs" required autocomplete="off">
                </div>

                <div class="mb-6">
                    <label class="block text-slate-700 text-xs font-semibold mb-1">PIN Otorisasi Supervisor <span class="text-xs text-rose-500 font-normal">(Wajib diisi oleh Atasan)</span></label>
                    <input type="password" name="pin_supervisor" placeholder="Masukkan 6 Digit PIN" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none text-center tracking-widest text-lg font-bold tabular-nums" required>
                    <p class="text-[11px] text-slate-400 mt-1">*Sebagai ujicoba, masukkan PIN: 123456</p>
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="tutupModalVoid()" class="w-1/3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2 rounded-lg transition text-xs">Batal</button>
                    <button type="submit" onclick="return confirm('Tindakan ini tidak bisa dikembalikan. Yakin Void?')" class="w-2/3 bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 rounded-lg shadow-sm transition text-xs">
                        Proses Void
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Script JavaScript untuk Dropdown -->
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
        }

        window.addEventListener('click', function(e) {
            const btn = document.getElementById('profile-btn');
            const dropdown = document.getElementById('profile-dropdown');
            if (btn && dropdown && !btn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        function bukaModalVoid(id, nama, jenis, nominal) {
            document.getElementById('form-void').action = "{{ url('/teller/transaksi/void') }}/" + id;
            document.getElementById('void-info').innerText = nama + " | " + jenis.toUpperCase() + " | " + nominal;
            document.getElementById('modal-void').classList.remove('hidden');
        }

        function tutupModalVoid() {
            document.getElementById('modal-void').classList.add('hidden');
        }
    </script>
</body>
</html>
