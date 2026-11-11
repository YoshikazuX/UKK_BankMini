@props([
    'type' => 'info',
    'title' => null,
])

@php
    $styles = match ($type) {
        'tip', 'success' => [
            'wrap' => 'bg-emerald-50/75 border-emerald-500 text-emerald-950',
            'iconWrap' => 'text-emerald-600',
            'defaultTitle' => 'Praktik Baik & Catatan Sistem',
            'iconPath' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'warning' => [
            'wrap' => 'bg-amber-50/80 border-amber-500 text-amber-950',
            'iconWrap' => 'text-amber-600',
            'defaultTitle' => 'Perhatian & Catatan Audit',
            'iconPath' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        ],
        'danger', 'security' => [
            'wrap' => 'bg-rose-50/80 border-rose-500 text-rose-950',
            'iconWrap' => 'text-rose-600',
            'defaultTitle' => 'Peringatan Keamanan / Pembatasan Kritis',
            'iconPath' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        default => [
            'wrap' => 'bg-sky-50/75 border-sky-500 text-slate-900',
            'iconWrap' => 'text-sky-600',
            'defaultTitle' => 'Informasi Teknis',
            'iconPath' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'my-6 rounded-r-xl border-l-4 p-4 sm:p-5 shadow-sm ' . $styles['wrap']]) }}>
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 mt-0.5 shrink-0 {{ $styles['iconWrap'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $styles['iconPath'] }}" />
        </svg>
        <div class="min-w-0 flex-1 text-sm leading-relaxed">
            <div class="font-bold tracking-tight mb-1">{{ $title ?? $styles['defaultTitle'] }}</div>
            <div class="text-slate-700 [&>p+p]:mt-2 [&_code]:bg-white/80 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-xs [&_code]:font-mono">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
