@props([
    'status' => 'Implemented',
])

@php
    $normalized = trim((string) $status);
    $badgeClass = match ($normalized) {
        'Tested and Verified' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Implemented' => 'bg-teal-50 text-teal-800 border-teal-200',
        'Partially Implemented' => 'bg-amber-50 text-amber-800 border-amber-200',
        'Not Yet Tested' => 'bg-slate-100 text-slate-700 border-slate-300',
        'Planned' => 'bg-sky-50 text-sky-700 border-sky-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border whitespace-nowrap ' . $badgeClass]) }}>
    {{ $normalized }}
</span>

