@props([
    'headers' => [],
])

<div {{ $attributes->merge(['class' => 'my-6 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            @if(!empty($headers))
                <thead>
                    <tr class="bg-slate-900 text-slate-100 border-b border-slate-800">
                        @foreach($headers as $header)
                            <th class="py-3 px-4 font-semibold tracking-wide whitespace-nowrap">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="divide-y divide-slate-200 text-slate-700 [&_tr:nth-child(even)]:bg-slate-50/50 [&_td]:py-3 [&_td]:px-4 [&_td]:align-top">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
