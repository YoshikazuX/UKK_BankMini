<!-- resources/views/components/public_navbar.blade.php -->
{{-- Public navigation bar used only on the landing page --}}
<nav class="bg-teal-600 text-white fixed w-full z-10 top-0 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('images/SMK_Negeri_1_Bantul_logo.png') }}" alt="Bank Mini Logo" class="h-8 w-auto" />
                    <span class="font-semibold text-xl">Bank Mini Sekolah</span>
                </a>
            </div>
            <!-- Desktop menu -->
            <div class="hidden md:flex md:space-x-8">
                <a href="#hero" class="hover:underline">Beranda</a>
                <a href="#tentang" class="hover:underline">Tentang</a>
                <a href="#layanan" class="hover:underline">Layanan</a>
                <a href="#fitur" class="hover:underline">Fitur</a>

                <a href="#faq" class="hover:underline">FAQ</a>
                <a href="#kontak" class="hover:underline">Kontak</a>
            </div>
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" class="text-white focus:outline-none" id="nav-toggle">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <!-- Mobile menu, hidden by default -->
    <div class="md:hidden hidden" id="mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="#hero" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-teal-500">Beranda</a>
            <a href="#tentang" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-teal-500">Tentang</a>
            <a href="#layanan" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-teal-500">Layanan</a>
            <a href="#fitur" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-teal-500">Fitur</a>

            <a href="#faq" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-teal-500">FAQ</a>
            <a href="#kontak" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-teal-500">Kontak</a>
        </div>
    </div>
</nav>

<script>
    // Simple toggle for mobile menu
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('nav-toggle');
        const menu = document.getElementById('mobile-menu');
        if (toggle && menu) {
            toggle.addEventListener('click', function () {
                menu.classList.toggle('hidden');
            });
        }
    });
</script>
