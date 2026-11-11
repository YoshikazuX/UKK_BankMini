<x-docs.heading id="persyaratan-akses-sistem" level="2">Persyaratan Sistem &amp; Perangkat</x-docs.heading>
<p>
    Pengoperasian HANBANK dapat dilakukan melalui browser desktop/laptop di loket Bank Mini maupun perangkat Android siswa dan petugas:
</p>
<x-docs.table :headers="['Platform', 'Kebutuhan Minimal Aktual', 'Catatan Operasional']">
    <tr>
        <td class="font-semibold text-slate-900">Web Browser (Loket &amp; Kantor)</td>
        <td>Google Chrome, Microsoft Edge, atau Firefox versi modern dengan JavaScript aktif.</td>
        <td>Untuk fitur <strong>Scan Cepat QR</strong> pada loket Teller (<code>/teller/dashboard</code> &amp; <code>/teller/scan</code>), browser memerlukan akses kamera serta protokol HTTPS (atau <code>localhost</code> saat pengembangan).</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Mobile Android (<code>ukk_bankmini</code>)</td>
        <td>Android 7.0 (Nougat) ke atas (sesuai spesifikasi halaman <code>/download</code>), penyimpanan kosong &gt; 80 MB untuk APK (~73 MB).</td>
        <td>Aplikasi membutuhkan koneksi internet/LAN yang dapat menjangkau endpoint server Laravel (<code>https://hanbank.my.id/api</code> atau IP lokal saat mode debug).</td>
    </tr>
</x-docs.table>

<x-docs.heading id="akses-website-dan-mobile" level="2">Akses Website &amp; Aplikasi Mobile</x-docs.heading>
<ul>
    <li><strong>Halaman Publik Website:</strong> Pengunjung umum dapat membuka Beranda (<code>/</code>), halaman pengunduhan aplikasi Android (<code>/download</code>), dan Pusat Dokumentasi (<code>/docs</code>) tanpa login.</li>
    <li><strong>Portal Masuk Web (<code>/login</code>):</strong> Digunakan oleh Administrator, Teller, Supervisor/Accounting, serta akun pengguna ber-role <code>nasabah</code> yang terdaftar di tabel <code>users</code>.</li>
    <li><strong>Aplikasi Mobile Android:</strong> Menggunakan layar masuk universal (<code>POST /api/auth/login</code>) yang otomatis mengenali akun Nasabah maupun Petugas. Jika baru pertama kali dipasang pada lingkungan pengujian lokal, pengguna dapat mengatur Base URL server melalui layar pengaturan koneksi.</li>
</ul>

<x-docs.heading id="prosedur-login-logout" level="2">Prosedur Login, Logout &amp; Pemulihan Sandi</x-docs.heading>
<x-docs.steps>
    <x-docs.step number="1" title="Masuk ke Akun (Web &amp; Mobile)">
        <p>Masukkan <strong>Username</strong> dan <strong>Password</strong> Anda. Setelah kredensial terverifikasi, web akan mengarahkan pengguna secara otomatis sesuai perannya (<code>/admin/dashboard</code>, <code>/teller/dashboard</code>, <code>/supervisor/dashboard</code>, atau <code>/nasabah/dashboard</code>).</p>
    </x-docs.step>
    <x-docs.step number="2" title="Lupa Kata Sandi Petugas (Web)">
        <p>Klik tautan pemulihan sandi menuju <code>/forgot-password</code>. Pilih metode verifikasi (WhatsApp, Email, atau Tiket Admin), ajukan kode OTP, lalu verifikasi OTP untuk mengganti kata sandi atau tunggu penanganan Administrator melalui menu Permohonan Reset.</p>
    </x-docs.step>
    <x-docs.step number="3" title="Keluar (Logout) dengan Aman">
        <p>Di web, klik foto/nama profil di pojok kanan atas bilah navigasi, lalu klik <strong>Keluar</strong> (mengirim <code>POST /logout</code> dan menghapus sesi database). Di mobile, menu Logout memanggil <code>POST /api/auth/logout</code> guna mencabut token Sanctum dari tabel <code>personal_access_tokens</code>.</p>
    </x-docs.step>
</x-docs.steps>

<x-docs.callout type="warning" title="Perhatian Akun Nonaktif">
    Apabila akun pegawai berstatus <code>nonaktif</code>, sistem akan menolak autentikasi baik saat login web maupun API mobile. Hubungi Administrator apabila status kepegawaian perlu diaktifkan kembali.
</x-docs.callout>

<x-docs.heading id="navigasi-dan-profil" level="2">Navigasi Dashboard &amp; Pengelolaan Profil</x-docs.heading>
<p>
    Setelah masuk di aplikasi web, bilah navigasi atas (<code>&lt;x-navbar /&gt;</code>) menyesuaikan menu secara otomatis mengikuti peran pengguna yang aktif:
</p>
<ul>
    <li><strong>Ikon Lonceng Notifikasi (Khusus Teller, Supervisor, Accounting):</strong> Menampilkan hingga 5 notifikasi terbaru yang belum dibaca beserta lencana hitungan merah/amber. Klik item notifikasi untuk langsung membuka tautan pekerjaan sekaligus menandainya telah dibaca.</li>
    <li><strong>Menu Profil Saya:</strong> Dapat diakses oleh Admin (<code>/admin/profil</code>), Teller (<code>/teller/profil</code>), dan Supervisor (<code>/supervisor/profil</code>) untuk memperbarui nama petugas, kontak, kata sandi, dan foto profil.</li>
    <li><strong>Aturan Unggah Foto Profil:</strong> File gambar divalidasi oleh <code>ProfileImageService</code> maksimal <strong>2 MB</strong> berformat <code>JPG</code>, <code>JPEG</code>, <code>PNG</code>, atau <code>WEBP</code> dengan dimensi 50x50 hingga 4000x4000 piksel dan akan disanitasi ulang ke kanvas baru sebelum disimpan.</li>
</ul>

