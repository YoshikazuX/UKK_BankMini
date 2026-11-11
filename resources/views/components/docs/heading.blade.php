@props([
    'id',
    'level' => 2,
])

@php
    $tag = (int) $level === 3 ? 'h3' : 'h2';
    $classes = (int) $level === 3
        ? 'group flex items-center gap-2 text-lg sm:text-xl font-bold text-slate-900 tracking-tight mt-8 mb-3 scroll-mt-24'
        : 'group flex items-center gap-2 text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mt-10 mb-4 pt-6 border-t border-slate-200/80 first:mt-0 first:pt-0 first:border-t-0 scroll-mt-24';
@endphp

<{{ $tag }} id="{{ $id }}" {{ $attributes->merge(['class' => $classes]) }} data-toc-heading data-toc-level="{{ (int) $level }}">
    <a href="#{{ $id }}" class="inline-flex items-center gap-2 text-inherit no-underline hover:text-emerald-700 transition-colors">
        <span class="text-emerald-600/70 opacity-70 sm:opacity-0 group-hover:opacity-100 transition-opacity font-mono text-base select-none" aria-hidden="true">#</span>
        <span>{{ $slot }}</span>
    </a>
</{{ $tag }}>

