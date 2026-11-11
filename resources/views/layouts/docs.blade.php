<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $currentPage['title'] ?? 'Documentation Center' }} — HANBANK Documentation Center</title>

    <link rel="icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        code, pre, kbd, .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        [x-cloak] {
            display: none !important;
        }
        .docs-prose p {
            margin-top: 0.9rem;
            margin-bottom: 0.9rem;
            line-height: 1.75;
            color: #334155;
            font-size: 0.965rem;
        }
        .docs-prose ul,
        .docs-prose ol {
            margin-top: 0.85rem;
            margin-bottom: 1.15rem;
            padding-left: 1.35rem;
            color: #334155;
            font-size: 0.95rem;
        }
        .docs-prose ul {
            list-style-type: disc;
        }
        .docs-prose ol {
            list-style-type: decimal;
        }
        .docs-prose li {
            margin-top: 0.35rem;
            margin-bottom: 0.35rem;
            line-height: 1.65;
        }
        .docs-prose li strong,
        .docs-prose p strong {
            color: #0F172A;
            font-weight: 700;
        }
        .docs-prose :not(pre) > code {
            background-color: #F1F5F9;
            color: #0F766E;
            padding: 0.14rem 0.42rem;
            border-radius: 0.375rem;
            font-size: 0.82em;
            font-weight: 600;
            border: 1px solid #E2E8F0;
            word-break: break-word;
        }
        .docs-prose a:not(.no-prose-link) {
            color: #0F766E;
            font-weight: 600;
            text-decoration: underline;
            text-decoration-color: rgba(15, 118, 110, 0.35);
            text-underline-offset: 3px;
        }
        .docs-prose a:not(.no-prose-link):hover {
            color: #059669;
            text-decoration-color: #059669;
        }
        /* Syntax highlighting tokens inside dark code blocks (#0F172A) */
        .tok-comment { color: #64748B; font-style: italic; }
        .tok-string { color: #6EE7B7; }
        .tok-keyword { color: #7DD3FC; font-weight: 600; }
        .tok-number { color: #FCD34D; }
        .tok-key { color: #5EEAD4; }
        .tok-method { color: #38BDF8; font-weight: 700; }
        .tok-operator { color: #94A3B8; }
        .tok-variable { color: #FDE68A; }

        /* Custom subtle scrollbar for sidebars */
        .docs-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .docs-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .docs-scroll::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 9999px;
        }
        .docs-scroll::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased selection:bg-slate-900 selection:text-emerald-300"
      x-data="hanbankDocsApp({
          currentSection: @js($currentSectionSlug),
          searchIndex: @js($searchIndex),
          tocIds: @js(collect($tableOfContents)->pluck('id')->values()->all())
      })"
      x-init="init()"
      @keydown.window="handleGlobalKeydown($event)">

    {{-- 1. MINIMALIST TOP DOCUMENTATION HEADER (Consistent with Public Navbar) --}}
    <header class="sticky top-0 z-40 h-20 bg-white/95 backdrop-blur-md border-b border-slate-200/80 transition-all duration-300">
        <div class="max-w-[90rem] mx-auto h-full px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-3 xl:gap-6">
            {{-- Left: Mobile drawer trigger + Institutional Brand --}}
            <div class="flex items-center gap-3 shrink-0 min-w-0">
                <button type="button"
                        @click="mobileDrawerOpen = !mobileDrawerOpen"
                        class="lg:hidden inline-flex items-center justify-center p-2.5 rounded-xl text-slate-600 hover:text-slate-950 hover:bg-slate-100 border border-slate-200/80 transition cursor-pointer shrink-0"
                        aria-label="Buka navigasi dokumentasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <a href="{{ route('docs.index') }}" class="flex items-center gap-3 group focus:outline-none shrink-0 whitespace-nowrap">
                    <img src="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}"
                         alt="Logo SMK Negeri 1 Bantul"
                         class="h-10 sm:h-11 w-auto object-contain shrink-0">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-base sm:text-lg tracking-tight leading-snug group-hover:text-emerald-700 transition">
                                LKM Mitra Siswa Abadi
                            </span>
                            <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[10px] font-bold uppercase tracking-wider shrink-0">
                                Docs
                            </span>
                        </div>
                        <span class="text-xs font-medium text-slate-500 tracking-tight">
                            SMK Negeri 1 Bantul
                        </span>
                    </div>
                </a>
            </div>

            {{-- Center: Instant Documentation Search Trigger --}}
            <div class="flex-1 max-w-md mx-1 sm:mx-2 min-w-0">
                <button type="button"
                        @click="openSearch()"
                        class="w-full flex items-center justify-between gap-2 px-3 sm:px-3.5 py-2.5 rounded-xl bg-slate-100/85 hover:bg-slate-100 border border-slate-200/90 hover:border-slate-300 text-slate-500 text-xs sm:text-sm transition cursor-pointer shadow-sm">
                    <span class="flex items-center gap-2 truncate">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span class="truncate">Cari dokumentasi, API, role, tabel...</span>
                    </span>
                    <kbd class="hidden sm:inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-white border border-slate-200 text-[10px] font-mono font-semibold text-slate-500 shrink-0">
                        Ctrl K
                    </kbd>
                </button>
            </div>

            {{-- Right: Quick Ecosystem Navigation & CTA Actions (Consistent with Public Navbar) --}}
            <div class="flex items-center gap-1.5 xl:gap-2.5 shrink-0">
                <a href="{{ route('landing') }}"
                   class="hidden lg:inline-flex items-center px-3 py-2 text-sm font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100/80 rounded-lg transition whitespace-nowrap">
                    Beranda
                </a>
                <a href="{{ route('docs.index') }}"
                   class="hidden xl:inline-flex items-center px-3 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50/80 hover:bg-emerald-100/80 rounded-lg transition whitespace-nowrap">
                    Dokumentasi
                </a>
                <a href="{{ route('download') }}"
                   class="hidden sm:inline-flex items-center gap-2 px-3.5 xl:px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:text-slate-950 bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200/80 transition shadow-sm whitespace-nowrap shrink-0">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh APK</span>
                </a>
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 px-3.5 xl:px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-sm hover:shadow transition active:scale-[0.99] whitespace-nowrap shrink-0">
                        <span>Dashboard</span>
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-3.5 xl:px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-sm hover:shadow transition active:scale-[0.99] whitespace-nowrap shrink-0">
                        <span class="sm:hidden">Masuk</span>
                        <span class="hidden sm:inline">Masuk Dashboard</span>
                        <svg class="w-4 h-4 text-emerald-400 hidden sm:inline shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- 2. MOBILE & TABLET NAVIGATION DRAWER --}}
    <div x-show="mobileDrawerOpen"
         x-cloak
         class="fixed inset-0 z-50 lg:hidden flex"
         role="dialog"
         aria-modal="true">
        <div x-show="mobileDrawerOpen"
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileDrawerOpen = false"
             class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm"></div>

        <aside x-show="mobileDrawerOpen"
               x-transition:enter="transition ease-out duration-200 transform"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-150 transform"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="relative w-80 max-w-[85vw] bg-white h-full shadow-2xl border-r border-slate-200 flex flex-col">
            <div class="h-20 px-4 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <img src="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}" alt="Logo SMK Negeri 1 Bantul" class="w-8 h-8 object-contain shrink-0">
                    <div class="flex flex-col min-w-0">
                        <span class="font-bold text-slate-900 text-sm tracking-tight leading-snug truncate">LKM Mitra Siswa Abadi</span>
                        <span class="text-[11px] font-medium text-slate-500 truncate">SMK Negeri 1 Bantul · Docs</span>
                    </div>
                </div>
                <button type="button"
                        @click="mobileDrawerOpen = false"
                        class="p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition"
                        aria-label="Tutup menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 border-b border-slate-100">
                <button type="button"
                        @click="mobileDrawerOpen = false; openSearch()"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-100 border border-slate-200 text-xs text-slate-600 font-medium">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Cari topik dokumentasi...</span>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto docs-scroll p-4 space-y-2" aria-label="Navigasi Dokumentasi Mobile">
                @foreach($navigationSections as $section)
                    @php
                        $sectionSlug = $section['slug'];
                    @endphp
                    <div class="border-b border-slate-100 pb-2 last:border-b-0">
                        <button type="button"
                                @click="toggleSection('{{ $sectionSlug }}')"
                                class="w-full flex items-center justify-between py-2 px-2 rounded-lg text-left text-xs font-extrabold uppercase tracking-wider text-slate-700 hover:bg-slate-50 cursor-pointer">
                            <span>{{ $section['title'] }}</span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                 :class="isSectionOpen('{{ $sectionSlug }}') ? 'rotate-90 text-emerald-600' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <ul x-show="isSectionOpen('{{ $sectionSlug }}')" x-cloak class="mt-1 space-y-0.5 pl-2">
                            @foreach($section['pages'] as $navPage)
                                @php
                                    $isActive = ($sectionSlug === $currentSectionSlug && $navPage['slug'] === $currentPageSlug);
                                @endphp
                                <li>
                                    <a href="{{ $navPage['url'] }}"
                                       @click="mobileDrawerOpen = false"
                                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm transition {{ $isActive ? 'bg-emerald-50 text-emerald-800 font-bold border-l-2 border-emerald-600' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-50 font-medium' }}">
                                        <span class="truncate">{{ $navPage['title'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach

                <div class="pt-4 mt-2 border-t border-slate-200 flex flex-col gap-2 text-xs font-semibold">
                    <a href="{{ route('landing') }}" class="px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-50">Beranda</a>
                    <a href="{{ route('download') }}" class="px-3 py-2 rounded-lg text-slate-700 bg-slate-100/80 border border-slate-200/80 text-center">Unduh APK</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-lg text-white bg-slate-900 text-center">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg text-white bg-slate-900 text-center">Masuk Dashboard</a>
                    @endauth
                </div>
            </nav>
        </aside>
    </div>

    {{-- 3. MAIN 3-COLUMN DOCUMENTATION WORKSPACE (Sidebar + Reading Content + Right TOC) --}}
    <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row lg:gap-8 xl:gap-10">

            {{-- LEFT SIDEBAR NAVIGATION (DESKTOP) --}}
            <aside class="hidden lg:block w-72 shrink-0">
                <div class="sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto docs-scroll py-8 pr-4 border-r border-slate-200/80">
                    <div class="mb-5 px-2.5 flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Daftar Dokumentasi</span>
                        <span class="text-[11px] font-mono text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ $currentNumber }}/{{ $totalPages }}</span>
                    </div>

                    <nav class="space-y-3" aria-label="Sidebar Dokumentasi">
                        @foreach($navigationSections as $section)
                            @php
                                $sectionSlug = $section['slug'];
                                $isCurrentSection = ($sectionSlug === $currentSectionSlug);
                            @endphp
                            <div class="rounded-xl transition-colors">
                                <button type="button"
                                        @click="toggleSection('{{ $sectionSlug }}')"
                                        :aria-expanded="isSectionOpen('{{ $sectionSlug }}').toString()"
                                        class="w-full flex items-center justify-between px-2.5 py-2 rounded-lg text-left text-xs font-extrabold uppercase tracking-wider transition cursor-pointer {{ $isCurrentSection ? 'text-slate-900 bg-slate-100/90' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/60' }}">
                                    <span>{{ $section['title'] }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200 shrink-0"
                                         :class="isSectionOpen('{{ $sectionSlug }}') ? 'rotate-90 text-emerald-600' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>

                                <ul x-show="isSectionOpen('{{ $sectionSlug }}')"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="mt-1 space-y-0.5 ml-2.5 pl-2.5 border-l border-slate-200">
                                    @foreach($section['pages'] as $navPage)
                                        @php
                                            $isActive = ($isCurrentSection && $navPage['slug'] === $currentPageSlug);
                                        @endphp
                                        <li>
                                            <a href="{{ $navPage['url'] }}"
                                               @if($isActive) aria-current="page" @endif
                                               class="group flex items-center justify-between gap-2 -ml-[11px] pl-3 pr-2.5 py-1.5 rounded-r-lg text-[13px] transition border-l-2 {{ $isActive ? 'border-emerald-600 bg-emerald-50/90 text-emerald-900 font-bold' : 'border-transparent text-slate-600 hover:text-slate-950 hover:border-slate-300 hover:bg-slate-100/50 font-medium' }}">
                                                <span class="truncate">{{ $navPage['title'] }}</span>
                                                @if($isActive)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 shrink-0"></span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </nav>
                </div>
            </aside>

            {{-- CENTER MAIN READING CONTENT --}}
            <main class="min-w-0 flex-1 py-8 lg:py-10">
                <article class="max-w-3xl mx-auto xl:mx-0">

                    {{-- Breadcrumb Navigation --}}
                    <nav class="flex flex-wrap items-center gap-1.5 text-xs font-medium text-slate-500 mb-5" aria-label="Breadcrumb">
                        @foreach($breadcrumbs as $idx => $crumb)
                            @if($idx > 0)
                                <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            @endif
                            @if(!empty($crumb['url']))
                                <a href="{{ $crumb['url'] }}" class="hover:text-emerald-700 transition-colors">{{ $crumb['label'] }}</a>
                            @else
                                <span class="text-slate-900 font-semibold">{{ $crumb['label'] }}</span>
                            @endif
                        @endforeach
                    </nav>

                    {{-- Article Header --}}
                    <header class="pb-7 mb-8 border-b border-slate-200/90">
                        <div class="flex flex-wrap items-center gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">
                                {{ $currentSection['title'] }}
                            </span>
                            @if(!empty($currentPage['badge']))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-900 text-emerald-400">
                                    {{ $currentPage['badge'] }}
                                </span>
                            @endif
                        </div>

                        <h1 class="text-2xl sm:text-3xl lg:text-[2.15rem] font-extrabold text-slate-900 tracking-tight leading-tight">
                            {{ $currentPage['title'] }}
                        </h1>

                        @if(!empty($currentPage['summary']))
                            <p class="mt-3 text-base sm:text-[1.05rem] text-slate-600 leading-relaxed">
                                {{ $currentPage['summary'] }}
                            </p>
                        @endif

                        {{-- Mobile/Tablet Inline Table of Contents Accordion --}}
                        @if(!empty($tableOfContents))
                            <div x-data="{ openMobileToc: false }" class="mt-5 xl:hidden rounded-xl border border-slate-200 bg-white overflow-hidden">
                                <button type="button"
                                        @click="openMobileToc = !openMobileToc"
                                        class="w-full px-4 py-2.5 flex items-center justify-between text-xs font-bold text-slate-700 bg-slate-50 hover:bg-slate-100/80 transition cursor-pointer">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14" />
                                        </svg>
                                        <span>On This Page ({{ count($tableOfContents) }} bagian)</span>
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400 transition-transform" :class="openMobileToc ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <ul x-show="openMobileToc" x-cloak class="px-4 py-3 space-y-1.5 border-t border-slate-200 text-xs">
                                    @foreach($tableOfContents as $item)
                                        <li class="{{ ($item['level'] ?? 2) === 3 ? 'pl-4' : '' }}">
                                            <a href="#{{ $item['id'] }}"
                                               @click="openMobileToc = false"
                                               class="block py-1 text-slate-600 hover:text-emerald-700 font-medium">
                                                {{ $item['title'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </header>

                    {{-- Main Documentation Body --}}
                    <div class="docs-prose">
                        @include($currentPage['view'])
                    </div>

                    {{-- Previous & Next Page Navigation --}}
                    <nav class="mt-14 pt-8 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4" aria-label="Navigasi Halaman Sebelum dan Berikutnya">
                        <div>
                            @if($prevPage)
                                <a href="{{ $prevPage['url'] }}"
                                   class="group h-full flex flex-col justify-between p-4 sm:p-5 rounded-xl bg-white border border-slate-200 hover:border-emerald-500/60 hover:shadow-sm transition text-left">
                                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-700 transition">
                                        <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        <span>Previous · {{ $prevPage['sectionTitle'] }}</span>
                                    </span>
                                    <span class="mt-1.5 text-sm sm:text-base font-bold text-slate-900 group-hover:text-emerald-700 transition">
                                        {{ $prevPage['title'] }}
                                    </span>
                                </a>
                            @endif
                        </div>

                        <div>
                            @if($nextPage)
                                <a href="{{ $nextPage['url'] }}"
                                   class="group h-full flex flex-col justify-between p-4 sm:p-5 rounded-xl bg-white border border-slate-200 hover:border-emerald-500/60 hover:shadow-sm transition text-right">
                                    <span class="inline-flex items-center justify-end gap-1.5 text-[11px] font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-700 transition">
                                        <span>Next · {{ $nextPage['sectionTitle'] }}</span>
                                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                    <span class="mt-1.5 text-sm sm:text-base font-bold text-slate-900 group-hover:text-emerald-700 transition">
                                        {{ $nextPage['title'] }}
                                    </span>
                                </a>
                            @endif
                        </div>
                    </nav>

                    {{-- Minimalist Documentation Footer --}}
                    <footer class="mt-12 pt-6 border-t border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-slate-500">
                        <div>
                            &copy; {{ date('Y') }} LKM Mitra Siswa Abadi — SMK Negeri 1 Bantul. Dokumentasi disusun berdasarkan audit kode aktual.
                        </div>
                        <div class="flex items-center gap-4 font-medium shrink-0">
                            <a href="{{ route('landing') }}" class="hover:text-slate-900">Beranda</a>
                            <a href="{{ url('/api/docs') }}" target="_blank" class="hover:text-slate-900">API Spec JSON</a>
                            <a href="{{ route('docs.show', ['section' => 'project-evaluation', 'page' => 'project-evaluation-report']) }}" class="text-emerald-700 hover:text-emerald-800 font-semibold">Evaluasi Proyek</a>
                        </div>
                    </footer>
                </article>
            </main>

            {{-- RIGHT SIDEBAR: ON THIS PAGE TABLE OF CONTENTS (DESKTOP) --}}
            <aside class="hidden xl:block w-60 shrink-0">
                <div class="sticky top-20 h-[calc(100vh-5rem)] overflow-y-auto docs-scroll py-10 pl-2">
                    @if(!empty($tableOfContents))
                        <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-3 px-3">
                            On This Page
                        </div>
                        <ul class="space-y-1 border-l border-slate-200 text-xs">
                            @foreach($tableOfContents as $item)
                                @php
                                    $isSub = ($item['level'] ?? 2) === 3;
                                @endphp
                                <li>
                                    <a href="#{{ $item['id'] }}"
                                       class="-ml-px block py-1.5 border-l-2 transition-all {{ $isSub ? 'pl-5 text-[11px]' : 'pl-3' }}"
                                       :class="activeHeading === '{{ $item['id'] }}' ? 'border-emerald-600 text-emerald-700 font-bold' : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300 font-medium'">
                                        {{ $item['title'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </aside>

        </div>
    </div>

    {{-- 4. INSTANT DOCUMENTATION SEARCH MODAL (Ctrl+K / Cmd+K) --}}
    <div x-show="searchOpen"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto p-4 sm:p-6 md:p-16"
         role="dialog"
         aria-modal="true"
         aria-label="Pencarian Dokumentasi HANBANK">
        <div x-show="searchOpen"
             x-transition:enter="transition-opacity ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeSearch()"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"></div>

        <div x-show="searchOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative mx-auto max-w-2xl rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden">

            <div class="flex items-center gap-3 px-4 py-3.5 border-b border-slate-200">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input x-ref="searchInput"
                       x-model="searchQuery"
                       @keydown.arrow-down.prevent="moveSearchSelection(1)"
                       @keydown.arrow-up.prevent="moveSearchSelection(-1)"
                       @keydown.enter.prevent="openSelectedResult()"
                       @keydown.escape.prevent="closeSearch()"
                       type="text"
                       placeholder="Ketik kata kunci: mis. saldo mengendap, api auth, teller, void, erd, flutter..."
                       class="w-full border-0 bg-transparent p-0 text-sm sm:text-base text-slate-900 placeholder:text-slate-400 focus:ring-0 focus:outline-none">
                <button type="button"
                        @click="searchQuery ? searchQuery = '' : closeSearch()"
                        class="px-2 py-1 rounded-md bg-slate-100 hover:bg-slate-200 text-[11px] font-mono font-semibold text-slate-600 cursor-pointer">
                    <span x-text="searchQuery ? 'Clear' : 'ESC'"></span>
                </button>
            </div>

            <div class="max-h-[60vh] overflow-y-auto docs-scroll divide-y divide-slate-100">
                <template x-if="filteredResults.length === 0">
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm font-bold text-slate-800">Tidak ada halaman yang cocok</p>
                        <p class="text-xs text-slate-500 mt-1">Coba kata kunci lain seperti <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">setoran</code>, <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">sanctum</code>, <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">roleguard</code>, atau <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded">evaluasi</code>.</p>
                    </div>
                </template>

                <template x-for="(item, index) in filteredResults" :key="item.url">
                    <div class="p-3 transition-colors"
                         :class="selectedSearchIndex === index ? 'bg-emerald-50/70' : 'hover:bg-slate-50'"
                         @mouseenter="selectedSearchIndex = index">
                        <a :href="item.url"
                           @click="closeSearch()"
                           class="block px-2 py-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700" x-text="item.sectionTitle"></span>
                                <span x-show="item.badge" class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600" x-text="item.badge"></span>
                            </div>
                            <div class="text-sm font-bold text-slate-900 mt-0.5" x-text="item.title"></div>
                            <p class="text-xs text-slate-600 mt-0.5 line-clamp-2" x-text="item.summary"></p>
                        </a>

                        <template x-if="item.matchedHeadings && item.matchedHeadings.length > 0">
                            <div class="mt-2 pl-2 flex flex-wrap gap-1.5">
                                <template x-for="h in item.matchedHeadings" :key="h.url">
                                    <a :href="h.url"
                                       @click="closeSearch()"
                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white border border-slate-200 hover:border-emerald-400 text-[11px] font-medium text-slate-600 hover:text-emerald-800">
                                        <span class="text-emerald-600 font-mono">#</span>
                                        <span x-text="h.title"></span>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center gap-3">
                    <span><kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 font-mono">↑</kbd> <kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 font-mono">↓</kbd> Navigasi</span>
                    <span><kbd class="px-1.5 py-0.5 rounded bg-white border border-slate-200 font-mono">Enter</kbd> Buka</span>
                </div>
                <span x-text="filteredResults.length + ' hasil'"></span>
            </div>
        </div>
    </div>

    <script>
        function hanbankDocsApp(config) {
            return {
                currentSection: config.currentSection,
                searchIndex: config.searchIndex || [],
                tocIds: config.tocIds || [],
                openSections: {},
                mobileDrawerOpen: false,
                searchOpen: false,
                searchQuery: '',
                selectedSearchIndex: 0,
                activeHeading: (config.tocIds && config.tocIds.length > 0) ? config.tocIds[0] : '',

                init() {
                    const storageKey = 'hanbank_docs_open_sections';
                    let saved = {};
                    try {
                        saved = JSON.parse(localStorage.getItem(storageKey) || '{}') || {};
                    } catch (_) {
                        saved = {};
                    }

                    // Pastikan kategori aktif selalu terbuka, sisanya ikuti preferensi atau buka Getting Started
                    this.openSections = {
                        'getting-started': true,
                        ...saved,
                        [this.currentSection]: true,
                    };

                    this.initScrollSpy();
                    this.highlightCodeBlocks();
                },

                isSectionOpen(slug) {
                    return Boolean(this.openSections[slug]);
                },

                toggleSection(slug) {
                    this.openSections[slug] = !this.openSections[slug];
                    try {
                        localStorage.setItem('hanbank_docs_open_sections', JSON.stringify(this.openSections));
                    } catch (_) {}
                },

                openSearch() {
                    this.searchOpen = true;
                    this.selectedSearchIndex = 0;
                    this.$nextTick(() => {
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                            this.$refs.searchInput.select();
                        }
                    });
                },

                closeSearch() {
                    this.searchOpen = false;
                },

                get filteredResults() {
                    const raw = (this.searchQuery || '').trim().toLowerCase();
                    if (!raw) {
                        return this.searchIndex.slice(0, 10).map(item => ({
                            ...item,
                            matchedHeadings: [],
                        }));
                    }

                    const terms = raw.split(/\s+/).filter(Boolean);
                    const matches = [];

                    for (const entry of this.searchIndex) {
                        const titleL = (entry.title || '').toLowerCase();
                        const secL = (entry.sectionTitle || '').toLowerCase();
                        const sumL = (entry.summary || '').toLowerCase();
                        const kwL = (entry.keywords || []).join(' ').toLowerCase();
                        const headings = entry.headings || [];
                        const headL = headings.map(h => (h.title || '')).join(' ').toLowerCase();

                        let score = 0;
                        let allMatched = true;

                        for (const term of terms) {
                            let ts = 0;
                            if (titleL.includes(term)) ts += 12;
                            if (kwL.includes(term)) ts += 8;
                            if (headL.includes(term)) ts += 6;
                            if (secL.includes(term)) ts += 4;
                            if (sumL.includes(term)) ts += 3;
                            if (ts === 0) {
                                allMatched = false;
                                break;
                            }
                            score += ts;
                        }

                        if (allMatched && score > 0) {
                            const matchedHeadings = headings
                                .filter(h => terms.some(t => (h.title || '').toLowerCase().includes(t)))
                                .slice(0, 3)
                                .map(h => ({
                                    title: h.title,
                                    url: entry.url + '#' + h.id,
                                }));

                            matches.push({
                                ...entry,
                                score,
                                matchedHeadings,
                            });
                        }
                    }

                    matches.sort((a, b) => b.score - a.score);
                    return matches.slice(0, 15);
                },

                moveSearchSelection(delta) {
                    const total = this.filteredResults.length;
                    if (total === 0) return;
                    this.selectedSearchIndex = (this.selectedSearchIndex + delta + total) % total;
                },

                openSelectedResult() {
                    const results = this.filteredResults;
                    if (results.length === 0) return;
                    const picked = results[this.selectedSearchIndex] || results[0];
                    if (picked && picked.url) {
                        window.location.href = picked.url;
                    }
                },

                handleGlobalKeydown(e) {
                    const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                    const isTyping = tag === 'input' || tag === 'textarea' || e.target?.isContentEditable;

                    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                        e.preventDefault();
                        this.searchOpen ? this.closeSearch() : this.openSearch();
                    } else if (!isTyping && e.key === '/' && !this.searchOpen) {
                        e.preventDefault();
                        this.openSearch();
                    } else if (e.key === 'Escape') {
                        if (this.searchOpen) this.closeSearch();
                        if (this.mobileDrawerOpen) this.mobileDrawerOpen = false;
                    }
                },

                initScrollSpy() {
                    if (!this.tocIds.length || typeof IntersectionObserver === 'undefined') return;

                    const headingEls = this.tocIds
                        .map(id => document.getElementById(id))
                        .filter(Boolean);

                    if (!headingEls.length) return;

                    const observer = new IntersectionObserver((entries) => {
                        const visible = entries
                            .filter(entry => entry.isIntersecting)
                            .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

                        if (visible.length > 0) {
                            this.activeHeading = visible[0].target.id;
                        }
                    }, {
                        rootMargin: '-90px 0px -68% 0px',
                        threshold: [0, 1],
                    });

                    headingEls.forEach(el => observer.observe(el));
                },

                highlightCodeBlocks() {
                    const esc = (str) => str
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');

                    document.querySelectorAll('code.hanbank-code').forEach((el) => {
                        const lang = (el.getAttribute('data-lang') || 'text').toLowerCase();
                        if (lang === 'text') return;

                        const raw = el.textContent || '';
                        let html = '';

                        if (lang === 'json') {
                            const pattern = /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?|\/\/.*$)/gm;
                            let last = 0;
                            let m;
                            while ((m = pattern.exec(raw)) !== null) {
                                html += esc(raw.slice(last, m.index));
                                const token = m[0];
                                if (token.startsWith('//')) {
                                    html += `<span class="tok-comment">${esc(token)}</span>`;
                                } else if (token.startsWith('"')) {
                                    const cls = /:$/.test(token) ? 'tok-key' : 'tok-string';
                                    html += `<span class="${cls}">${esc(token)}</span>`;
                                } else if (/^(true|false|null)$/.test(token)) {
                                    html += `<span class="tok-keyword">${esc(token)}</span>`;
                                } else {
                                    html += `<span class="tok-number">${esc(token)}</span>`;
                                }
                                last = pattern.lastIndex;
                            }
                            html += esc(raw.slice(last));
                            el.innerHTML = html;
                            return;
                        }

                        if (lang === 'http') {
                            const pattern = /\b(GET|POST|PUT|PATCH|DELETE|HTTP\/[\d.]+)\b|("[^"\n]*")|(#[^\n]*|\/\/[^\n]*)/g;
                            let last = 0;
                            let m;
                            while ((m = pattern.exec(raw)) !== null) {
                                html += esc(raw.slice(last, m.index));
                                const tok = m[0];
                                if (m[3]) {
                                    html += `<span class="tok-comment">${esc(tok)}</span>`;
                                } else if (m[2]) {
                                    html += `<span class="tok-string">${esc(tok)}</span>`;
                                } else {
                                    html += `<span class="tok-method">${esc(tok)}</span>`;
                                }
                                last = pattern.lastIndex;
                            }
                            html += esc(raw.slice(last));
                            el.innerHTML = html;
                            return;
                        }

                        // PHP, Dart, SQL, Bash, Blade tokenizer
                        const pattern = /(\/\/[^\n]*|\/\*[\s\S]*?\*\/|#[^\n]*|--[^\n]*)|('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")|(\$[a-zA-Z_][a-zA-Z0-9_]*)|\b(class|function|public|private|protected|static|return|namespace|use|new|if|else|elseif|match|foreach|as|final|void|string|int|bool|array|Route|DB|Auth|Hash|Future|async|await|const|import|SELECT|FROM|WHERE|JOIN|ON|CREATE|TABLE|INSERT|INTO|VALUES|UPDATE|SET|DELETE|PRIMARY|KEY|FOREIGN|REFERENCES|true|false|null|php|artisan|composer|flutter|npm)\b|\b(\d+(?:\.\d+)?)\b/gm;
                        let last = 0;
                        let m;
                        while ((m = pattern.exec(raw)) !== null) {
                            html += esc(raw.slice(last, m.index));
                            const tok = m[0];
                            if (m[1]) {
                                html += `<span class="tok-comment">${esc(tok)}</span>`;
                            } else if (m[2]) {
                                html += `<span class="tok-string">${esc(tok)}</span>`;
                            } else if (m[3]) {
                                html += `<span class="tok-variable">${esc(tok)}</span>`;
                            } else if (m[4]) {
                                html += `<span class="tok-keyword">${esc(tok)}</span>`;
                            } else if (m[5]) {
                                html += `<span class="tok-number">${esc(tok)}</span>`;
                            } else {
                                html += esc(tok);
                            }
                            last = pattern.lastIndex;
                        }
                        html += esc(raw.slice(last));
                        el.innerHTML = html;
                    });
                },
            };
        }
    </script>
</body>
</html>
