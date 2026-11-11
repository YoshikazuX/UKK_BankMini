<x-docs.heading id="arsitektur-pengujian" level="2">Arsitektur Pengujian Dual-Repository</x-docs.heading>
<p>
    Ekosistem HANBANK memiliki skenario pengujian terpisah untuk sisi server dan sisi klien mobile sesuai dengan teknologi masing-masing repositori:
</p>
<ul>
    <li><strong>Backend Web &amp; REST API (<code>belajar_laravel_handa/tests/</code>):</strong> Memakai <strong>PHPUnit 12</strong> (<code>phpunit/phpunit ^12.5.12</code> di <code>composer.json</code> bagian <code>require-dev</code>) yang dikelompokkan menjadi folder <code>tests/Feature/</code> dan <code>tests/Unit/</code>, memperluas <code>Tests\TestCase</code> dengan trait <code>RefreshDatabase</code> untuk mereset skema migrasi penuh sebelum setiap skenario.</li>
    <li><strong>Aplikasi Mobile (<code>ukk_bankmini/test/</code>):</strong> Memakai paket pengujian bawaan Flutter (<code>flutter_test</code>) untuk melakukan unit test model domain, core storage/network, widget test UI per peran, serta integrasi service API menggunakan interceptor mock terisolasi (<code>TestApiMockInterceptor</code>).</li>
</ul>

<x-docs.heading id="lingkungan-dan-konfigurasi" level="2">Konfigurasi phpunit.xml &amp; Isolasi Mock Mobile</x-docs.heading>
<p>
    Konfigurasi lingkungan uji Laravel pada <code>phpunit.xml</code> mengisolasi eksekusi pengujian dari basis data operasional agar tidak memengaruhi data produksi:
</p>
<x-docs.code-block lang="text" title="Konfigurasi Utama phpunit.xml">
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_STORE=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
MAIL_MAILER=array
BCRYPT_ROUNDS=4
</x-docs.code-block>
<p>
    Di sisi mobile, file <code>test/test_api_mock_interceptor.dart</code> mencegat panggilan HTTP <code>Dio</code> pada tingkat interceptor nomor indeks <code>0</code> khusus selama test berjalan. Dengan pola ini, pengujian Flutter dapat dijalankan cepat dan deterministik tanpa harus menyalakan server Laravel ataupun menyentuh database sungguhan.
</p>

<x-docs.heading id="status-eksekusi-lingkungan" level="2">Status Lingkungan Uji Saat Audit</x-docs.heading>
<x-docs.callout type="warning" title="Fakta Lingkungan Lokal (--no-dev pada Laravel)">
    Pada saat audit repositori dijalankan:
    <ul class="list-disc pl-5 space-y-1 mt-1">
        <li><strong>Flutter Test Suite:</strong> Berhasil dijalankan langsung melalui <code>flutter test --no-pub</code> dan menuntaskan <strong>40/40 test case (100% lulus)</strong> secara terverifikasi.</li>
        <li><strong>Laravel PHPUnit Suite:</strong> Direktori <code>vendor/</code> lokal terpasang tanpa paket pengembang (<em>mode <code>--no-dev</code></em>), sehingga perintah <code>php artisan test</code> belum terdefinisi dan biner <code>phpunit</code> tidak tersedia di <code>vendor/bin/</code>. Dokumentasi di bagian ini hanya menyajikan skenario uji aktual yang terinspeksi di kode dan hasil eksekusi Flutter yang benar-benar dijalankan—tanpa merekayasa angka kelulusan PHPUnit.</li>
    </ul>
</x-docs.callout>

