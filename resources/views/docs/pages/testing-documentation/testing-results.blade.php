<x-docs.heading id="ringkasan-hasil-eksekusi" level="2">Ringkasan Status Pengujian Aktual</x-docs.heading>
<p>
    Sesuai standar akurasi audit dokumentasi, bagian ini memisahkan secara tegas antara suite yang <strong>telah dieksekusi dan lulus pada sesi audit ini</strong> dengan suite yang <strong>belum dapat dieksekusi di lingkungan lokal</strong> beserta alasan teknisnya.
</p>

<x-docs.table :headers="['Suite / Mekanisme Uji', 'Total Kasus', 'Status Aktual', 'Keterangan Bukti Eksekusi']">
    <tr>
        <td class="font-semibold text-slate-900">Flutter Unit, Service &amp; Widget Suite (<code>ukk_bankmini</code>)</td>
        <td>40 kasus (6 berkas test)</td>
        <td><x-docs.status-badge status="Tested and Verified" /></td>
        <td>Dieksekusi dengan perintah <code>flutter test --no-pub</code> (Flutter 3.41.9 / Dart 3.11.5); seluruh <strong>40/40 lulus (0 gagal)</strong> dalam ~54 detik menggunakan <code>TestApiMockInterceptor</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Laravel PHPUnit Feature &amp; Unit Suite (<code>belajar_laravel_handa</code>)</td>
        <td>100 method (20 berkas test termasuk Documentation Center)</td>
        <td><x-docs.status-badge status="Not Yet Tested" /></td>
        <td>Perintah <code>php artisan test</code> gagal dijalankan karena <code>vendor/</code> lokal dipasang dalam mode <code>--no-dev</code> (paket <code>phpunit/phpunit</code> &amp; <code>nunomaduro/collision</code> belum terpasang).</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Verifikasi Syntax PHP, Routing &amp; Render HTTP Documentation Center</td>
        <td>27 halaman + API search + build Vite</td>
        <td><x-docs.status-badge status="Tested and Verified" /></td>
        <td>Diverifikasi melalui pemeriksaan lint <code>php -l</code>, inspeksi <code>artisan route:list</code>, simulasi request HTTP kernel Laravel pada seluruh rute <code>/docs*</code>, serta kompilasi <code>npm run build</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Live E2E Mobile &harr; Server &amp; Uji Beban</td>
        <td>—</td>
        <td><x-docs.status-badge status="Not Yet Tested" /></td>
        <td>Belum tersedia rangkaian pengujian beban (<em>load testing</em>) maupun skrip E2E otomatis lintas APK Android dengan server MySQL produksi.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="hasil-eksekusi-flutter" level="2">Hasil Aktual Flutter Test Suite (40/40 Lulus)</x-docs.heading>
<x-docs.code-block lang="bash" title="Output Terverifikasi — flutter test --no-pub (d:\Project UKK\ukk_bankmini)">
Flutter 3.41.9 • channel stable
Tools • Dart 3.11.5 • DevTools 2.54.2
00:00 +0: loading D:/Project UKK/ukk_bankmini/test/fitur_lengkap_nasabah_test.dart
...
00:53 +40: D:/Project UKK/ukk_bankmini/test/widget_test.dart: (tearDownAll)
00:54 +40: All tests passed!
</x-docs.code-block>
<p>
    Rincian kelulusan per file pada eksekusi tersebut: <code>fitur_lengkap_nasabah_test.dart</code> (5/5), <code>nasabah_module_test.dart</code> (5/5), <code>network_test.dart</code> (6/6), <code>teller_module_test.dart</code> (7/7), <code>supervisor_admin_test.dart</code> (10/10), dan <code>widget_test.dart</code> (7/7). Seluruh pemanggilan API berjalan terhadap mock Dio (<code>TestApiMockInterceptor</code>), bukan server live.
</p>

<x-docs.heading id="status-eksekusi-laravel" level="2">Status Suite Laravel PHPUnit Lokal &amp; Langkah Reproduksi</x-docs.heading>
<x-docs.code-block lang="bash" title="Keluaran Aktual di Workspace Lokal (--no-dev)">
$ php artisan test --no-ansi
Laravel Framework 13.24.0 · PHP 8.5.9 (cli)
   ERROR  Command "test" is not defined. Did you mean one of these?
  ⇂ make:test
  ⇂ schedule:test
</x-docs.code-block>
<p>
    Untuk menjalankan penuh 20 berkas PHPUnit di mesin penguji dengan koneksi internet / cache Composer yang memiliki dev dependencies:
</p>
<x-docs.code-block lang="bash" title="Langkah Menjalankan PHPUnit Setelah Memasang Dev Dependencies">
# 1. Pasang kembali paket require-dev (phpunit, faker, pint, collision, dll.)
composer install

# 2. Jalankan seluruh Feature &amp; Unit Tests (otomatis memakai SQLite :memory: dari phpunit.xml)
php artisan test

# 3. Atau jalankan khusus pengujian Documentation Center
php artisan test --filter=DocumentationCenterTest
</x-docs.code-block>

