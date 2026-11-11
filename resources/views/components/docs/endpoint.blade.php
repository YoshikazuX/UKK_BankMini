@props([
    'method' => 'GET',
    'path' => '/',
    'auth' => 'Public',
    'controller' => null,
    'summary' => null,
])

@php
    $upperMethod = strtoupper($method);
    $methodClass = match ($upperMethod) {
        'GET' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'POST' => 'bg-sky-50 text-sky-700 border-sky-200',
        'PUT', 'PATCH' => 'bg-amber-50 text-amber-700 border-amber-200',
        'DELETE' => 'bg-rose-50 text-rose-700 border-rose-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };

    $isProtected = str_contains(strtolower($auth), 'sanctum') && ! str_contains(strtolower($auth), 'tanpa');
@endphp

<div class="my-5 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="px-4 py-3.5 bg-slate-50/80 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2.5">
        <div class="flex flex-wrap items-center gap-2.5 min-w-0">
            <span class="px-2.5 py-0.5 rounded-md border font-mono text-xs font-extrabold tracking-wide {{ $methodClass }}">
                {{ $upperMethod }}
            </span>
            <code class="text-xs sm:text-sm font-mono font-bold text-slate-900 break-all">{{ $path }}</code>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold border {{ $isProtected ? 'bg-slate-900 text-emerald-400 border-slate-900' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                {{ $auth }}
            </span>
            @if($controller)
                <span class="px-2 py-0.5 rounded bg-white text-slate-600 border border-slate-200 font-mono text-[11px]">
                    {{ $controller }}
                </span>
            @endif
        </div>
    </div>

    @if($summary || trim((string) $slot) !== '')
        <div class="p-4 text-sm text-slate-700 space-y-3">
            @if($summary)
                <p class="leading-relaxed">{{ $summary }}</p>
            @endif
            @if(trim((string) $slot) !== '')
                <div>{{ $slot }}</div>
            @endif
        </div>
    @endif
</div>
