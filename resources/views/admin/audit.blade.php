<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jejak Audit & Riwayat Perubahan Data - Administrator</title>
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

        <!-- HEADER HALAMAN -->
        <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Jejak Audit (Audit Trail Log)</h1>
                <p class="text-slate-600 text-xs sm:text-sm mt-1">
                    Rekam jejak permanen seluruh aktivitas perubahan data penting, mutasi status, dan tindakan administratif.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ url('/admin/audit/export?'.http_build_query(request()->all())) }}" target="_blank" class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 py-2 px-4 rounded-lg text-xs font-semibold shadow-sm transition inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Ekspor CSV Resmi
                </a>
            </div>
        </div>

        <!-- STATISTIK AUDIT LOG -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] text-slate-500 font-medium block">Total Catatan Audit</span>
                <span class="text-xl font-bold text-slate-900">{{ number_format($totalLog, 0, ',', '.') }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] text-slate-500 font-medium block">Jenis Aktivitas Terpantau</span>
                <span class="text-xl font-bold text-slate-800">{{ $daftarAktivitas->count() }} Tipe</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[11px] text-slate-500 font-medium block">Petugas Terdaftar</span>
                <span class="text-xl font-bold text-slate-800">{{ $daftarUser->count() }} Akun</span>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- FORM FILTER TERPADU (FEATURE 3) -->
        <!-- ========================================================================= -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <form action="{{ url('/admin/audit') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                    <!-- 1. Rentang Tanggal Mulai -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" value="{{ request('tgl_mulai') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs">
                    </div>

                    <!-- 2. Rentang Tanggal Akhir -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" value="{{ request('tgl_akhir') }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs">
                    </div>

                    <!-- 3. Filter Pengguna -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Petugas / Pelaksana</label>
                        <select name="user_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all">Semua Petugas</option>
                            @foreach($daftarUser as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_petugas }} ({{ strtoupper($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Filter Role -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Role Pengguna</label>
                        <select name="role" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all">Semua Role</option>
                            @foreach($daftarRole as $r)
                                <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>
                                    {{ strtoupper($r) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 5. Filter Jenis Aktivitas -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Jenis Aktivitas</label>
                        <select name="jenis_aktivitas" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs bg-white">
                            <option value="all">Semua Jenis</option>
                            @foreach($daftarAktivitas as $akt)
                                <option value="{{ $akt }}" {{ request('jenis_aktivitas') === $akt ? 'selected' : '' }}>
                                    {{ $akt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                    <div class="flex items-center gap-2 flex-grow max-w-sm">
                        <input
                            type="text"
                            name="cari"
                            value="{{ request('cari') }}"
                            placeholder="Cari objek (No Rek, NIS, Username)..."
                            class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-700 text-xs">
                    </div>

                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['tgl_mulai', 'tgl_akhir', 'user_id', 'role', 'jenis_aktivitas', 'cari']))
                            <a href="{{ url('/admin/audit') }}" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition inline-flex items-center gap-1">
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
        <!-- TABEL JEJAK AUDIT -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white text-left text-xs whitespace-nowrap">
                    <thead class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Waktu Sistem</th>
                            <th class="py-3 px-4">Pengguna (Pelaksana)</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Jenis Aktivitas</th>
                            <th class="py-3 px-4">Modul</th>
                            <th class="py-3 px-4">Objek Terkait</th>
                            <th class="py-3 px-4 text-center">Rincian Perubahan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Waktu -->
                            <td class="py-3 px-4 font-mono text-slate-500 text-[11px]">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                            </td>

                            <!-- Pelaksana -->
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900 block">{{ $log->nama_user }}</span>
                                <span class="text-slate-400 font-mono text-[10px]">ID: #{{ $log->user_id ?? '-' }} &bull; IP: {{ $log->ip_address ?? '-' }}</span>
                            </td>

                            <!-- Role -->
                            <td class="py-3 px-4">
                                <span class="bg-slate-100 text-slate-700 border border-slate-200 font-semibold px-2 py-0.5 rounded text-[10px] uppercase">
                                    {{ $log->role_user ?? '-' }}
                                </span>
                            </td>

                            <!-- Aktivitas -->
                            <td class="py-3 px-4">
                                @if(str_contains($log->jenis_aktivitas, 'TAMBAH'))
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold px-2 py-0.5 rounded text-[10px]">
                                        {{ $log->jenis_aktivitas }}
                                    </span>
                                @elseif(str_contains($log->jenis_aktivitas, 'HAPUS') || str_contains($log->jenis_aktivitas, 'REJECT') || str_contains($log->jenis_aktivitas, 'NONAKTIF'))
                                    <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 border border-rose-200 font-semibold px-2 py-0.5 rounded text-[10px]">
                                        {{ $log->jenis_aktivitas }}
                                    </span>
                                @elseif(str_contains($log->jenis_aktivitas, 'APPROVE') || str_contains($log->jenis_aktivitas, 'AKTIF'))
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold px-2 py-0.5 rounded text-[10px]">
                                        {{ $log->jenis_aktivitas }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-200 font-semibold px-2 py-0.5 rounded text-[10px]">
                                        {{ $log->jenis_aktivitas }}
                                    </span>
                                @endif
                            </td>

                            <!-- Modul -->
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $log->modul }}
                            </td>

                            <!-- Objek ID -->
                            <td class="py-3 px-4 font-mono font-medium text-slate-700">
                                <span class="bg-slate-100 px-2 py-0.5 rounded border border-slate-200">
                                    {{ $log->objek_id ?? '-' }}
                                </span>
                            </td>

                            <!-- Rincian Nilai -->
                            <td class="py-3 px-4 text-center">
                                <button
                                    type="button"
                                    onclick='lihatDetailAudit(@json($log))'
                                    class="bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold py-1 px-3 rounded-lg text-[11px] transition shadow-sm inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Detail Perubahan
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400 italic">
                                Belum ada rekaman audit log yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($logs->hasPages())
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <span class="text-slate-500">
                    Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}</strong> - <strong>{{ $logs->lastItem() ?? 0 }}</strong> dari <strong>{{ $logs->total() }}</strong> rekaman audit
                </span>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
            @endif
        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- MODAL DETAIL AKTIVITAS PERUBAHAN (FEATURE 3) -->
    <!-- ========================================================================= -->
    <div id="modal-detail-audit" class="hidden fixed inset-0 bg-slate-900/60 z-50 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="min-h-full flex items-center justify-center">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-200 my-8">
            <div class="bg-slate-900 px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Detail Rekaman Audit Trail
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5" id="detail-log-id">Log #</p>
                </div>
                <button type="button" onclick="tutupDetailAudit()" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs">
                <!-- Meta Info Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div>
                        <span class="text-slate-500 block mb-0.5">Pelaksana</span>
                        <strong class="text-slate-900 text-sm" id="detail-user">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Role</span>
                        <strong class="text-slate-900 uppercase" id="detail-role">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Modul</span>
                        <strong class="text-slate-900" id="detail-modul">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block mb-0.5">Objek ID</span>
                        <strong class="text-slate-900 font-mono" id="detail-objek">-</strong>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-500 text-[11px]">
                    <div>Waktu: <strong class="text-slate-700" id="detail-waktu">-</strong></div>
                    <div>Alamat IP: <strong class="text-slate-700 font-mono" id="detail-ip">-</strong></div>
                </div>

                <!-- Perbandingan Before vs After -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <!-- Nilai Sebelum -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-rose-700">
                                Nilai Sebelum Perubahan
                            </span>
                        </div>
                        <div class="bg-rose-50/50 border border-rose-200 rounded-xl p-3 font-mono text-[11px] text-slate-800 overflow-x-auto min-h-[140px]" id="detail-before">
                            <span class="text-slate-400 italic">(Tidak ada data lama / Tambah baru)</span>
                        </div>
                    </div>

                    <!-- Nilai Sesudah -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-emerald-700">
                                Nilai Sesudah Perubahan
                            </span>
                        </div>
                        <div class="bg-emerald-50/50 border border-emerald-200 rounded-xl p-3 font-mono text-[11px] text-slate-800 overflow-x-auto min-h-[140px]" id="detail-after">
                            <span class="text-slate-400 italic">(Tidak ada data baru / Hapus)</span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-[11px] text-amber-900 flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>
                        <strong>Catatan Keamanan Perbankan:</strong> Seluruh kredensial rahasia seperti Password, PIN keamanan, dan token autentikasi secara otomatis disensor dan tidak disimpan dalam jejak audit.
                    </span>
                </div>
            </div>

            <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex justify-end">
                <button type="button" onclick="tutupDetailAudit()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg text-xs transition">
                    Tutup
                </button>
            </div>
        </div>
        </div>
    </div>

    <script>
        function lihatDetailAudit(log) {
            document.getElementById('detail-log-id').textContent = 'Log #' + log.id + ' (' + log.jenis_aktivitas + ')';
            document.getElementById('detail-user').textContent = log.nama_user;
            document.getElementById('detail-role').textContent = log.role_user || '-';
            document.getElementById('detail-modul').textContent = log.modul;
            document.getElementById('detail-objek').textContent = log.objek_id || '-';
            document.getElementById('detail-waktu').textContent = log.created_at;
            document.getElementById('detail-ip').textContent = log.ip_address || '-';

            const beforeEl = document.getElementById('detail-before');
            const afterEl = document.getElementById('detail-after');

            if (log.old_values && Object.keys(log.old_values).length > 0) {
                beforeEl.innerHTML = '<pre class="whitespace-pre-wrap leading-relaxed">' + JSON.stringify(log.old_values, null, 2) + '</pre>';
            } else {
                beforeEl.innerHTML = '<span class="text-slate-400 italic">(Tidak ada data sebelum perubahan)</span>';
            }

            if (log.new_values && Object.keys(log.new_values).length > 0) {
                afterEl.innerHTML = '<pre class="whitespace-pre-wrap leading-relaxed">' + JSON.stringify(log.new_values, null, 2) + '</pre>';
            } else {
                afterEl.innerHTML = '<span class="text-slate-400 italic">(Tidak ada data sesudah perubahan)</span>';
            }

            document.getElementById('modal-detail-audit').classList.remove('hidden');
        }

        function tutupDetailAudit() {
            document.getElementById('modal-detail-audit').classList.add('hidden');
        }
    </script>
</body>
</html>
