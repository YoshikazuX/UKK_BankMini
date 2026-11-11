<x-docs.heading id="struktur-view-dan-layout" level="2">Struktur Views &amp; Layouts Blade</x-docs.heading>
<p>
    Antarmuka web HANBANK dibangun dengan template engine <strong>Laravel Blade</strong> yang berada di direktori <code>resources/views/</code>. Pemisahan layout dikelompokkan berdasarkan zona akses: halaman publik, otentikasi, dashboard internal per peran, halaman cetak dokumen, serta Documentation Center.
</p>

<x-docs.table :headers="['Direktori / Layout', 'Digunakan Oleh', 'Komponen Utama']">
    <tr>
        <td><code>layouts/app.blade.php</code></td>
        <td><code>landing.blade.php</code>, <code>download.blade.php</code></td>
        <td>Memuat font Plus Jakarta Sans &amp; Figtree, Vite bundle (<code>@@vite</code>), Tailwind CDN, serta menyembunyikan navbar internal pada <code>/</code> dan <code>/download</code>.</td>
    </tr>
    <tr>
        <td><code>layouts/docs.blade.php</code></td>
        <td><code>DocsController</code> (27 halaman dokumentasi)</td>
        <td>Shell dokumentasi 3 kolom (sidebar kiri collapsible, konten utama, right TOC scroll-spy), drawer mobile, dan modal search <code>Ctrl+K</code> via Alpine.js + Vite build.</td>
    </tr>
    <tr>
        <td><code>components/public_navbar.blade.php</code></td>
        <td>Landing page &amp; halaman download</td>
        <td>Navbar publik sticky berbasis Alpine.js (<code>mobileMenuOpen</code>, <code>isScrolled</code>), CTA Unduh APK, Masuk Dashboard, dan Documentation Center.</td>
    </tr>
    <tr>
        <td><code>components/navbar.blade.php</code></td>
        <td>Admin, Teller, Supervisor, Nasabah (<code>&lt;x-navbar /&gt;</code>)</td>
        <td>Navbar operasional adaptif berbasis <code>Auth::user()-&gt;role</code> dengan badge notifikasi tugas, dropdown sub-menu per peran, menu profil, dan drawer mobile.</td>
    </tr>
    <tr>
        <td><code>admin/</code>, <code>teller/</code>, <code>supervisor/</code>, <code>nasabah/</code>, <code>avalis/</code>, <code>laporan/</code></td>
        <td>Seluruh modul internal perbankan</td>
        <td>Tampilan operasional mandiri yang merender head HTML lengkap, <code>&lt;x-navbar /&gt;</code>, serta layout cetak khusus (<code>cetak-*.blade.php</code>, <code>slip-*.blade.php</code>).</td>
    </tr>
</x-docs.table>

<x-docs.heading id="aset-css-dan-javascript" level="2">Manajemen Aset CSS, Vite &amp; CDN Hybrid</x-docs.heading>
<p>
    Proyek menggabungkan dua mekanisme asset styling/scripting yang perlu dipahami pengembang sebelum memodifikasi tampilan:
</p>
<ul>
    <li><strong>Vite Asset Pipeline:</strong> File <code>resources/css/app.css</code> (direktif Tailwind <code>@tailwind base/components/utilities</code> + <code>[x-cloak]</code>) dan <code>resources/js/app.js</code> (inisialisasi <code>Alpine.start()</code>) dikompilasi oleh <strong>Vite 8.2.1</strong> (<code>^8.0.0</code>) + <strong>Tailwind CSS 3.4.19</strong> (<code>^3.4.17</code>) ke <code>public/build/manifest.json</code>.</li>
    <li><strong>Tailwind CDN &amp; Pustaka Browser:</strong> Sebagian besar halaman dashboard internal (<code>admin/dashboard</code>, <code>teller/dashboard</code>, <code>supervisor/dashboard</code>, <code>nasabah/dashboard</code>) serta <code>auth/login.blade.php</code> dan <code>layouts/app.blade.php</code> masih menyertakan <code>https://cdn.tailwindcss.com</code>, sedangkan <code>teller/dashboard.blade.php</code> memuat <code>html5-qrcode@2.3.8</code> untuk kamera pemindai QR di loket kasir.</li>
    <li><strong>Konfigurasi Konflik Nol pada Documentation Center:</strong> Layout <code>layouts/docs.blade.php</code> murni mengandalkan bundle lokal <code>@@vite(['resources/css/app.css', 'resources/js/app.js'])</code> dan utilitas Tailwind v3.4 tanpa menyentuh <code>layouts/app.blade.php</code> maupun view dashboard yang sudah ada.</li>
</ul>

<x-docs.heading id="design-system-hanbank" level="2">Design System &amp; Palet Warna HANBANK</x-docs.heading>
<p>
    Baik aplikasi web Laravel maupun aplikasi mobile Flutter (<code>lib/core/theme/app_theme.dart</code>) menerapkan sistem desain perbankan modern yang konsisten: kontras tinggi antara permukaan baca netral dengan warna institusional <strong>Navy/Slate</strong> dan aksen hijau <strong>Emerald/Teal</strong>.
</p>

<x-docs.table :headers="['Token Desain', 'Nilai Aktual (Web Tailwind / Flutter)', 'Penggunaan dalam UI']">
    <tr>
        <td class="font-semibold text-slate-900">Canvas Utama</td>
        <td><code>#F8FAFC</code> (<code>bg-[#F8FAFC]</code>, <code>bg-slate-50</code>)</td>
        <td>Latar belakang utama landing page, dashboard operasional, dan area baca dokumentasi.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Surface Kartu</td>
        <td><code>#FFFFFF</code> dengan border <code>#E2E8F0</code> (<code>border-slate-200</code>)</td>
        <td>Kartu widget KPI, tabel mutasi, form input, dan container konten.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Primary Navy / Dark Slate</td>
        <td><code>#0F172A</code> (<code>slate-900</code>), <code>#1E293B</code> (<code>slate-800</code>), <code>#101B2D</code> (Flutter)</td>
        <td>Navbar internal (<code>bg-slate-800</code>), tombol CTA utama, kartu rekening digital, dan header dark code block.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Primary Accent (Emerald / Teal)</td>
        <td><code>#10B981</code> / <code>#059669</code> (Web) &amp; <code>#19C9A3</code> / <code>#0F766E</code> (Flutter)</td>
        <td>Status aktif, nominal saldo utama, tombol konfirmasi supervisor, dan indikator halaman aktif.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Semantik Perbankan</td>
        <td>Amber (<code>#D99A28</code>), Rose (<code>#DC5656</code>), Sky (<code>#3986C8</code>)</td>
        <td>Status pending/hold, error/void/nonaktif, dan callout informasi teknis.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Tipografi</td>
        <td><code>Plus Jakarta Sans</code> + <code>Figtree</code> + <code>JetBrains Mono</code></td>
        <td>Seluruh angka saldo dan kode rekening memakai <code>tabular-nums</code> agar presisi vertikal.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="komponen-dan-cetak" level="2">Komponen Navigasi &amp; Tampilan Cetak (Print)</x-docs.heading>
<p>
    View cetak seperti <code>slip-setoran.blade.php</code>, <code>slip-penarikan.blade.php</code>, <code>nasabah/cetak-buku.blade.php</code>, <code>supervisor/cetak-harian.blade.php</code>, dan <code>laporan/cetak-*.blade.php</code> menggunakan kelas utilitas <code>no-print</code> pada elemen kontrol browser dan menyertakan teks terbilang dari <code>TerbilangHelper::terbilang()</code> agar dokumen siap dicetak langsung di loket bank mini.
</p>

