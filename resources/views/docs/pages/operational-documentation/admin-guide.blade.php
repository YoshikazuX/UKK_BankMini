<x-docs.heading id="dashboard-administrator" level="2">Dashboard Administrator</x-docs.heading>
<p>
    Administrator bertugas mengelola tata kelola akun pegawai, data master nasabah, otorisasi reset kata sandi &amp; PIN, jadwal petugas avalis, jejak audit sistem, serta konfigurasi parameter global. Saat masuk melalui <code>/admin/dashboard</code>, Admin melihat 3 kartu KPI utama (Total Nasabah, Manajemen Pegawai, Status Sistem &amp; Brankas) beserta daftar permohonan reset kata sandi tertunda dan aktivitas audit terbaru.
</p>

<x-docs.heading id="pengelolaan-nasabah-dan-rekening" level="2">Pengelolaan Nasabah, Rekening &amp; Alumni</x-docs.heading>
<p>Seluruh fungsi nasabah terpusat pada menu <strong>Manajemen Nasabah</strong> (<code>/admin/data-nasabah</code>):</p>
<ul>
    <li><strong>Pencarian &amp; Filter Status:</strong> Filter tab cepat tersedia untuk <em>Semua Status</em>, <em>Aktif</em>, <em>Nonaktif / Siswa Baru Kelas 10</em>, <em>Alumni (Lulus Kelas 12)</em>, serta filter <em>Arsip Alumni Dihapus</em> (<code>status=terhapus</code> untuk memantau data <em>soft deleted</em>).</li>
    <li><strong>Tambah &amp; Edit Nasabah:</strong> Pada <code>/admin/tambah-nasabah</code>, tersedia tombol generator nomor rekening otomatis (<code>/admin/generate-no-rekening</code>) yang membentuk nomor rekening berdasarkan jurusan dan tahun ajaran aktif. Status rekening dan kategori (<code>siswa</code> vs <code>guru_karyawan</code>) dapat diperbarui melalui menu edit atau tombol ubah status.</li>
    <li><strong>Import Massal CSV / XLSX:</strong> Gunakan fitur import pada halaman Manajemen Nasabah. File <code>.csv</code> maupun <code>.xlsx</code> dibaca langsung oleh <code>NasabahController::parseXlsxNative()</code>. Nasabah baru tingkat X akan otomatis berstatus <code>nonaktif</code> sampai diaktifkan.</li>
    <li><strong>Hapus Lunak &amp; Pemulihan (Restore):</strong> Admin hanya dapat menghapus data nasabah yang sudah berstatus <code>nonaktif</code> atau <code>alumni</code>. Nasabah yang dihapus dipindahkan ke arsip <em>Soft Delete</em> sehingga riwayat transaksinya di database tidak hilang, dan dapat dipulihkan lewat rute <code>/admin/nasabah/restore/{id}</code>.</li>
</ul>

<x-docs.heading id="pengelolaan-pegawai-admin" level="2">Pengelolaan Pegawai &amp; Pengaturan Shift Avalis</x-docs.heading>
<ul>
    <li><strong>Manajemen Pegawai (<code>/admin/data-pegawai</code>):</strong> Admin dapat menambah, mengubah profil/status (<code>aktif</code>/<code>nonaktif</code>), serta menghapus akun pegawai. Pada formulir UI <code>AdminController</code>, pilihan peran yang disediakan adalah <code>admin</code>, <code>teller</code>, dan <code>supervisor</code>. Admin tidak dapat menghapus akun miliknya sendiri yang sedang aktif login.</li>
    <li><strong>Absensi &amp; Master Petugas Avalis (<code>/admin/absensi-avalis</code>):</strong> Admin memiliki kontrol CRUD penuh terhadap jadwal dan presensi petugas avalis (siswa pendamping loket), termasuk sinkronisasi otomatis dari tabel pegawai teller (<code>/absensi-avalis/sync-from-petugas</code>), ubah shift (<em>Shift Pagi</em> / <em>Shift Siang</em>), ubah status kehadiran, tambah massal petugas (<code>storeBulk</code>), dan ekspor CSV absensi (<code>/absensi-avalis/export</code>).</li>
</ul>

<x-docs.heading id="persetujuan-reset-sandi-pin" level="2">Persetujuan Reset Password &amp; Reset PIN</x-docs.heading>
<p>Di menu <strong>Permohonan Reset</strong> (<code>/admin/reset-password-requests</code>), Admin mengelola dua jenis antrean pemulihan:</p>
<x-docs.steps>
    <x-docs.step number="1" title="Permohonan Reset Password Pegawai">
        <p>Admin dapat menyetujui tiket reset password (dengan memasukkan password sementara baru) atau menolak permohonan tersebut sesuai verifikasi identitas pemohon.</p>
    </x-docs.step>
    <x-docs.step number="2" title="Permohonan Reset PIN Keamanan Nasabah">
        <p>Untuk menjaga kerahasiaan PIN nasabah, Admin <strong>tidak memasukkan PIN baru</strong> siswa. Ketika Admin menekan <strong>Setujui</strong> pada pengajuan <code>pin_reset_requests</code>, sistem menerbitkan <code>reset_token</code> unik 64 karakter dan mengubah status tiket menjadi <code>approved</code> agar nasabah dapat menyetel PIN 6 digitnya sendiri melalui aplikasi mobile atau portal web.</p>
    </x-docs.step>
</x-docs.steps>

<x-docs.heading id="konfigurasi-audit-jurnal" level="2">Konfigurasi Sistem, Jejak Audit &amp; Buku Jurnal</x-docs.heading>
<ul>
    <li><strong>Konfigurasi Sistem Global (<code>/admin/konfigurasi</code>):</strong> Mengatur nilai <code>saldo_mengendap_min</code> (bawaan Rp 10.000), aktivasi pemindai kamera QR (<code>scanner_kamera_aktif</code>), <code>tahun_ajaran_aktif</code> (mis. <code>2026/2027</code>), <code>format_no_rekening</code>, <code>nama_bank</code>, dan <code>biaya_administrasi_buku</code>.</li>
    <li><strong>Jejak Audit Sistem (<code>/admin/audit</code>):</strong> Memantau seluruh aktivitas sistem beserta perubahan nilai lama dan baru (data sensitif seperti password/PIN otomatis bertuliskan <code>[REDACTED]</code>). Tersedia ekspor log ke format CSV lewat <code>/admin/audit/export</code>.</li>
    <li><strong>Buku Jurnal &amp; Laporan Keuangan (<code>/laporan/jurnal</code>, <code>/laporan/saldo-produk</code>, <code>/laporan/tahunan</code>):</strong> Admin dapat membaca serta mencetak laporan jurnal akuntansi, komposisi saldo per jurusan, dan rekapitulasi tahunan.</li>
</ul>

<x-docs.heading id="batasan-wewenang-admin" level="2">Batasan Wewenang Administrator (SoD)</x-docs.heading>
<x-docs.callout type="danger" title="Akses yang Ditolak Keras untuk Admin">
    Sesuai aturan <em>Separation of Duties</em> pada <code>RoleGuard</code>, Administrator <strong>dilarang mengakses halaman maupun eksekusi transaksi kasir loket Teller</strong> (seluruh rute <code>/teller/transaksi*</code> akan langsung mengembalikan error <code>403 Akses Ditolak</code>). Menu Teller POS juga tidak ditampilkan pada navigasi Administrator.
</x-docs.callout>

