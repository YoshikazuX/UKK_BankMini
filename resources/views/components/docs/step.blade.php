@props([
    'number' => '1',
    'title' => '',
])

<li class="relative pl-10">
    <span class="absolute left-0 top-0.5 w-7 h-7 rounded-full bg-slate-900 text-emerald-400 text-xs font-extrabold flex items-center justify-center ring-4 ring-[#F8FAFC] shadow-sm">
        {{ $number }}
    </span>
    @if($title)
        <div class="font-bold text-slate-900 text-sm sm:text-base mb-1">{{ $title }}</div>
    @endif
    <div class="text-sm text-slate-700 leading-relaxed space-y-2">
        {{ $slot }}
    </div>
</li>
