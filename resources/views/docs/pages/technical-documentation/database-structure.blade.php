<x-docs.heading id="ringkasan-skema-database" level="2">Ringkasan Skema &amp; Migrasi</x-docs.heading>
<p>
    Basis data HANBANK dibangun secara inkremental melalui <strong>29 file migrasi</strong> pada folder <code>database/migrations/</code> (rentang timestamp sistem <code>0001_01_01_*</code> hingga <code>2026_11_04_224405</code>). Selain tabel inti sistem Laravel (<code>sessions</code>, <code>cache</code>, <code>jobs</code>, <code>notifications</code>, <code>personal_access_tokens</code>), aplikasi mengelola tabel domain perbankan mini, operasional shift, serta audit trail.
</p>

<x-docs.heading id="tabel-identitas-akses" level="2">Tabel Identitas, Nasabah &amp; Keamanan</x-docs.heading>
<x-docs.table :headers="['Tabel', 'Kolom Utama &amp; Tipe Data', 'Indeks / Constraint / Catatan']">
    <tr>
        <td class="font-mono font-bold text-slate-900">users</td>
        <td><code>id</code>, <code>username</code> (50), <code>nama_petugas</code> (100), <code>password</code>, <code>role</code> (string 50, default <code>teller</code>), <code>status</code> (enum <code>aktif/nonaktif</code>), <code>kelas</code>, <code>no_hp</code>, <code>email</code>, <code>foto</code>, <code>remember_token</code>, timestamps.</td>
        <td><code>username</code> UNIQUE. Awalnya enum 4 nilai lalu diubah via migrasi <code>2026_10_31_113459</code> menjadi <code>string(50)</code> untuk mendukung <code>accounting</code>, <code>cs</code>, dan <code>avalis</code>.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">nasabah</td>
        <td><code>id</code>, <code>no_rekening</code> (20), <code>nis</code> (20), <code>nama_siswa</code> (100), <code>kelas</code> (50), <code>jurusan</code> (100), <code>username</code> (50), <code>email</code> (150), <code>no_hp</code> (25), <code>password</code>, <code>pin_keamanan</code>, <code>saldo</code> (bigInteger default 0), <code>status</code> (string 20 default <code>aktif</code>), <code>kategori</code> (string 30 default <code>siswa</code>), <code>deleted_at</code>, timestamps.</td>
        <td><code>no_rekening</code>, <code>nis</code>, <code>username</code> masing-masing UNIQUE. Mendukung <code>SoftDeletes</code> (arsip alumni) sejak migrasi <code>2026_11_04_173824</code>. Model menggunakan trait <code>HasApiTokens</code> Sanctum.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">password_reset_requests</td>
        <td><code>id</code>, <code>user_id</code>, <code>username</code>, <code>nama_pemohon</code>, <code>metode</code> (<code>whatsapp/email/ticket</code>), <code>kontak</code>, <code>otp_code</code> (string 255), <code>otp_expires_at</code>, <code>status</code> (enum <code>pending/verified/completed/rejected</code>), <code>handled_by</code>, <code>handled_at</code>, <code>keterangan</code>, <code>ip_address</code>.</td>
        <td>Migrasi <code>2026_11_04_224405</code> memperlebar <code>otp_code</code> dari 10 karakter menjadi 255 karakter guna menyimpan hash bcrypt/SHA kode OTP tanpa error pemotongan string.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">pin_reset_requests</td>
        <td><code>id</code>, <code>nasabah_id</code> (FK cascade), <code>no_rekening</code>, <code>nis</code>, <code>nama_siswa</code>, <code>alasan</code>, <code>status</code> (<code>pending/approved/completed/rejected</code>), <code>approved_by</code> (FK <code>users</code> set null), <code>reset_token</code> (64, UNIQUE), <code>approved_at</code>, <code>completed_at</code>.</td>
        <td>Mendukung alur persetujuan admin terlebih dahulu sebelum siswa membuat PIN 6 digit mandiri melalui token reset.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="tabel-inti-perbankan" level="2">Tabel Transaksi, Jurnal &amp; Brankas</x-docs.heading>
<x-docs.table :headers="['Tabel', 'Kolom Utama &amp; Tipe Data', 'Fungsi &amp; Constraint']">
    <tr>
        <td class="font-mono font-bold text-slate-900">transaksi</td>
        <td><code>id</code>, <code>nasabah_id</code> (FK cascade), <code>petugas_id</code> (FK <code>users</code> set null), <code>daily_report_id</code>, <code>jenis_transaksi</code>, <code>kode_sandi</code> (<code>101, 201, 102, 202, 301</code>), <code>jenis_layanan</code> (default <code>Tabungan</code>), <code>nominal</code> (bigInteger), <code>nama_petugas</code>, <code>status</code> (<code>pending/sukses/batal/gagal</code>), <code>alasan_batal</code>, <code>idempotency_key</code> (64, UNIQUE), <code>ref_transaksi_id</code> (self-FK), <code>dibatalkan_oleh</code> (FK <code>users</code>), <code>dibatalkan_at</code>.</td>
        <td>Tabel ledger utama transaksi nasabah. Memiliki <code>idempotency_key</code> UNIQUE untuk mencegah transaksi ganda dan self-reference <code>ref_transaksi_id</code> untuk mencatat mutasi pembalik (<em>reversal/void</em>).</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">jurnal</td>
        <td><code>id</code>, <code>transaksi_id</code> (nullable, FK <code>transaksi</code> cascade), <code>keterangan</code>, <code>debit</code> (bigInteger), <code>kredit</code> (bigInteger), <code>tanggal</code> (date), timestamps.</td>
        <td>Buku jurnal harian aktif. Kolom <code>transaksi_id</code> diubah menjadi nullable pada migrasi <code>2026_10_25_500001</code> agar droping kas non-nasabah juga dapat masuk ke jurnal.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">brankas</td>
        <td><code>id</code>, <code>tanggal</code> (date UNIQUE), <code>saldo_awal</code>, <code>masuk</code>, <code>keluar</code>, <code>total</code> (seluruhnya bigInteger), timestamps.</td>
        <td>Mencatat likuiditas fisik kas harian loket bank mini. Helper model <code>Brankas::getOrCreateToday()</code> meneruskan <code>total</code> dari hari sebelumnya menjadi <code>saldo_awal</code> hari berjalan.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="tabel-operasional-pengawasan" level="2">Tabel Operasional Kas, Avalis &amp; Persetujuan</x-docs.heading>
<x-docs.table :headers="['Tabel', 'Kolom Utama &amp; Tipe Data', 'Fungsi &amp; Constraint']">
    <tr>
        <td class="font-mono font-bold text-slate-900">pengajuan_kas</td>
        <td><code>id</code>, <code>nomor_pengajuan</code> (50 UNIQUE), <code>user_id</code> (FK <code>users</code> cascade), <code>tanggal</code>, <code>nominal</code>, <code>alasan</code>, <code>status</code> (string 20 default <code>pending</code>; mendukung <code>hold</code>, <code>disetujui</code>, <code>ditolak</code>), <code>disetujui_oleh</code> (FK set null), <code>catatan_spv</code>, <code>alasan_penolakan</code>.</td>
        <td>Tiket permohonan tambahan kas (droping kas) oleh Teller. Format kode otomatis <code>PK-YYYYMMDD-XXX</code> dibangkitkan saat event <code>creating</code> model.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">daily_reports</td>
        <td><code>id</code>, <code>user_id</code> (FK cascade), <code>report_date</code>, <code>opening_balance</code>, <code>total_deposit</code>, <code>total_cash_drop</code>, <code>total_withdrawal</code>, <code>system_balance</code>, <code>physical_balance</code>, <code>difference</code>, <code>status</code> (<code>pending/approved/hold/rejected</code>), <code>approved_by</code> (FK set null), <code>notes</code>, <code>catatan_spv</code>, <code>alasan_penolakan</code>.</td>
        <td>Rekonsiliasi tutup kas harian per petugas teller. Menyimpan selisih antara kas sistem dengan kas fisik yang dihitung teller.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">approval_histories</td>
        <td><code>id</code>, <code>pekerjaan_type</code>, <code>pekerjaan_id</code>, <code>nomor_referensi</code>, <code>teller_id</code> (FK set null), <code>nama_teller</code>, <code>supervisor_id</code> (FK set null), <code>nama_supervisor</code>, <code>jenis_keputusan</code>, <code>status_sebelum</code>, <code>status_sesudah</code>, <code>nominal</code>, <code>catatan</code>, <code>alasan_penolakan</code>, <code>ip_address</code>, <code>user_agent</code>.</td>
        <td>Jejak historis setiap tindakan otorisasi (setuju, tolak, klarifikasi/hold) yang diambil supervisor terhadap pekerjaan teller.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">avalis &amp; absensi_avalis</td>
        <td><strong>avalis:</strong> <code>nama</code>, <code>kelas</code>, <code>no_wa</code>, <code>user_id</code> (FK nullable), <code>status</code>.<br><strong>absensi_avalis:</strong> <code>tanggal</code>, <code>nama</code>, <code>kelas</code>, <code>no_wa</code>, <code>shift_jaga</code>, <code>status</code>, <code>keterangan</code>, <code>created_by</code>.</td>
        <td>Master petugas siswa pendamping loket beserta log presensi harian per shift.</td>
    </tr>
    <tr>
        <td class="font-mono font-bold text-slate-900">verifikasi_kunjungan_siswa</td>
        <td><code>id</code>, <code>nasabah_id</code>, <code>tanggal</code>, <code>status_kunjungan</code>, <code>waktu_verifikasi</code>, <code>user_id</code>, <code>nama_verifikator</code>, <code>role_verifikator</code>, <code>loket</code>, <code>shift</code>, <code>petugas_avalis_id</code>, <code>petugas_avalis_nama</code>, <code>keterangan</code>.</td>
        <td>Unique constraint komposit <code>unique_siswa_tanggal_kunjungan</code> pada <code>(nasabah_id, tanggal)</code> untuk mencegah verifikasi kunjungan ganda siswa di hari yang sama.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="tabel-fitur-mobile-dan-audit" level="2">Tabel Saving Goals, Audit Log &amp; Konfigurasi</x-docs.heading>
<ul>
    <li><strong><code>saving_goals</code>:</strong> <code>id</code>, <code>nasabah_id</code> (FK cascade), <code>nama</code> (150), <code>target_amount</code> (decimal 15,2), <code>current_amount</code> (decimal 15,2), <code>icon</code> (50), <code>deadline</code> (date nullable), <code>is_completed</code> (boolean).</li>
    <li><strong><code>audit_logs</code>:</strong> <code>id</code>, <code>user_id</code>, <code>nama_user</code>, <code>role_user</code>, <code>jenis_aktivitas</code>, <code>modul</code>, <code>objek_id</code>, <code>old_values</code> (JSON), <code>new_values</code> (JSON), <code>ip_address</code>, <code>user_agent</code>, timestamps.</li>
    <li><strong><code>pengaturan_sistem</code>:</strong> Berbasis kolom bertipe eksplisit (1 baris konfigurasi aktif): <code>saldo_mengendap_min</code> (int default <code>10000</code>), <code>scanner_kamera_aktif</code> (bool default <code>true</code>), <code>tahun_ajaran_aktif</code> (default <code>2026/2027</code>), <code>format_no_rekening</code> (default <code>TA-JURUSAN-URUT</code>), <code>nama_bank</code> (default <code>LKM Mitra Siswa Abadi</code>), <code>biaya_administrasi_buku</code> (int default <code>5000</code>).</li>
    <li><strong><code>personal_access_tokens</code> &amp; <code>notifications</code>:</strong> Tabel standar Sanctum (<code>morphs('tokenable')</code>) dan Database Notifications Laravel (<code>morphs('notifiable')</code>, UUID primary key).</li>
</ul>

<x-docs.heading id="klasifikasi-tabel-legacy" level="2">Catatan Tabel Legacy / Tidak Aktif</x-docs.heading>
<x-docs.callout type="warning" title="Perbedaan Skema Migrasi Lama vs Model Aktif">
    <ul class="list-disc pl-5 space-y-1 my-1">
        <li><strong><code>jurnal_akuntansi</code> vs <code>jurnal</code>:</strong> Migrasi awal <code>2026_08_12_011333</code> membuat tabel <code>jurnal_akuntansi</code> (kolom <code>id_transaksi</code>, <code>kode_akun</code>, <code>posisi</code> debet/kredit), sementara model <code>JurnalAkuntansi</code> memetakan tabel plural <code>jurnal_akuntansis</code> dan alur transaksi aktif justru menulis ke tabel <code>jurnal</code> (<code>debit</code>, <code>kredit</code>, <code>tanggal</code>).</li>
        <li><strong><code>2026_08_12_075550_create_transaksi_table.php</code>:</strong> File migrasi kosong dengan komentar digabungkan ke migrasi awal <code>create_transaksis_table</code> (yang sebenarnya membuat tabel singular <code>transaksi</code>).</li>
        <li><strong>Model <code>UserApp</code>:</strong> Mereferensikan tabel <code>user_app</code>, namun tidak ada file migrasi di <code>database/migrations/</code> yang membuat tabel <code>user_app</code> dan tidak dipakai controller aktif.</li>
    </ul>
</x-docs.callout>

