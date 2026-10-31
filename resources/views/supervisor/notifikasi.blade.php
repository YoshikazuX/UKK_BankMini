<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Notifikasi Pekerjaan - Supervisor Bank Mini</title>
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

    <main class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- NOTIFIKASI FLASH -->
        @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-r shadow-sm flex items-start justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="font-medium text-sm">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
        @endif

        <!-- HEADER PUSAT NOTIFIKASI -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-200 gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pusat Notifikasi Pekerjaan</h1>
                    @if($unreadCount > 0)
                        <span class="text-xs font-bold text-amber-700">
                            ({{ $unreadCount }} Belum Dibaca)
                        </span>
                    @endif
                </div>
                <p class="text-slate-500 text-sm mt-1">
                    Daftar pemberitahuan otomatis saat Teller mengirim pengajuan kas baru atau mengirim Daily Report tutup kas.
                </p>
            </div>

            @if($unreadCount > 0)
            <form action="{{ route('supervisor.notifikasi.baca-semua') }}" method="POST">
                @csrf
                <button type="submit" class="bg-white hover:bg-slate-50 border border-slate-300 text-slate-700 font-semibold px-4 py-2 rounded-lg text-xs transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Tandai Semua Sudah Dibaca
                </button>
            </form>
            @endif
        </div>

        <!-- TABS FILTER STATUS NOTIFIKASI -->
        <div class="flex items-center space-x-2 border-b border-slate-200 pb-px mb-6 text-xs">
            <a href="{{ route('supervisor.notifikasi', ['status' => 'semua']) }}" class="px-4 py-2 font-bold border-b-2 transition {{ $filterStatus === 'semua' ? 'border-indigo-600 text-indigo-700 bg-indigo-50/40' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Semua ({{ $totalCount }})
            </a>
            <a href="{{ route('supervisor.notifikasi', ['status' => 'belum_dibaca']) }}" class="px-4 py-2 font-bold border-b-2 transition {{ $filterStatus === 'belum_dibaca' ? 'border-indigo-600 text-indigo-700 bg-indigo-50/40' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Belum Dibaca ({{ $unreadCount }})
            </a>
            <a href="{{ route('supervisor.notifikasi', ['status' => 'sudah_dibaca']) }}" class="px-4 py-2 font-bold border-b-2 transition {{ $filterStatus === 'sudah_dibaca' ? 'border-indigo-600 text-indigo-700 bg-indigo-50/40' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Sudah Dibaca
            </a>
        </div>

        <!-- LIST NOTIFIKASI -->
        <div class="space-y-3 mb-6">
            @forelse($notifications as $notif)
            @php
                $isUnread = is_null($notif->read_at);
                $taskType = $notif->data['task_type'] ?? 'umum';
                $actionUrl = route('supervisor.notifikasi.baca', $notif->id);
            @endphp
            <div class="p-4 rounded-xl border transition {{ $isUnread ? 'bg-amber-50/50 border-amber-200 shadow-sm' : 'bg-white border-slate-200' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start space-x-3.5">
                        <div class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center font-bold {{ $taskType === 'pengajuan_kas' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            @if($taskType === 'pengajuan_kas')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @endif
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900">{{ $notif->data['title'] ?? 'Pemberitahuan Tugas' }}</h3>
                                @if($isUnread)
                                    <span class="text-[11px] font-bold text-amber-700">Baru</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-700 mt-1 leading-relaxed">{{ $notif->data['message'] ?? '-' }}</p>
                            <div class="flex flex-wrap items-center gap-3 mt-2 text-[11px] text-slate-500">
                                @if(isset($notif->data['nomor_referensi']))
                                    <span class="font-bold text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">
                                        {{ $notif->data['nomor_referensi'] }}
                                    </span>
                                @endif
                                <span>Petugas: <strong class="text-slate-800">{{ $notif->data['nama_teller'] ?? 'Teller' }}</strong></span>
                                <span>&bull;</span>
                                <span class="tabular-nums">{{ $notif->created_at->format('d/m/Y H:i') }} ({{ $notif->created_at->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <a href="{{ $actionUrl }}" class="inline-flex items-center gap-1 bg-slate-900 hover:bg-slate-800 text-white font-semibold py-1.5 px-3 rounded-lg text-xs transition shadow-sm">
                            <span>Buka Pekerjaan</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-400">
                <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <p class="font-medium text-slate-600">Tidak ada notifikasi dalam filter ini.</p>
                <p class="text-xs text-slate-400 mt-1">Setiap ada permohonan baru dari Teller akan otomatis tampil di sini.</p>
            </div>
            @endforelse
        </div>

        <!-- PAGINASI -->
        @if($notifications->hasPages())
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            {{ $notifications->links() }}
        </div>
        @endif
    </main>
</body>
</html>

