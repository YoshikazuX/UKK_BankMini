<x-docs.heading id="katalog-skenario-laravel" level="2">Katalog File &amp; Skenario Uji Laravel</x-docs.heading>
<p>
    Tabel berikut merangkum seluruh berkas uji pada repositori <code>belajar_laravel_handa/tests/</code> yang diaudit langsung dari kode sumber:
</p>
<x-docs.table :headers="['Berkas Test', 'Kategori', 'Jumlah Method', 'Ringkasan Skenario']">
    <tr><td><code>Unit/ExampleTest.php</code></td><td>Unit</td><td>1</td><td>Assertion dasar contoh kerangka PHPUnit.</td></tr>
    <tr><td><code>Feature/ExampleTest.php</code></td><td>Smoke</td><td>1</td><td>Respon dasar root aplikasi.</td></tr>
    <tr><td><code>Feature/LandingPageTest.php</code></td><td>Web / UI</td><td>4</td><td>Render 200 landing page, brand, routes, device mockups &amp; FAQ.</td></tr>
    <tr><td><code>Feature/DownloadPageTest.php</code></td><td>Web / UI</td><td>3</td><td>Render 200 halaman download APK, navigasi, panduan instalasi.</td></tr>
    <tr><td><code>Feature/DocumentationCenterTest.php</code></td><td>Docs / QC</td><td>6</td><td>Render seluruh 27 halaman dokumentasi, endpoint search, prev/next, breadcrumbs, TOC anchors.</td></tr>
    <tr><td><code>Feature/DashboardWidgetsTest.php</code></td><td>Functional / Security</td><td>8</td><td>Widget KPI Admin, Teller, Supervisor, konsistensi agregat, proteksi token &amp; RBAC.</td></tr>
    <tr><td><code>Feature/BankMiniFiturSempurnaTest.php</code></td><td>Functional / Security</td><td>7</td><td>Pencarian, detail nasabah, sensor audit log, SoD, reset password, avalis, profil/audit.</td></tr>
    <tr><td><code>Feature/RevisiLengkapBankMiniTest.php</code></td><td>Functional / Domain</td><td>6</td><td>Privasi loket &amp; QR, min setor/mengendap, tutup kas, auto-close, lupa PIN, import/soft delete.</td></tr>
    <tr><td><code>Feature/TellerPenyempurnaanTest.php</code></td><td>Functional / Concurrency</td><td>9</td><td>Akun nonaktif, limit saldo/brankas, idempotency &amp; polling, commit, void reversal, selisih, filter.</td></tr>
    <tr><td><code>Feature/SupervisorPenyempurnaanTest.php</code></td><td>Functional / Concurrency</td><td>10</td><td>Approval center, detail kas, approval history, wajib alasan, monitoring void, rekonsiliasi, filter, notifikasi, locking, hold.</td></tr>
    <tr><td><code>Feature/ForgotPasswordOtpTest.php</code></td><td>Auth / Validation</td><td>3</td><td>Request OTP tanpa error truncation, penolakan OTP salah, reset password sukses.</td></tr>
    <tr><td><code>Feature/NasabahApiTest.php</code></td><td>REST API</td><td>13</td><td>Login/logout Sanctum, saldo, verify PIN, manajemen nasabah, cek rekening, blokir transfer, ganti password/PIN, ringkasan.</td></tr>
    <tr><td><code>Feature/NasabahMobileFiturLengkapTest.php</code></td><td>REST API</td><td>6</td><td>Emergency freeze + audit log, saving goals CRUD, badges dinamis, session logs, notifikasi, tren 6 bulan.</td></tr>
    <tr><td><code>Feature/ProfileTest.php</code></td><td>Breeze Profile</td><td>5</td><td>Tampil profil, update profil, status verifikasi email, hapus akun &amp; password konfirmasi.</td></tr>
    <tr><td><code>Feature/Auth/* (6 berkas)</code></td><td>Breeze Scaffolding</td><td>18</td><td>Login, logout, register, verifikasi email, konfirmasi &amp; reset password bawaan Breeze.</td></tr>
</x-docs.table>

<x-docs.heading id="katalog-skenario-flutter" level="2">Katalog Skenario Uji Flutter (ukk_bankmini)</x-docs.heading>
<x-docs.table :headers="['Berkas Test Flutter', 'Jumlah Test Case', 'Cakupan Skenario']">
    <tr><td><code>test/fitur_lengkap_nasabah_test.dart</code></td><td>5</td><td>Progress SavingGoalModel, evaluasi tier BadgeModel, metadata SessionModel, NotifikasiModel, flag status nonaktif NasabahModel.</td></tr>
    <tr><td><code>test/nasabah_module_test.dart</code></td><td>5</td><td>Parsing JSON TransaksiModel, deteksi mutasi keluar, kalkulasi saldo efektif (-Rp10.000), penonaktifan transfer P2P, validasi PIN 6 digit.</td></tr>
    <tr><td><code>test/network_test.dart</code></td><td>6</td><td>Path ApiConstants, parsing sukses/error ApiResponse, simpan/hapus token &amp; role di SessionStorage, custom Base URL, setup interceptor ApiClient.</td></tr>
    <tr><td><code>test/teller_module_test.dart</code></td><td>7</td><td>Search nasabah, detail + saldo efektif, setor tunai kode 101, tarik tunai PIN + kas kode 201, posisi kas brankas, droping kas, submit daily report.</td></tr>
    <tr><td><code>test/supervisor_admin_test.dart</code></td><td>10</td><td>8 pengujian SupervisorApiService (pengajuan kas, setujui/tolak, daily report list/update, ringkasan harian, saldo kelas, mutasi jurnal) + 2 widget test (SupervisorMainScreen &amp; AdminMainScreen).</td></tr>
    <tr><td><code>test/widget_test.dart</code></td><td>7</td><td>4 unit fixture domain + 3 widget test render aplikasi utama (LoginScreen, SplashScreen, OnboardingScreen).</td></tr>
</x-docs.table>
<p class="text-xs text-slate-500">
    *Total berkas pendukung tambahan: <code>test/test_api_mock_interceptor.dart</code> (interceptor mock HTTP untuk isolasi uji Flutter).
</p>

