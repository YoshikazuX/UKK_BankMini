<x-docs.heading id="cakupan-fungsional-web" level="2">Cakupan Uji Fungsional Aplikasi Web</x-docs.heading>
<p>
    Pengujian fungsional di repositori <code>belajar_laravel_handa</code> disusun dalam kelas-kelas Feature Test yang mengirimkan simulasi HTTP GET/POST menggunakan helper <code>$this-&gt;actingAs($user)</code>:
</p>
<x-docs.table :headers="['File Test Fungsional', 'Jumlah Skenario', 'Fokus Verifikasi di Kode']">
    <tr>
        <td><code>LandingPageTest.php</code></td>
        <td>4 method</td>
        <td>Status HTTP 200 di <code>/</code>, identitas LKM Mitra Siswa Abadi, tautan rute <code>login</code> &amp; <code>download</code>, keberadaan mockup device &amp; FAQ peran.</td>
    </tr>
    <tr>
        <td><code>DownloadPageTest.php</code></td>
        <td>3 method</td>
        <td>Status HTTP 200 di <code>/download</code>, rute navigasi, panduan instalasi APK, serta spesifikasi kompatibilitas Android 7.0+.</td>
    </tr>
    <tr>
        <td><code>DocumentationCenterTest.php</code></td>
        <td>6 method</td>
        <td>Seluruh 27 rute <code>/docs</code> &amp; <code>/docs/{section}/{page}</code> mengembalikan HTTP 200, API <code>/docs/search</code>, breadcrumbs, prev/next links, serta validasi semua anchor TOC.</td>
    </tr>
    <tr>
        <td><code>DashboardWidgetsTest.php</code></td>
        <td>8 method</td>
        <td>Keakuratan agregat widget di dashboard Admin, Teller, dan Supervisor; isolasi notifikasi tutup kas disetujui; penyembunyian token/password pada widget reset; serta pembatasan akses pegawai dari Teller.</td>
    </tr>
    <tr>
        <td><code>BankMiniFiturSempurnaTest.php</code></td>
        <td>7 method</td>
        <td>Filter &amp; pencarian nasabah, halaman detail buku/saldo, perekaman audit trail dengan penyensoran kredensial, pembatasan SoD <code>RoleGuard</code>, pengelolaan permohonan reset, absensi avalis, serta validasi profil &amp; konfigurasi.</td>
    </tr>
    <tr>
        <td><code>RevisiLengkapBankMiniTest.php</code></td>
        <td>6 method</td>
        <td>Privasi daftar nasabah di dashboard teller sampai QR/pencarian dimasukkan, batas setor Rp 5.000 &amp; mengendap Rp 10.000, logika tutup kas hanya menghitung sukses, <code>kas:auto-close</code>, lupa PIN, dan import siswa/soft delete alumni.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="uji-modul-inti-bank-mini" level="2">Uji Dashboard, RBAC, Teller &amp; Supervisor</x-docs.heading>
<p>
    Dua file test tambahan mendetailkan skenario operasional harian pada modul loket kasir dan pengawasan supervisor:
</p>
<ul>
    <li><strong><code>TellerPenyempurnaanTest.php</code> (9 method):</strong> Memastikan rekening nonaktif diblokir dari form transaksi, penarikan melebihi saldo efektif atau kas fisik brankas ditolak, pencegahan transaksi duplikat menggunakan <code>idempotency_key</code>, endpoint pengecekan status idempotensi pasca koneksi putus, commit status <code>sukses</code>, mekanisme <em>void</em> berjurnal pembalik, selisih tutup kas wajib catatan, serta filter multi-parameter riwayat.</li>
    <li><strong><code>SupervisorPenyempurnaanTest.php</code> (10 method):</strong> Menguji Pusat Persetujuan, posisi kas brankas pada halaman detail, pencatatan ke <code>approval_histories</code>, kewajiban mengisi alasan penolakan/hold, pemisahan metrik transaksi sukses vs void pada monitoring teller, perhitungan selisih otomatis di rekonsiliasi, filter gabungan laporan, pengiriman <code>SupervisorTaskNotification</code>, pencegahan <em>double approval</em> melalui locking, serta transisi status <code>hold</code> ke <code>disetujui</code>.</li>
</ul>
<p class="text-xs text-slate-500">
    *Catatan: Di <code>tests/Feature/Auth/</code> terdapat pula 6 file test bawaan Breeze (18 method) untuk rute <code>register</code>, <code>email/verification-notification</code>, dan <code>password.reset</code>; sebagian rute Breeze di <code>routes/auth.php</code> saat ini tidak diaktifkan pada <code>routes/web.php</code> utama.
</p>

