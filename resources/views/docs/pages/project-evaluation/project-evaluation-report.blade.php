<x-docs.callout type="info" title="Legenda Status Evaluasi Resmi">
    Evaluasi di halaman ini memisahkan secara tegas antara fitur yang selesai ditulis di kode namun belum dieksekusi test-nya pada lingkungan audit lokal dengan komponen yang telah lolos uji terverifikasi:
    <div class="flex flex-wrap gap-2 mt-2">
        <x-docs.status-badge status="Tested and Verified" />
        <x-docs.status-badge status="Implemented" />
        <x-docs.status-badge status="Partially Implemented" />
        <x-docs.status-badge status="Not Yet Tested" />
        <x-docs.status-badge status="Planned" />
    </div>
</x-docs.callout>

<x-docs.heading id="project-summary" level="2">1. Project Summary</x-docs.heading>
<p>
    Proyek <strong>HANBANK (LKM Mitra Siswa Abadi — SMK Negeri 1 Bantul)</strong> berhasil mengintegrasikan proses inti operasional Bank Mini Sekolah ke dalam platform web <strong>Laravel 13.24.0</strong> dan aplikasi klien Android <strong>Flutter 3.41.9 (v2.4.0+1)</strong>. Ekosistem ini menghubungkan fungsi pelayanan loket Teller (setoran, penarikan, scan QR, pengajuan kas, tutup kas), tata kelola Administrator (master nasabah, import CSV/XLSX, alumni soft-delete, pegawai, reset PIN/password, audit trail, konfigurasi global), pengawasan Supervisor (approval droping kas/daily report ber-locking, rekonsiliasi, laporan akuntansi), serta layanan perbankan digital siswa (QR Card, mutasi, saving goals, dynamic badges, emergency freeze).
</p>

<x-docs.heading id="implementation-achievement" level="2">2. Implementation Achievement</x-docs.heading>
<p>
    Evaluasi ketercapaian terhadap kebutuhan sistem berdasarkan pemeriksaan langsung seluruh controller, model, migrasi, view Blade, dan modul Flutter:
</p>
<x-docs.table :headers="['Modul / Kebutuhan', 'Status Implementasi', 'Status Pengujian Saat Audit', 'Bukti Kode Aktual']">
    <tr>
        <td class="font-semibold text-slate-900">Portal Publik (<code>/</code>, <code>/download</code>)</td>
        <td><x-docs.status-badge status="Implemented" /></td>
        <td><x-docs.status-badge status="Not Yet Tested" /> (PHPUnit lokal <code>--no-dev</code>)</td>
        <td><code>PageController</code>, <code>landing.blade.php</code>, <code>download.blade.php</code>, <code>LandingPageTest</code>, <code>DownloadPageTest</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Documentation Center (<code>/docs*</code>)</td>
        <td><x-docs.status-badge status="Implemented" /></td>
        <td><x-docs.status-badge status="Tested and Verified" /> (HTTP kernel &amp; Vite build)</td>
        <td><code>DocsController</code>, <code>config/hanbank_docs.php</code>, <code>layouts/docs.blade.php</code>, 27 halaman &amp; <code>DocumentationCenterTest</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Transaksi Kasir Teller, Brankas, Idempotency &amp; Void</td>
        <td><x-docs.status-badge status="Implemented" /></td>
        <td><x-docs.status-badge status="Not Yet Tested" /> (PHPUnit lokal); <x-docs.status-badge status="Tested and Verified" /> (Mobile POS mock)</td>
        <td><code>TellerController</code>, <code>TellerApiController</code>, <code>BukuTabunganController</code>, <code>Brankas</code>, <code>Jurnal</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Approval Supervisor, Rekonsiliasi &amp; Daily Report</td>
        <td><x-docs.status-badge status="Partially Implemented" /></td>
        <td><x-docs.status-badge status="Not Yet Tested" /> (PHPUnit lokal); <x-docs.status-badge status="Tested and Verified" /> (Mobile mock)</td>
        <td><code>SupervisorController</code>, <code>PengajuanKasController</code>, <code>DailyReportController</code> (ditemukan mismatch properti di <code>SupervisorController::index</code> baris 187–197).</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Manajemen Nasabah, Import XLSX Native, Pegawai, Shift Avalis &amp; Audit Log</td>
        <td><x-docs.status-badge status="Implemented" /></td>
        <td><x-docs.status-badge status="Not Yet Tested" /> (PHPUnit lokal)</td>
        <td><code>AdminController</code>, <code>NasabahController::parseXlsxNative</code>, <code>AvalisSyncHelper</code>, <code>AuditHelper</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Mobile Banking Siswa (QR, Goals, Badges, Freeze, Sessions, Mutasi)</td>
        <td><x-docs.status-badge status="Implemented" /></td>
        <td><x-docs.status-badge status="Tested and Verified" /> (Mock API Flutter 40/40 lulus)</td>
        <td><code>ukk_bankmini/lib/screens/*</code>, <code>ProfilNasabahController</code>, <code>SavingGoalApiController</code>, <code>TransaksiNasabahController</code>.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Keamanan Middleware REST API (RBAC API &amp; Sanctum Route Guard Penuh)</td>
        <td><x-docs.status-badge status="Partially Implemented" /></td>
        <td><x-docs.status-badge status="Not Yet Tested" /></td>
        <td><code>routes/api.php</code>: baru sebagian grup memakai <code>auth:sanctum</code>; belum ada <code>RoleGuard</code> di rute API.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="functional-evaluation" level="2">3. Functional Evaluation</x-docs.heading>
<ul>
    <li><strong>Kesesuaian Inti Perbankan:</strong> Alur pembukuan kas sudah solid secara arsitektural. Setoran dan penarikan dijalankan di dalam transaksi database disertai <code>lockForUpdate()</code>, pembaruan posisi fisik <code>brankas</code>, pencatatan <code>jurnal</code>, perlindungan saldo mengendap Rp 10.000, pembatalan <em>void</em> dengan jurnal pembalik (tanpa menghapus riwayat), serta pencegahan pengiriman ganda memakai <code>idempotency_key</code>.</li>
    <li><strong>Sinkronisasi Operasional Harian:</strong> Mekanisme pengajuan kas berformat <code>PK-YYYYMMDD-XXX</code>, verifikasi kunjungan siswa unik per tanggal, sinkronisasi otomatis petugas teller ke <code>avalis</code> dan shift jaga, serta penjadwalan <code>kas:auto-close</code> pukul 17:00 menjawab kebutuhan operasional bank mini sekolah secara nyata.</li>
    <li><strong>Ketidakkonsistenan Minor yang Ditemukan:</strong> Aturan minimal setoran di loket web adalah <code>Rp 5.000</code>, namun di REST API masih tercatat <code>Rp 1.000</code> (<code>TellerApiController::setor</code>). Selain itu, tampilan widget riwayat keputusan pada <code>SupervisorController::index</code> merujuk atribut <code>$kep-&gt;user</code> dan <code>$kep-&gt;status</code> alih-alih nama kolom aktual <code>ApprovalHistory</code> (<code>supervisor</code>/<code>nama_supervisor</code>, <code>jenis_keputusan</code>, <code>status_sesudah</code>).</li>
</ul>

<x-docs.heading id="ui-ux-evaluation" level="2">4. UI/UX Evaluation</x-docs.heading>
<ul>
    <li><strong>Konsistensi Visual Web &amp; Mobile:</strong> Seluruh antarmuka memakai identitas warna yang seragam: latar terang bersih (<code>#F8FAFC</code>), permukaan putih ber-hairline border (<code>#E2E8F0</code>), warna institusional Navy/Slate (<code>#0F172A</code> / <code>#101B2D</code>), aksen Emerald/Teal (<code>#10B981</code> / <code>#19C9A3</code>), font <code>Plus Jakarta Sans</code>, dan <code>tabular-nums</code> untuk seluruh angka nominal rupiah.</li>
    <li><strong>Kegunaan Operasional:</strong> Dashboard loket teller melindungi privasi nasabah dengan tidak menampilkan seluruh daftar siswa sebelum discan/dicari; halaman slip/buku tabungan memiliki layout cetak (<code>no-print</code>) dengan angka terbilang otomatis; navbar web menyediakan lencana hitungan notifikasi serta drawer responsif mobile.</li>
    <li><strong>Catatan Teknis Frontend:</strong> Masih adanya pemuatan ganda antara Vite bundle (<code>@@vite</code>) pada landing/docs dengan <code>cdn.tailwindcss.com</code> pada sebagian besar view dashboard internal membuat tampilan sangat bergantung pada koneksi internet eksternal apabila pengguna membuka dashboard loket secara luring/LAN murni.</li>
</ul>

<x-docs.heading id="integration-evaluation" level="2">5. Integration Evaluation</x-docs.heading>
<ul>
    <li><strong>Kesesuaian Kontrak Flutter &harr; Laravel:</strong> Peninjauan terhadap <code>ApiConstants</code> dan 4 class service Flutter (<code>AuthService</code>, <code>NasabahApiService</code>, <code>TellerApiService</code>, <code>SupervisorApiService</code>) menunjukkan seluruh path endpoint utama selaras dengan definisi di <code>routes/api.php</code> (misalnya <code>/api/auth/login</code>, <code>/api/nasabah/emergency-freeze</code>, <code>/api/nasabah/badges</code>, <code>/api/notifikasi</code>, <code>/api/petugas/transaksi/setor</code>, <code>/api/supervisor/pengajuan-kas</code>).</li>
    <li><strong>Status Integrasi Teruji vs Terpasang:</strong> Kontrak JSON dan state aplikasi mobile telah lolos 40/40 test case pada <code>flutter test --no-pub</code> memakai <code>TestApiMockInterceptor</code>. Namun, pengujian integrasi langsung (*live end-to-end*) tanpa mock dari APK Android ke server MySQL backend masih berstatus <x-docs.status-badge status="Not Yet Tested" />.</li>
</ul>

<x-docs.heading id="security-evaluation" level="2">6. Security Evaluation</x-docs.heading>
<p>Kekuatan pengamanan yang sudah terimplementasi pada kode aktual:</p>
<ul>
    <li><strong>Web RBAC &amp; Separation of Duties:</strong> <code>RoleGuard</code> memblokir Administrator dan Supervisor dari seluruh rute transaksi kasir loket (<code>teller/transaksi*</code>) serta memblokir Teller dari fungsi Supervisor/Accounting.</li>
    <li><strong>Sanitasi Media &amp; Audit Trail:</strong> <code>ProfileImageService</code> melakukan validasi berlapis plus re-encoding piksel GD (melumpuhkan skrip berbahaya dalam metadata file gambar). <code>AuditHelper</code> menyensor key kredensial dengan <code>[REDACTED]</code> dan kolom <code>otp_code</code> sudah diperlebar menjadi 255 karakter agar mendukung penyimpanan hash OTP.</li>
    <li><strong>Reset PIN Berbasis Token Mandiri:</strong> Admin hanya menyetujui permintaan reset PIN (menghasilkan <code>reset_token</code>), sedangkan penetapan PIN 6 digit tetap dilakukan sendiri oleh nasabah.</li>
</ul>
<p>Celah pengamanan dan kelemahan yang ditemukan selama audit:</p>
<ul>
    <li><strong>Middleware API Belum Menyeluruh:</strong> Pada <code>routes/api.php</code>, sebagian besar endpoint <code>/api/nasabah/*</code> (selain <code>logout</code>) tidak memasang <code>auth:sanctum</code> sehingga helper <code>resolveNasabah()</code> masih menerima parameter <code>no_rekening</code>/<code>id_nasabah</code> tanpa Bearer token. Grup <code>/api/avalis/*</code> dan <code>/api/laporan/*</code> terbuka tanpa autentikasi route, serta <code>/api/petugas/*</code> dan <code>/api/supervisor/*</code> belum memvalidasi pemisahan role di level middleware.</li>
    <li><strong>Fallback PIN Default Hardcoded:</strong> Controller transaksi penarikan dan <em>void</em> masih menyertakan pengecekan PIN fallback statis <code>'123456'</code> di samping verifikasi <code>Hash::check</code>, yang melemahkan otorisasi apabila tidak dihapus pada lingkungan produksi.</li>
    <li><strong>Token Sanctum Tanpa Masa Berlaku:</strong> Konfigurasi <code>config/sanctum.php</code> menyetel <code>'expiration' =&gt; null</code>.</li>
</ul>

<x-docs.heading id="performance-evaluation" level="2">7. Performance Evaluation</x-docs.heading>
<ul>
    <li><strong>Keunggulan Desain:</strong> Agregasi widget dashboard sudah dikonsolidasikan di <code>DashboardWidgetService</code>, query mutasi memakai paginasi/pembatasan, pencatatan transaksi memakai penguncian baris selektif (<code>lockForUpdate</code>) alih-alih penguncian seluruh tabel, dan pipeline CI menjalankan <code>php artisan route:cache</code> serta optimasi autoloader.</li>
    <li><strong>Keterbatasan Pengukuran:</strong> Belum terdapat laporan hasil *benchmark*, *query profiling*, ataupun *load testing* (mis. JMeter/k6/ApacheBench) di repositori, sehingga aspek throughput konkurensi dan waktu respons di beban puncak berstatus <x-docs.status-badge status="Not Yet Tested" />.</li>
</ul>

<x-docs.heading id="project-limitations" level="2">8. Project Limitations</x-docs.heading>
<ol>
    <li><strong>Lingkungan PHPUnit Lokal Tidak Terpasang:</strong> Direktori <code>vendor/</code> lokal terpasang dengan <code>--no-dev</code> sehingga suite pengujian Laravel (19 berkas test + test baru Documentation Center) belum dapat dieksekusi di mesin lokal tanpa menjalankan <code>composer install</code> penuh.</li>
    <li><strong>Keamanan Route REST API Belum Seragam:</strong> Celah autentikasi/otorisasi pada beberapa grup di <code>routes/api.php</code> (<code>nasabah</code>, <code>avalis</code>, <code>laporan</code>, serta absennya cek role di <code>petugas</code> &amp; <code>supervisor</code>).</li>
    <li><strong>Ketidaksesuaian Kecil &amp; Sisa Scaffolding:</strong>
        <ul>
            <li>Mismatch atribut relasi pada <code>SupervisorController::index</code> (baris 187–197) terhadap skema aktual <code>ApprovalHistory</code>.</li>
            <li>Perbedaan nominal minimal setoran (Rp 5.000 di web vs Rp 1.000 di API) dan masih adanya fallback PIN <code>'123456'</code>.</li>
            <li><code>composer.json</code> masih mencantumkan <code>phpoffice/phpspreadsheet</code> dan terdapat tabel migrasi lama (<code>jurnal_akuntansi</code>) serta model tak bermigrasi (<code>UserApp</code>) dan file rute Breeze tidak terpakai (<code>routes/auth.php</code>).</li>
            <li>Dashboard web masih mengandalkan Tailwind CDN eksternal pada sebagian halaman.</li>
        </ul>
    </li>
</ol>

<x-docs.heading id="recommendations" level="2">9. Recommendations</x-docs.heading>
<ol>
    <li><strong>Prioritas Keamanan Kritis:</strong> Wajibkan middleware <code>auth:sanctum</code> pada seluruh endpoint privat <code>/api/nasabah/*</code> (hapus resolusi berbasis query <code>no_rekening</code> tanpa token), kunci grup <code>/api/avalis/*</code> &amp; <code>/api/laporan/*</code>, tambahkan verifikasi role (SoD) pada API <code>petugas</code> &amp; <code>supervisor</code>, hapus fallback PIN statis <code>'123456'</code>, dan tetapkan masa kedaluwarsa token Sanctum yang wajar.</li>
    <li><strong>Perbaikan Bug &amp; Konsistensi Kode:</strong> Perbaiki pembacaan properti <code>ApprovalHistory</code> di <code>SupervisorController::index</code> menggunakan <code>nama_supervisor</code> / <code>jenis_keputusan</code> / <code>status_sesudah</code>, samakan aturan minimal setoran antara Web dan API melalui konfigurasi terpusat, dan pastikan <code>AdminController</code> mengizinkan pembuatan role <code>accounting</code> bila diperlukan operasional.</li>
    <li><strong>Pembersihan Dependensi &amp; Eksekusi Penuh Test:</strong> Jalankan penuh seluruh suite <code>php artisan test</code> di lingkungan dev/CI (bersama <code>NasabahApiTest</code> serta test baru untuk endpoint <code>/api/petugas/*</code> dan <code>/api/supervisor/*</code>), bersihkan dependensi <code>phpoffice/phpspreadsheet</code> atau migrasi lama yang tidak terpakai, serta alihkan seluruh view dashboard agar memakai kompilasi Vite lokal.</li>
</ol>

<x-docs.heading id="future-development" level="2">10. Future Development</x-docs.heading>
<x-docs.table :headers="['Inisiatif Pengembangan Lanjutan', 'Status Roadmap', 'Deskripsi Teknis Singkat']">
    <tr>
        <td class="font-semibold text-slate-900">Hardening Penuh REST API &amp; Sanctum Abilities</td>
        <td><x-docs.status-badge status="Planned" /></td>
        <td>Penegakan token ability spesifik peran (<code>['role:teller']</code>, <code>['role:supervisor']</code>, <code>['role:nasabah']</code>) + rate limiting login/OTP.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Feature Test API Petugas, Supervisor &amp; CI Test Gate</td>
        <td><x-docs.status-badge status="Planned" /></td>
        <td>Menambahkan tahap <code>php artisan test</code> dan <code>flutter test</code> otomatis di GitHub Actions sebelum deployment FTPS.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Ekspansi Manajemen Web-Parity untuk Supervisor/Admin Mobile</td>
        <td><x-docs.status-badge status="Planned" /></td>
        <td>Menambahkan fitur manajemen nasabah/reset PIN Admin di aplikasi Flutter (saat ini masih fokus pada health &amp; absensi).</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Produk Layanan Tambahan (Angsuran &amp; Deposito Siswa)</td>
        <td><x-docs.status-badge status="Planned" /></td>
        <td>Memanfaatkan kolom <code>transaksi.jenis_layanan</code> yang sudah disiapkan pada skema tabel saat ini.</td>
    </tr>
</x-docs.table>

