<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Daily Report - Supervisor & Accounting</title>
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

        <!-- HEADER & FILTER -->
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-6 mb-6 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Pemeriksaan & Pengesahan Daily Report (Tutup Kas)
                </h1>
                <p class="text-slate-500 text-sm mt-1">
                    Sesuai SOP Perbankan Mini, dokumen direview satu per satu untuk memastikan integritas saldo kas fisik dan sistem.
                </p>
            </div>
        </div>

        <!-- FORM FILTER PENCARIAN -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form action="{{ url('/supervisor/daily-report') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Filter Tanggal Laporan</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
                           class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Dokumen</label>
                    <select name="status" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 font-medium">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu Review)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                        <option value="hold" {{ request('status') === 'hold' ? 'selected' : '' }}>Hold (Ditahan / Pending)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                    </select>
                </div>

                <div class="flex space-x-2">
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-medium py-2 px-4 rounded-lg text-sm transition flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Terapkan Filter
                    </button>
                    @if(request()->hasAny(['tanggal', 'status']))
                    <a href="{{ url('/supervisor/daily-report') }}" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium py-2 px-3 rounded-lg text-sm transition">
                        Reset
                    </a>
                    @endif
                </div>

                <div class="text-right text-xs text-slate-500">
                    Total Laporan Ditemukan: <span class="font-bold text-slate-800 tabular-nums">{{ count($reports) }} dokumen</span>
                </div>
            </form>
        </div>

        <!-- TABEL LAPORAN HARIAN DARI TELLER -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 text-slate-600 font-semibold text-xs border-b border-slate-200">
                        <tr>
                            <th class="p-3">ID / Tanggal</th>
                            <th class="p-3">Petugas Teller</th>
                            <th class="p-3 text-right">Saldo Awal</th>
                            <th class="p-3 text-right">Total Setor</th>
                            <th class="p-3 text-right">Total Tarik</th>
                            <th class="p-3 text-right">Saldo Sistem</th>
                            <th class="p-3 text-right">Kas Fisik</th>
                            <th class="p-3 text-right">Selisih</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3">Pemeriksa (Approver)</th>
                            <th class="p-3 text-center">Tindakan Otorisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($reports as $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 tabular-nums">
                                <span class="font-bold text-slate-900 block">#REP-{{ str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-slate-400 text-[11px]">{{ date('d/m/Y', strtotime($r->report_date)) }}</span>
                            </td>
                            <td class="p-3 font-semibold text-slate-800">
                                <span class="block">{{ $r->nama_teller }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">ID User: #{{ $r->user_id }}</span>
                            </td>
                            <td class="p-3 text-right tabular-nums">Rp {{ number_format($r->opening_balance, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-emerald-600 font-semibold tabular-nums">Rp {{ number_format($r->total_deposit, 0, ',', '.') }}</td>
                            <td class="p-3 text-right text-rose-600 font-semibold tabular-nums">Rp {{ number_format($r->total_withdrawal, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-semibold text-slate-900 tabular-nums">Rp {{ number_format($r->system_balance, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-semibold text-slate-900 tabular-nums">Rp {{ number_format($r->physical_balance, 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-semibold tabular-nums {{ $r->difference == 0 ? 'text-emerald-600' : ($r->difference > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                                <span>{{ $r->difference >= 0 ? '+' : '' }}Rp {{ number_format($r->difference, 0, ',', '.') }}</span>
                                <span class="block text-[10px] font-normal uppercase">
                                    {{ $r->difference == 0 ? 'Seimbang' : ($r->difference > 0 ? 'Surplus' : 'Defisit') }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @if($r->status === 'approved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        Approved
                                    </span>
                                @elseif($r->status === 'hold')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800">
                                        Hold (Pending)
                                    </span>
                                @elseif($r->status === 'rejected')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800">
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800">
                                        Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="p-3 text-slate-600">
                                @if($r->nama_approver)
                                    <span class="font-semibold text-slate-800 block">{{ $r->nama_approver }}</span>
                                    <span class="text-[10px] text-slate-400 tabular-nums">{{ date('d/m/Y H:i', strtotime($r->updated_at)) }}</span>
                                @else
                                    <span class="text-slate-400 italic">Belum diperiksa</span>
                                @endif
                                @if($r->notes)
                                    <p class="text-[10px] text-slate-500 mt-0.5 italic max-w-xs truncate" title="{{ $r->notes }}">"{{ $r->notes }}"</p>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Tombol Review Modal -->
                                    <button type="button" onclick="openApprovalModal({{ json_encode($r) }})"
                                            class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-2.5 py-1.5 rounded-lg transition text-xs shadow-sm flex items-center">
                                        Review
                                    </button>

                                    <!-- Tombol Cetak -->
                                    <a href="{{ url('/daily-report/cetak/'.$r->id) }}" target="_blank"
                                       class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium px-2 py-1.5 rounded-lg transition text-xs shadow-sm flex items-center" title="Cetak Dokumen Resmi">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="p-8 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Tidak ada laporan daily report yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL REVIEW & PENGESAHAN DOKUMEN DAILY REPORT -->
    <div id="modal-review" class="hidden fixed inset-0 bg-slate-900 bg-opacity-75 z-50 p-4 backdrop-blur-sm overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="bg-slate-800 text-white px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold flex items-center" id="m-title">Otorisasi Dokumen Daily Report</h3>
                    <p class="text-xs text-slate-300 mt-0.5" id="m-subtitle">Petugas Teller: -</p>
                </div>
                <button type="button" onclick="closeApprovalModal()" class="text-slate-400 hover:text-white text-xl font-bold">&times;</button>
            </div>

            <form id="form-review-status" method="POST" action="">
                @csrf
                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <span class="text-slate-500 block">Saldo Sistem (Komputer):</span>
                            <span class="font-bold text-sm text-blue-900" id="m-system-balance">Rp 0</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Kas Fisik Laci:</span>
                            <span class="font-bold text-sm text-slate-900" id="m-physical-balance">Rp 0</span>
                        </div>
                        <div class="col-span-2 pt-2 border-t border-slate-200 flex justify-between items-center">
                            <span class="text-slate-600 font-bold">Selisih Kas Fisik:</span>
                            <span class="font-black text-sm" id="m-difference">Rp 0</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keputusan Otorisasi *</label>
                        <select name="status" id="m-status-select" required
                                class="w-full text-sm font-medium border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700">
                            <option value="approved">Setujui (Approved - Tutup Kas Sah & Selesai)</option>
                            <option value="hold">Tahan / Pending (Hold - Investigasi Selisih tanpa memblokir login)</option>
                            <option value="rejected">Tolak (Rejected - Perhitungan Teller Keliru)</option>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Status <strong>Hold</strong> menangguhkan pengesahan kas tanpa mengunci akses login teller untuk bertransaksi di hari berikutnya.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Supervisor / Accounting</label>
                        <textarea name="notes" id="m-notes" rows="3" placeholder="Masukkan arahan atau catatan verifikasi untuk teller..."
                                  class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700"></textarea>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-3">
                    <button type="button" onclick="closeApprovalModal()" class="text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 px-4 py-2 rounded-lg border border-slate-300 transition">
                        Batal
                    </button>
                    <button type="submit" class="text-xs font-medium text-white bg-slate-900 hover:bg-slate-800 px-5 py-2 rounded-lg shadow-sm transition">
                        Simpan Keputusan Otorisasi
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <script>
        function openApprovalModal(report) {
            document.getElementById('m-title').innerText = 'Otorisasi Daily Report #REP-' + String(report.id).padStart(4, '0');
            document.getElementById('m-subtitle').innerText = 'Petugas Teller: ' + report.nama_teller + ' | Tanggal: ' + report.report_date;

            document.getElementById('m-system-balance').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(report.system_balance);
            document.getElementById('m-physical-balance').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(report.physical_balance);

            const diff = report.difference;
            const diffEl = document.getElementById('m-difference');
            diffEl.innerText = (diff >= 0 ? '+ ' : '- ') + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(diff)) +
                               ' (' + (diff === 0 ? 'Seimbang' : (diff > 0 ? 'Surplus' : 'Defisit')) + ')';
            diffEl.className = 'font-black text-sm ' + (diff === 0 ? 'text-emerald-600' : (diff > 0 ? 'text-blue-600' : 'text-rose-600'));

            document.getElementById('m-status-select').value = report.status || 'approved';
            document.getElementById('m-notes').value = report.notes || '';

            document.getElementById('form-review-status').action = "{{ url('/supervisor/daily-report/status') }}/" + report.id;

            document.getElementById('modal-review').classList.remove('hidden');
        }

        function closeApprovalModal() {
            document.getElementById('modal-review').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeApprovalModal();
            }
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
