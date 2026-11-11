@props([
    'lang' => 'text',
    'title' => null,
    'code' => null,
])

@php
    $langLabel = strtoupper($lang);
    $rawCode = $code !== null ? (string) $code : trim((string) $slot);
@endphp

<div x-data="{
        copied: false,
        copyCode() {
            const target = this.$refs.codeTarget;
            if (!target) return;
            navigator.clipboard.writeText(target.innerText).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 1800);
            });
        }
     }"
     class="my-5 rounded-xl border border-slate-800 bg-[#0F172A] shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-4 py-2.5 bg-slate-900/90 border-b border-slate-800 text-xs">
        <div class="flex items-center gap-2 min-w-0">
            <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300 font-mono text-[11px] font-semibold border border-emerald-500/20">
                {{ $langLabel }}
            </span>
            @if($title)
                <span class="text-slate-300 font-mono truncate">{{ $title }}</span>
            @endif
        </div>
        <button type="button"
                @click="copyCode()"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold text-slate-300 hover:text-white hover:bg-slate-800 transition cursor-pointer shrink-0">
            <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <svg x-show="copied" x-cloak class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span x-text="copied ? 'Tersalin' : 'Salin'">Salin</span>
        </button>
    </div>
    <div class="overflow-x-auto p-4 text-[13px] leading-relaxed font-mono text-slate-100">
        <pre class="m-0"><code x-ref="codeTarget" data-lang="{{ strtolower($lang) }}" class="hanbank-code block whitespace-pre">{{ $rawCode }}</code></pre>
    </div>
</div>

