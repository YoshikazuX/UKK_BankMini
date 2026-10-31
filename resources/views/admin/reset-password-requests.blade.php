<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Permohonan Reset Password - Administrator</title>
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

    <!-- NAVBAR -->
    <x-navbar />

    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full flex-grow">

        @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 mb-6 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3 mb-6 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center text-rose-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span class="text-sm font-semibold">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <!-- HEADER HALAMAN -->
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Pengelolaan Permohonan Reset Password</h1>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">
                    Daftar permohonan pemulihan kata sandi pegawai melalui metode OTP Email, WhatsApp, dan Tiket Administrator.
                </p>
            </div>
        </div>

        <!-- STATS BADGES STATUS PERMOHONAN -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6 text-xs">
            <a href="{{ url('/admin/reset-password-requests?status=all') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-slate-400 transition flex items-center justify-between {{ (!request('status') || request('status') === 'all') ? 'ring-2 ring-slate-900/10' : '' }}">
                <div>
                    <span class="text-[11px] text-slate-500 font-medium block">Total Permohonan</span>
                    <span class="text-lg font-bold text-slate-900">{{ number_format($totalSemua, 0, ',', '.') }}</span>
                </div>
                <span class="text-xs font-bold text-slate-400">ALL</span>
            </a>

            <a href="{{ url('/admin/reset-password-requests?status=pending') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-amber-300 transition flex items-center justify-between {{ request('status') === 'pending' ? 'ring-2 ring-amber-500/20 border-amber-300' : '' }}">
                <div>
                    <span class="text-[11px] text-amber-600 font-medium block">Menunggu (Pending)</span>
                    <span class="text-lg font-bold text-amber-700">{{ number_format($totalPending, 0, ',', '.') }}</span>
                </div>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    !
                </div>
            </a>

            <a href="{{ url('/admin/reset-password-requests?status=verified') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-sky-300 transition flex items-center justify-between {{ request('status') === 'verified' ? 'ring-2 ring-sky-500/20 border-sky-300' : '' }}">
                <div>
                    <span class="text-[11px] text-sky-600 font-medium block">Terverifikasi OTP</span>
                    <span class="text-lg font-bold text-sky-700">{{ number_format($totalVerified, 0, ',', '.') }}</span>
                </div>
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    &check;
                </div>
            </a>

            <a href="{{ url('/admin/reset-password-requests?status=completed') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-emerald-300 transition flex items-center justify-between {{ request('status') === 'completed' ? 'ring-2 ring-emerald-500/20 border-emerald-300' : '' }}">
                <div>
                    <span class="text-[11px] text-emerald-600 font-medium block">Selesai (Completed)</span>
                    <span class="text-lg font-bold text-emerald-700">{{ number_format($totalCompleted, 0, ',', '.') }}</span>
                </div>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    &check;&check;
                </div>
            </a>

            <a href="{{ url('/admin/reset-password-requests?status=rejected') }}" class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-rose-300 transition flex items-center justify-between {{ request('status') === 'rejected' ? 'ring-2 ring-rose-500/20 border-rose-300' : '' }}">
                <div>
                    <span class="text-[11px] text-rose-600 font-medium block">Ditolak (Rejected)</span>
                    <span class="text-lg font-bold text-rose-700">{{ number_format($totalRejected, 0, ',', '.') }}</span>
                </div>
                <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                    &times;
                </div>
            </a>
        </div>

        <!-- ========================================================================= -->
        <!-- FORM FILTER & PENCARIAN TIKET -->
        <!-- ========================================================================= -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form action="{{ url('/admin/reset-password-requests') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                    <!-- Status -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Status Permohonan</label>
                        <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all" {{ (!request('status') || request('status') === 'all') ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu Persetujuan)</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified (Terverifikasi)</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed (Selesai Direset)</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                        </select>
                    </div>

                    <!-- Metode -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Saluran / Metode</label>
                        <select name="metode" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all">Semua Saluran</option>
                            <option value="ticket" {{ request('metode') === 'ticket' ? 'selected' : '' }}>Tiket Administrator</option>
                            <option value="whatsapp" {{ request('metode') === 'whatsapp' ? 'selected' : '' }}>WhatsApp OTP</option>
                            <option value="email" {{ request('metode') === 'email' ? 'selected' : '' }}>Email OTP</option>
                        </select>
                    </div>

                    <!-- Dari Tanggal -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs">
                    </div>

                    <!-- Sampai Tanggal -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" value="{{ request('tgl_akhir') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                    <div class="flex items-center gap-2 flex-grow max-w-sm">
                        <input
                            type="text"
                            name="cari"
                            value="{{ request('cari') }}"
                            placeholder="Cari username, nama pemohon, atau kontak..."
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs">
                    </div>

                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['status', 'metode', 'tgl_mulai', 'tgl_akhir', 'cari']))
                            <a href="{{ url('/admin/reset-password-requests') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Reset Filter
                            </a>
                        @endif
                        <button type="submit" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg transition shadow-sm inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- TABEL PERMOHONAN RESET PASSWORD -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left text-xs whitespace-nowrap">
                    <thead class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4">No. Tiket</th>
                            <th class="py-3.5 px-4">Nama & Username</th>
                            <th class="py-3.5 px-4">Role Akun</th>
                            <th class="py-3.5 px-4">Metode Permohonan</th>
                            <th class="py-3.5 px-4">Waktu Permohonan</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi / Penanganan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($requests as $req)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- ID Tiket -->
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                #RST-{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Nama & Username -->
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 block text-sm">{{ $req->nama_pemohon ?? ($req->user->nama_petugas ?? $req->username) }}</span>
                                <span class="text-slate-500 font-mono text-[11px]">Username: {{ $req->username }}</span>
                            </td>

                            <!-- Role -->
                            <td class="py-3.5 px-4 text-xs font-medium text-slate-700 uppercase">
                                {{ $req->user->role ?? 'Pegawai' }}
                            </td>

                            <!-- Metode -->
                            <td class="py-3.5 px-4">
                                @if($req->metode === 'whatsapp')
                                    <span class="font-semibold text-xs text-emerald-700">
                                        WhatsApp OTP
                                    </span>
                                @elseif($req->metode === 'email')
                                    <span class="font-semibold text-xs text-indigo-700">
                                        Email OTP
                                    </span>
                                @else
                                    <span class="font-semibold text-xs text-amber-700">
                                        Tiket Administrator
                                    </span>
                                @endif
                                <span class="text-slate-400 block text-[10px]">{{ $req->kontak ?: '-' }}</span>
                            </td>

                            <!-- Waktu -->
                            <td class="py-3.5 px-4 font-mono text-slate-500 text-[11px]">
                                {{ $req->created_at ? $req->created_at->format('d/m/Y H:i') : '-' }}
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                @if($req->status === 'pending')
                                    <span class="text-xs font-semibold text-amber-700">Pending</span>
                                @elseif($req->status === 'verified')
                                    <span class="text-xs font-semibold text-sky-700">Verified</span>
                                @elseif($req->status === 'completed')
                                    <span class="text-xs font-semibold text-emerald-700">Completed</span>
                                @else
                                    <span class="text-xs font-semibold text-rose-700">Rejected</span>
                                @endif
                            </td>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap space-x-1">
                                <!-- Tombol Detail -->
                                <button
                                    type="button"
                                    onclick='lihatDetailTiket(@json($req))'
                                    class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold py-1 px-2.5 rounded text-[11px] transition shadow-sm inline-flex items-center gap-1">
                                    Detail
                                </button>

                                @if($req->status === 'pending')
                                    <!-- Tombol Setujui (Modal Konfirmasi Feature 7) -->
                                    <button
                                        type="button"
                                        onclick="konfirmasiApprove({{ $req->id }}, '{{ addslashes($req->username) }}', '{{ addslashes($req->nama_pemohon ?? '') }}')"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-1 px-2.5 rounded text-[11px] transition shadow-sm">
                                        Setujui
                                    </button>

                                    <!-- Tombol Tolak (Modal Konfirmasi Feature 7) -->
                                    <button
                                        type="button"
                                        onclick="konfirmasiReject({{ $req->id }}, '{{ addslashes($req->username) }}')"
                                        class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-semibold py-1 px-2.5 rounded text-[11px] transition shadow-sm">
                                        Tolak
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400 italic">
                                Belum ada permohonan reset password yang cocok dengan kriteria filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($requests->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <span class="text-slate-500">
                    Menampilkan <strong>{{ $requests->firstItem() ?? 0 }}</strong> - <strong>{{ $requests->lastItem() ?? 0 }}</strong> dari <strong>{{ $requests->total() }}</strong> tiket permohonan
                </span>
                <div>
                    {{ $requests->links() }}
                </div>
            </div>
            @endif
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL DETAIL TIKET LENGKAP (FEATURE 5) -->
    <!-- ========================================================================= -->
    <div id="modal-detail-tiket" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
                <div class="bg-slate-900 px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                            Rincian Permohonan Reset Password
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5" id="tiket-detail-id">Tiket #</p>
                    </div>
                    <button type="button" onclick="tutupDetailTiket()" class="text-slate-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div>
                            <span class="text-slate-500 block mb-0.5">Nama Pemohon</span>
                            <strong class="text-slate-900 text-sm" id="tiket-detail-nama">-</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-0.5">Username Akun</span>
                            <strong class="text-slate-900 font-mono text-sm" id="tiket-detail-user">-</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-0.5">Metode Saluran</span>
                            <strong class="text-slate-800 uppercase" id="tiket-detail-metode">-</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-0.5">Kontak Terdaftar</span>
                            <strong class="text-slate-800 font-mono" id="tiket-detail-kontak">-</strong>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <span class="text-slate-500 font-semibold block">Catatan / Keterangan Pemohon:</span>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-slate-800 leading-relaxed font-sans" id="tiket-detail-keterangan">
                            -
                        </div>
                    </div>

                    <!-- Info Penanganan Admin -->
                    <div class="p-3 bg-slate-100/70 rounded-xl border border-slate-200 space-y-1 text-[11px] text-slate-600">
                        <div>Waktu Pengajuan: <strong class="text-slate-800" id="tiket-detail-created">-</strong></div>
                        <div>Alamat IP Pemohon: <strong class="text-slate-800 font-mono" id="tiket-detail-ip">-</strong></div>
                        <div id="tiket-detail-handled-container">
                            Penanganan: <strong class="text-slate-800" id="tiket-detail-handled">-</strong>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end">
                    <button type="button" onclick="tutupDetailTiket()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI APPROVE (FEATURE 7) -->
    <!-- ========================================================================= -->
    <div id="modal-confirm-approve" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
                <div class="p-6">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Setujui Permohonan Reset Password?</h3>
                    <p class="text-xs text-slate-600 mt-2">
                        Sistem akan membuat kata sandi sementara yang aman secara acak untuk akun ini. Kata sandi lama akan langsung diganti dan status tiket menjadi <strong>Completed</strong>.
                    </p>

                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 text-xs font-mono text-slate-700 mt-4 space-y-1">
                        <div>Username: <strong id="approve-modal-user">-</strong></div>
                        <div>Nama: <strong id="approve-modal-nama">-</strong></div>
                    </div>
                </div>

                <form id="form-approve" method="POST" action="" class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end space-x-2">
                    @csrf
                    <button type="button" onclick="tutupApproveModal()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold rounded-lg text-xs transition shadow-sm">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-approve" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2">
                        <span>Ya, Setujui & Reset Sandi</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL KONFIRMASI REJECT DENGAN ALASAN WAJIB (FEATURE 5 & 7) -->
    <!-- ========================================================================= -->
    <div id="modal-confirm-reject" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-200">
                <div class="p-6">
                    <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Tolak Permohonan Reset Password</h3>
                    <p class="text-xs text-slate-600 mt-2">
                        Sesuai SOP keamanan perbankan, penolakan permohonan pemulihan akun wajib disertai alasan yang jelas agar tercatat di jejak audit.
                    </p>

                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs font-mono text-slate-700 mt-3 mb-4">
                        Username: <strong id="reject-modal-user">-</strong>
                    </div>

                    <form id="form-reject" method="POST" action="">
                        @csrf
                        <label class="block text-slate-700 text-xs font-semibold mb-1.5" for="input-alasan-reject">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="alasan"
                            id="input-alasan-reject"
                            rows="3"
                            required
                            placeholder="Contoh: Identitas kontak tidak cocok dengan data kepegawaian..."
                            class="w-full border border-slate-300 rounded-lg p-2.5 text-xs focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 focus:outline-none"></textarea>

                        <div class="flex justify-end space-x-2 mt-4 pt-3 border-t border-slate-100">
                            <button type="button" onclick="tutupRejectModal()" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold rounded-lg text-xs transition shadow-sm">
                                Batal
                            </button>
                            <button type="submit" id="btn-submit-reject" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-lg text-xs transition shadow-sm flex items-center gap-2">
                                <span>Tolak Permohonan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function lihatDetailTiket(req) {
            document.getElementById('tiket-detail-id').textContent = 'Tiket #RST-' + String(req.id).padStart(4, '0') + ' (Status: ' + req.status.toUpperCase() + ')';
            document.getElementById('tiket-detail-nama').textContent = req.nama_pemohon || (req.user ? req.user.nama_petugas : req.username);
            document.getElementById('tiket-detail-user').textContent = req.username;
            document.getElementById('tiket-detail-metode').textContent = req.metode;
            document.getElementById('tiket-detail-kontak').textContent = req.kontak || '-';
            document.getElementById('tiket-detail-keterangan').textContent = req.keterangan || '-';
            document.getElementById('tiket-detail-created').textContent = req.created_at || '-';
            document.getElementById('tiket-detail-ip').textContent = req.ip_address || '-';
            document.getElementById('tiket-detail-handled').textContent = req.keterangan || '-';

            document.getElementById('modal-detail-tiket').classList.remove('hidden');
        }

        function tutupDetailTiket() {
            document.getElementById('modal-detail-tiket').classList.add('hidden');
        }

        // =====================================================================
        // KONFIRMASI APPROVE (FEATURE 7)
        // =====================================================================
        function konfirmasiApprove(id, username, nama) {
            const form = document.getElementById('form-approve');
            form.action = '{{ url("/admin/reset-password-request") }}/' + id + '/approve';
            document.getElementById('approve-modal-user').textContent = username;
            document.getElementById('approve-modal-nama').textContent = nama || '-';
            document.getElementById('modal-confirm-approve').classList.remove('hidden');
        }

        function tutupApproveModal() {
            document.getElementById('modal-confirm-approve').classList.add('hidden');
        }

        // =====================================================================
        // KONFIRMASI REJECT (FEATURE 7)
        // =====================================================================
        function konfirmasiReject(id, username) {
            const form = document.getElementById('form-reject');
            form.action = '{{ url("/admin/reset-password-request") }}/' + id + '/reject';
            document.getElementById('reject-modal-user').textContent = username;
            document.getElementById('input-alasan-reject').value = '';
            document.getElementById('modal-confirm-reject').classList.remove('hidden');
        }

        function tutupRejectModal() {
            document.getElementById('modal-confirm-reject').classList.add('hidden');
        }

        // Cegah Double Submit
        document.getElementById('form-approve').addEventListener('submit', function () {
            const btn = document.getElementById('btn-submit-approve');
            btn.disabled = true;
            btn.innerHTML = '<span>Memproses...</span>';
        });

        document.getElementById('form-reject').addEventListener('submit', function () {
            const btn = document.getElementById('btn-submit-reject');
            btn.disabled = true;
            btn.innerHTML = '<span>Menolak...</span>';
        });
    </script>
</body>
</html>

