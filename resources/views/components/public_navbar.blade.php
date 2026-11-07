<!-- resources/views/components/public_navbar.blade.php -->
{{-- Redesigned Public Navigation Bar for Bank Mini Sekolah --}}
<nav x-data="{ mobileMenuOpen: false, isScrolled: false }" 
     x-init="window.addEventListener('scroll', () => { isScrolled = window.scrollY > 20 })"
     :class="{ 'shadow-md border-slate-200/90 bg-white/95 backdrop-blur-md': isScrolled, 'border-slate-200/60 bg-white/90 backdrop-blur-sm': !isScrolled }"
     class="sticky top-0 z-50 w-full border-b transition-all duration-300" 
     id="main-public-navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
            
            <!-- BRAND / LOGO INSTITUSI -->
            <a href="{{ route('landing') }}" class="flex items-center gap-3.5 group focus:outline-none">
                <img src="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}" 
                     alt="Logo SMK Negeri 1 Bantul" 
                     class="h-10 sm:h-11 w-auto object-contain" />
                <div class="flex flex-col">
                    <span class="font-bold text-slate-900 text-base sm:text-lg tracking-tight leading-snug group-hover:text-emerald-700 transition">
                        LKM Mitra Siswa Abadi
                    </span>
                    <span class="text-xs font-medium text-slate-500 tracking-tight">
                        SMK Negeri 1 Bantul — Bank Mini Sekolah
                    </span>
                </div>
            </a>

            <!-- DESKTOP NAVIGATION LINKS -->
            <div class="hidden lg:flex items-center gap-1 xl:gap-2">
                <a href="{{ route('landing') }}#hero" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100/80 rounded-lg transition">
                    Beranda
                </a>
                <a href="{{ route('landing') }}#layanan" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100/80 rounded-lg transition">
                    Layanan
                </a>
                <a href="{{ route('landing') }}#platform" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100/80 rounded-lg transition">
                    Platform
                </a>
                <a href="{{ route('landing') }}#fitur" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100/80 rounded-lg transition">
                    Fitur Pengguna
                </a>
                <a href="{{ route('landing') }}#keamanan" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100/80 rounded-lg transition">
                    Keamanan
                </a>
                <a href="{{ route('landing') }}#faq" class="px-3.5 py-2 text-sm font-semibold text-slate-700 hover:text-slate-950 hover:bg-slate-100/80 rounded-lg transition">
                    FAQ
                </a>
            </div>

            <!-- DESKTOP CTA ACTIONS -->
            <div class="hidden sm:flex items-center gap-3">
                <a href="{{ route('download') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-700 hover:text-slate-950 bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200/80 transition shadow-sm">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Unduh APK</span>
                </a>
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow-sm hover:shadow transition active:scale-[0.99]">
                    <span>Masuk Dashboard</span>
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            <!-- MOBILE MENU BUTTON -->
            <div class="flex items-center gap-2 lg:hidden">
                <a href="{{ route('login') }}" class="sm:hidden px-3 py-1.5 text-xs font-semibold text-white bg-slate-900 rounded-lg">
                    Masuk
                </a>
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="p-2.5 rounded-xl text-slate-600 hover:text-slate-950 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-900/10 transition"
                        aria-label="Toggle navigation menu">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- MOBILE MENU DROPDOWN -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="lg:hidden border-t border-slate-200/80 bg-white/98 backdrop-blur-lg px-4 pt-3 pb-6 shadow-xl space-y-1">
        
        <div class="grid grid-cols-2 gap-1.5 py-2">
            <a href="{{ route('landing') }}#hero" @click="mobileMenuOpen = false" class="px-3.5 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                Beranda
            </a>
            <a href="{{ route('landing') }}#layanan" @click="mobileMenuOpen = false" class="px-3.5 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                Layanan
            </a>
            <a href="{{ route('landing') }}#platform" @click="mobileMenuOpen = false" class="px-3.5 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                Platform
            </a>
            <a href="{{ route('landing') }}#fitur" @click="mobileMenuOpen = false" class="px-3.5 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                Fitur Pengguna
            </a>
            <a href="{{ route('landing') }}#keamanan" @click="mobileMenuOpen = false" class="px-3.5 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                Keamanan
            </a>
            <a href="{{ route('landing') }}#faq" @click="mobileMenuOpen = false" class="px-3.5 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                FAQ
            </a>
        </div>

        <div class="pt-3 border-t border-slate-200/70 flex flex-col gap-2.5">
            <a href="{{ route('download') }}" 
               class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold text-slate-800 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Aplikasi Mobile (APK)</span>
            </a>
            <a href="{{ route('login') }}" 
               class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 shadow transition">
                <span>Masuk Dashboard Petugas</span>
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </div>
</nav>
