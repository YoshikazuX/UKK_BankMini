<x-docs.heading id="backend-stack" level="2">Backend (PHP &amp; Laravel)</x-docs.heading>
<p>
    Seluruh versi di bawah ini diverifikasi langsung dari <code>composer.json</code>, <code>composer show --direct</code>, dan keluaran <code>php -v</code> / <code>php artisan --version</code> pada repositori <code>belajar_laravel_handa</code>.
</p>

<x-docs.table :headers="['Komponen', 'Versi Aktual / Constraint', 'Sumber Verifikasi', 'Fungsi dalam Sistem']">
    <tr>
        <td class="font-semibold text-slate-900">PHP Runtime</td>
        <td><code>8.5.9</code> CLI lokal (<code>^8.3</code> di composer, PHP <code>8.4</code> di CI)</td>
        <td><code>php -v</code>, <code>composer.json</code>, <code>deploy.yml</code></td>
        <td>Bahasa pemrograman utama sisi server beserta ekstensi GD, ZipArchive, SimpleXML, PDO MySQL/SQLite.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Laravel Framework</td>
        <td><code>13.24.0</code> (constraint <code>^13.8</code>)</td>
        <td><code>composer show --direct</code></td>
        <td>Framework inti MVC, Eloquent ORM, Blade engine, validasi, queue/notifications, dan scheduler Artisan.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Laravel Sanctum</td>
        <td><code>4.3.3</code> (constraint <code>^4.0</code>)</td>
        <td><code>composer show --direct</code></td>
        <td>Penerbitan token autentikasi API (<code>personal_access_tokens</code>) untuk aplikasi Flutter mobile.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Laravel Tinker</td>
        <td><code>3.0.2</code> (constraint <code>^3.0</code>)</td>
        <td><code>composer show --direct</code></td>
        <td>REPL konsol interaktif untuk inspeksi model dan debugging di lingkungan Laravel.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="frontend-stack" level="2">Frontend Web</x-docs.heading>
<x-docs.table :headers="['Komponen', 'Versi / Constraint', 'Sumber Verifikasi', 'Fungsi dalam Sistem']">
    <tr>
        <td class="font-semibold text-slate-900">Tailwind CSS</td>
        <td><code>^3.4.17</code> (+ Tailwind CDN pada beberapa layout dashboard)</td>
        <td><code>package.json</code>, <code>resources/views/</code></td>
        <td>Utility-first CSS framework untuk antarmuka publik, login, dashboard, dan Documentation Center.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">@tailwindcss/forms</td>
        <td><code>^0.5.2</code></td>
        <td><code>package.json</code>, <code>tailwind.config.js</code></td>
        <td>Normalisasi dan styling konsisten untuk elemen form HTML.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Alpine.js</td>
        <td><code>^3.4.2</code></td>
        <td><code>package.json</code>, <code>resources/js/app.js</code></td>
        <td>Interaktivitas UI ringan tanpa build SPA penuh (menu navbar publik, akordeon FAQ, modal search &amp; drawer docs).</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Vite &amp; Laravel Vite Plugin</td>
        <td><code>^8.0.0</code> &amp; <code>^3.1</code></td>
        <td><code>package.json</code></td>
        <td>Bundling aset <code>resources/css/app.css</code> dan <code>resources/js/app.js</code> ke <code>public/build/</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">html5-qrcode</td>
        <td><code>2.3.8</code> (CDN unpkg di view loket teller)</td>
        <td><code>resources/views/teller/dashboard.blade.php</code></td>
        <td>Pemindaian QR code kartu rekening nasabah langsung melalui kamera perangkat pada loket teller web.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="database-stack" level="2">Database &amp; Storage</x-docs.heading>
<ul>
    <li><strong>SQLite / MySQL:</strong> Konfigurasi default <code>.env.example</code> dan konfigurasi test <code>phpunit.xml</code> (<code>:memory:</code>) menggunakan SQLite; ekstensi <code>pdo_mysql</code> &amp; <code>pdo_sqlite</code> aktif serta aplikasi mobile menyebut backend produksi berbasis MySQL.</li>
    <li><strong>Driver Session, Queue &amp; Cache:</strong> Konfigurasi bawaan <code>.env.example</code> memakai driver <code>database</code> untuk session, queue, dan cache.</li>
    <li><strong>Media Sanitization Engine:</strong> Unggahan foto profil diproses oleh <code>ProfileImageService</code> menggunakan ekstensi PHP <code>GD</code> &amp; <code>fileinfo</code> untuk melakukan re-encoding aman ke kanvas JPEG/PNG baru.</li>
</ul>

<x-docs.heading id="mobile-stack" level="2">Mobile Development (Flutter)</x-docs.heading>
<p>
    Versi berikut diverifikasi dari <code>flutter --version</code> dan <code>d:\Project UKK\ukk_bankmini\pubspec.yaml</code> (versi aplikasi <code>2.4.0+1</code>):
</p>
<x-docs.table :headers="['Pustaka / Tool', 'Versi Aktual', 'Peran di ukk_bankmini']">
    <tr>
        <td class="font-semibold text-slate-900">Flutter SDK &amp; Dart</td>
        <td>Flutter <code>3.41.9</code> · Dart <code>3.11.5</code> (<code>^3.11.5</code>)</td>
        <td>Framework UI lintas platform dan bahasa utama aplikasi mobile.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">dio</td>
        <td><code>^5.11.1</code></td>
        <td>HTTP client dengan interceptor Sanctum Bearer Token dan interceptor log/mock testing.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">mobile_scanner</td>
        <td><code>^7.4.2</code></td>
        <td>Pemindaian QR kartu rekening nasabah pada aplikasi Android (Teller POS / lookup cepat).</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">qr_flutter</td>
        <td><code>^4.1.0</code></td>
        <td>Pembangkitan QR code digital langsung pada layar kartu nasabah siswa.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">fl_chart</td>
        <td><code>^0.70.2</code></td>
        <td>Visualisasi grafik tren pemasukan dan pengeluaran keuangan 6 bulan terakhir.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">shared_preferences</td>
        <td><code>^2.5.3</code></td>
        <td>Penyimpanan persisten token Sanctum, role aktif, cache JSON pengguna, dan URL server kustom.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">google_fonts, intl, percent_indicator, url_launcher</td>
        <td><code>^6.2.1</code>, <code>^0.20.2</code>, <code>^4.2.5</code>, <code>^6.3.1</code></td>
        <td>Tipografi, pemformatan Rupiah/tanggal, progress bar Saving Goals, dan tautan eksternal pusat bantuan.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="dev-tools-catatan" level="2">Development Tools &amp; Catatan Dependensi</x-docs.heading>
<x-docs.callout type="warning" title="Audit Dependensi Lokal vs composer.json">
    <ul class="list-disc pl-5 space-y-1 my-1">
        <li><strong>PhpSpreadsheet:</strong> Paket <code>phpoffice/phpspreadsheet ^5.11</code> tercatat pada <code>composer.json</code> dan <code>composer.lock</code>, namun <strong>tidak terpasang</strong> di direktori <code>vendor/</code> lokal dan tidak di-import oleh kode controller. Fitur import Excel pada <code>NasabahController::parseXlsxNative()</code> menggunakan pembacaan XML ZIP native PHP (<code>ZipArchive</code> + <code>SimpleXML</code>).</li>
        <li><strong>Dev Dependencies (--no-dev):</strong> Paket <code>phpunit/phpunit ^12.5.12</code>, <code>laravel/pint ^1.27</code>, <code>laravel/breeze ^2.4</code>, dan <code>laravel/boost ^2.10</code> tercatat pada bagian <code>require-dev</code> di <code>composer.json</code>, tetapi tidak terpasang di <code>vendor/</code> lokal saat audit karena instalasi produksi/lokal menggunakan flag tanpa dev dependencies.</li>
    </ul>
</x-docs.callout>

