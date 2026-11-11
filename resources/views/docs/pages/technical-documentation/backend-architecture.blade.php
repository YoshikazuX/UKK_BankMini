<x-docs.heading id="struktur-direktori-laravel" level="2">Struktur Direktori Laravel</x-docs.heading>
<p>
    Backend HANBANK mengikuti struktur konvensi Laravel 13 yang diperluas dengan folder domain <code>app/Helpers</code>, <code>app/Services</code>, <code>app/Notifications</code>, dan <code>app/Console/Commands</code>. Konfigurasi bootstrap utama berada di <code>bootstrap/app.php</code>, yang menggantikan <code>app/Http/Kernel.php</code> lama (pola Laravel 11–13).
</p>

<x-docs.code-block lang="text" title="Peta Direktori Inti (belajar_laravel_handa)">
app/
├── Console/Commands/
│   └── AutoCloseKasCommand.php         # Artisan: kas:auto-close (otomatis tutup kas 17:00)
├── Helpers/
│   ├── AuditHelper.php                 # Pencatatan audit_logs + redaksi key sensitif
│   ├── AvalisSyncHelper.php            # Sinkronisasi otomatis User(teller) -&gt; Avalis &amp; Absensi
│   ├── NotificationHelper.php          # Dispatcher SupervisorTaskNotification &amp; DB alert
│   ├── SettingHelper.php               # Pembacaan PengaturanSistem (fallback default)
│   └── TerbilangHelper.php             # Konversi terbilang Rupiah &amp; kode jurusan (RPL, TKJ, dst.)
├── Http/
│   ├── Controllers/
│   │   ├── Api/                        # 12 API Controllers untuk Mobile &amp; REST Integrations
│   │   └── *.php                       # 13 Web Controllers (Auth, Admin, Teller, Supervisor, Docs, ...)
│   ├── Middleware/
│   │   └── RoleGuard.php               # Aliased sebagai 'role' di bootstrap/app.php
│   └── Requests/
├── Models/                             # 17 Eloquent Models + UserApp
├── Notifications/
│   └── SupervisorTaskNotification.php  # Database notification channel
└── Services/
    ├── DashboardWidgetService.php      # Agregasi KPI lintas role &amp; operational alerts
    └── ProfileImageService.php         # Validasi &amp; re-encoding gambar 6 lapis berbasis GD
bootstrap/app.php                       # Routing web/api/console, alias 'role', CSRF api/*
config/hanbank_docs.php                 # Registry navigasi &amp; indeks HANBANK Documentation Center
routes/
├── api.php                             # Seluruh endpoint /api/*
├── auth.php                            # Scaffolding Breeze (tidak di-include di routes/web.php)
├── console.php                         # Schedule command kas:auto-close setiap pukul 17:00
└── web.php                             # Seluruh route web operasional, publik &amp; dokumentasi
</x-docs.code-block>

<x-docs.heading id="layer-controllers" level="2">Layer Controllers (Web &amp; REST API)</x-docs.heading>
<p>
    Controller dipisahkan berdasarkan saluran komunikasi: controller di <code>app/Http/Controllers/</code> mengembalikan View Blade, Redirect, atau Stream CSV/cetak; sedangkan controller di <code>app/Http/Controllers/Api/</code> mengembalikan objek <code>JsonResponse</code>.
</p>

<x-docs.table :headers="['Web Controller', 'Tanggung Jawab Utama', 'API Controller Padanan']">
    <tr>
        <td><code>PageController</code> &amp; <code>DocsController</code></td>
        <td>Halaman landing, download APK (<code>APK_DOWNLOAD_URL</code> / file lokal), dan Documentation Center (<code>/docs</code>).</td>
        <td><code>SystemApiController</code> (<code>/api/</code>, <code>/api/health</code>, <code>/api/docs</code>)</td>
    </tr>
    <tr>
        <td><code>AuthController</code></td>
        <td>Login/logout web multi-role + pemulihan password berbasis OTP WhatsApp/Email/Tiket Admin.</td>
        <td><code>UniversalAuthController</code>, <code>AuthNasabahController</code>, <code>AuthPetugasController</code></td>
    </tr>
    <tr>
        <td><code>AdminController</code> &amp; <code>NasabahController</code></td>
        <td>Dashboard admin, CRUD pegawai, CRUD nasabah, import CSV/XLSX native, arsip alumni, audit log CSV, dan pengaturan sistem.</td>
        <td><code>ProfilNasabahController</code>, <code>SavingGoalApiController</code>, <code>TransaksiNasabahController</code></td>
    </tr>
    <tr>
        <td><code>TellerController</code> &amp; <code>BukuTabunganController</code></td>
        <td>Lookup nasabah/QR, proses setor/tarik ber-idempotency, pembatalan (void), riwayat, buku tabungan &amp; cetak slip.</td>
        <td><code>TellerApiController</code></td>
    </tr>
    <tr>
        <td><code>DailyReportController</code> &amp; <code>PengajuanKasController</code></td>
        <td>Pengajuan droping kas, pencatatan tutup kas harian teller, dan verifikasi supervisor.</td>
        <td><code>OperasionalKasApiController</code></td>
    </tr>
    <tr>
        <td><code>SupervisorController</code> &amp; <code>LaporanKeuanganController</code></td>
        <td>Pusat persetujuan ber-locking, monitoring teller, rekonsiliasi selisih kas, notifikasi, dan buku jurnal/laporan tahunan.</td>
        <td><code>LaporanApiController</code>, <code>NotificationApiController</code></td>
    </tr>
    <tr>
        <td><code>AbsensiAvalisController</code> &amp; <code>AvalisPetugasController</code></td>
        <td>Manajemen master petugas avalis, absensi shift Pagi/Siang, bulk import, serta verifikasi kunjungan siswa.</td>
        <td><code>AvalisApiController</code></td>
    </tr>
</x-docs.table>

<x-docs.heading id="services-dan-helpers" level="2">Services &amp; Helpers Domain</x-docs.heading>
<ul>
    <li><strong><code>DashboardWidgetService</code>:</strong> Memusatkan perhitungan metrik widget untuk Admin (statistik nasabah, pegawai, permohonan reset tanpa membocorkan token/OTP, status sistem &amp; <code>brankas</code>), Teller (aktivitas pribadi, alert antrean tutup kas/droping kas), dan Supervisor (aktivitas lintas loket, antrean approval).</li>
    <li><strong><code>ProfileImageService</code>:</strong> Menyediakan pemrosesan unggahan foto profil 6 lapis: pengecekan upload valid &amp; ukuran maksimal 2 MB, validasi ekstensi ketat (<code>jpg/jpeg/png/webp</code>), pemeriksaan MIME <code>finfo</code>, pemeriksaan dimensi via <code>getimagesize</code> (min 50x50, max 4000x4000 px), pemindaian payload PHP/script/null-byte mentah, dan pembuatan ulang kanvas baru berbasis <strong>GD</strong> (maksimal sisi 1000 px).</li>
    <li><strong><code>AuditHelper</code>:</strong> Merekam seluruh mutasi objek ke tabel <code>audit_logs</code> serta otomatis mengganti nilai field sensitif (<code>password</code>, <code>pin_keamanan</code>, <code>pin</code>, <code>otp_code</code>, <code>token</code>, <code>remember_token</code>, <code>secret</code>) menjadi <code>[REDACTED]</code>.</li>
    <li><strong><code>AvalisSyncHelper</code>:</strong> Menyelaraskan pengguna ber-role <code>teller</code>/<code>avalis</code> ke tabel <code>avalis</code> dan memetakan kehadiran otomatis ke tabel <code>absensi_avalis</code> per tanggal dengan alokasi Shift Pagi (07.30–11.30) dan Shift Siang (11.30–15.30).</li>
    <li><strong><code>SettingHelper</code> &amp; <code>TerbilangHelper</code>:</strong> Membaca pengaturan global dari <code>pengaturan_sistem</code> (default: <code>saldo_mengendap_min = 10000</code>, <code>biaya_administrasi_buku = 5000</code>, <code>tahun_ajaran_aktif = 2026/2027</code>) serta menerjemahkan nominal ke kalimat terbilang Bahasa Indonesia dan kode jurusan (RPL, TKJ, AKL, LPS, BR, BD, MP, DKV, GK).</li>
</ul>

<x-docs.heading id="console-command-scheduler" level="2">Artisan Command &amp; Task Scheduling</x-docs.heading>
<p>
    File <code>routes/console.php</code> mendaftarkan penjadwalan command <code>kas:auto-close</code> (<code>App\Console\Commands\AutoCloseKasCommand</code>) agar dijalankan setiap hari pada pukul <strong>17:00</strong>.
</p>
<x-docs.code-block lang="php" title="routes/console.php">
Schedule::command('kas:auto-close')->dailyAt('17:00');
</x-docs.code-block>
<p>
    Saat dieksekusi, command memeriksa apakah masih ada <code>PengajuanKas</code> berstatus <code>pending</code> atau <code>hold</code> pada hari berjalan (jika ada, tutup kas otomatis dilewati). Untuk laporan harian berstatus <code>pending</code> dengan <code>difference === 0</code>, statusnya disetujui otomatis oleh sistem; bagi teller yang belum mengirim <em>daily report</em> dan memiliki transaksi atau saldo brankas, sistem membentuk entri <code>DailyReport</code> terrekonsiliasi otomatis.
</p>

