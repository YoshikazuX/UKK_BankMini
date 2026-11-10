# DOKUMENTASI OPERASIONAL (SOP & MANUAL PENGGUNA)
## SISTEM CORE BANKING BANK MINI SEKOLAH (HANBANK)
### *Standard Operating Procedure (SOP), Manual Instalasi, & Panduan Alur Kerja Operasional Back-Office, Loket Teller, dan Mobile Banking*

---

## 1. Identitas Dokumen & Sistem

| Parameter | Keterangan |
|---|---|
| **Nama Sistem** | Sistem Informasi Bank Mini Sekolah (*HanBank / LKM Mitra Siswa Abadi*) |
| **Kode Proyek** | `BANKMINI-CBS-2026` |
| **Versi Dokumen** | `v2.8.0 (Operational & Bulk Import Multi-Sheet Release)` |
| **Target Implementasi** | Laboratorium Perbankan Mini SMK & Uji Kompetensi Keahlian (UKK) Nasional |
| **Framework & Engine** | PHP 8.4+, Laravel 13.x, MySQL 8.4 / MariaDB InnoDB, Tailwind CSS, Flutter 3.x |
| **Domain Produksi** | `https://hanbank.my.id` |
| **Status Dokumen** | **Resmi / Panduan Operasional Terstandarisasi** |

---

## 2. Kebutuhan Sistem & Tata Cara Menjalankan Aplikasi

### 2.1 Spesifikasi Server & Lingkungan Lokal
- **PHP**: Versi 8.3 atau 8.4 dengan ekstensi wajib: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `simplexml`.
- **Database**: MySQL 8.0+ atau MariaDB 10.6+ (Engine InnoDB).
- **Composer**: Versi 2.x ke atas.
- **Node.js & NPM**: Node.js LTS (v20+) dan NPM v10+.
- **Web Server**: Apache / Nginx / Built-in PHP CLI Server.

### 2.2 Langkah Instalasi & Menjalankan Server Lokal
1. **Persiapan Direktori & Dependensi:**
   ```bash
   cd "D:\UKK Bank Mini\belajar_laravel_handa"
   composer install
   npm install
   ```

2. **Konfigurasi Environment (`.env`):**
   Pastikan file `.env` bersih dari kesalahan ketik (sintaksis tidak terputus) dengan konfigurasi basis data yang sesuai:
   ```env
   APP_NAME="Bank Mini Sekolah"
   APP_ENV=local
   APP_KEY=base64:...
   APP_DEBUG=true
   APP_URL=http://127.0.0.1:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bank_mini_db
   DB_USERNAME=root
   DB_PASSWORD=

   # Format Nomor Rekening Default (JURUSAN-NIS / TAHUN/JURUSAN/URUT)
   # Mobile App APK URL
   APK_DOWNLOAD_URL=https://github.com/YoshikazuX/UKK_BankMini/releases/latest/download/BankMini-v2.4.0-universal.apk
   ```

3. **Migrasi Basis Data & Seeder Data Awal:**
   ```bash
   php artisan migrate --seed
   ```

4. **Kompilasi Aset Frontend (Vite):**
   ```bash
   npm run build
   # Atau mode development:
   npm run dev
   ```

5. **Menjalankan Server Web (Artisan Serve):**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui peramban web di `http://127.0.0.1:8000`.

> [!NOTE]
> **Troubleshooting Error `Encountered an invalid name` / `headers already sent`:**
> - Pastikan tidak ada spasi/kata liar tanpa format kunci (`KEY=VALUE`) di file `.env`.
> - Pastikan baris pertama file `bootstrap/app.php` diawali langsung dengan tag `<?php` tanpa karakter baris baru (*newline*) atau spasi di depannya.

---

## 3. Matriks Peran Pengguna (*Role-Based Access Control / RBAC*)

Sistem mengadopsi pemisahan tugas (*Segregation of Duties*) standar industri perbankan mikro:

| Role | Hak Akses Utama | Tanggung Jawab Operasional |
|---|---|---|
| **Administrator** | Manajemen direktori pengguna, data nasabah, impor massal Excel, pengaturan sistem, jejak audit, persetujuan reset PIN. | Mengelola operasional sistem, database, integritas akun, dan kelancaran infrastruktur. |
| **Supervisor** | Otorisasi droping kas, verifikasi penutupan kas harian teller, rekonsiliasi kas brankas, laporan keuangan/akuntansi. | Bertindak sebagai Kepala Bank Mini (*Checker & Approver*) untuk seluruh mutasi bernilai material. |
| **Teller** | Pembukaan kas loket harian, setor tunai, tarik tunai, cetak slip kuitansi, scan QR nasabah, penutupan kas harian. | Melayani transaksi fisik uang tunai di loket dengan prinsip ketelitian kas riil. |
| **Avalis** | Monitoring buku mutasi nasabah binaan, validasi kunjungan siswa, rekapitulasi tabungan rombel/kelas. | Siswa petugas piket / duta bank mini yang bertugas mencatat dan memverifikasi setoran rombel. |
| **Nasabah (Siswa/Guru)** | Akses mobile app: cek saldo, QRIS digital, riwayat transaksi, saving goals, ganti PIN, emergency freeze. | Pemilik rekening yang menabung dan memantau dana secara transparan. |

---

## 4. Prosedur Operasional Administrator

### 4.1 Manajemen Akun Pengguna & Pegawai
1. Akses menu **Admin &rarr; Kelola Pengguna**.
2. Tambahkan akun pegawai loket (Teller/Supervisor/Avalis) dengan menyertakan nama lengkap, email resmi, username, password awal, serta role yang tepat.
3. Seluruh perubahan status akun pegawai otomatis tercatat pada **Jejak Audit**.

### 4.2 Prosedur Impor Massal Nasabah Sekaligus (*Bulk Import Multi-Sheet Excel*)
Sistem HanBank telah dioptimalkan untuk memproses data ribuan siswa secara instan langsung dari file **Daftar Hadir Siswa Resmi Sekolah** (`.xlsx`, `.xls`, maupun `.csv`):

```
+--------------------------------------------------------------------------+
| ALUR IMPORT MASSAL DOKUMEN ABSENSI KE CORE BANKING                        |
|                                                                          |
| [File Excel .xlsx] ---> [Deteksi Rombel/Kelas] ---> [Generate No Rek]   |
| (Multi-Sheet X,XI,XII)  ("KELAS : X RPL 1")          (e.g. 26-RPL-002)   |
|                                |                            |            |
|                                v                            v            |
|                         [Mapping Jurusan]            [Filter Status]     |
|                         (RPL, TKJ, AKL, BD..)        (X=Nonaktif,        |
|                                                       XI/XII=Aktif)      |
|                                |                            |            |
|                                +------------> [Simpan DB] <-+            |
|                                              (1.700+ Siswa)              |
+--------------------------------------------------------------------------+
```

#### Langkah Kerja Eksekusi Impor:
1. Buka menu **Admin &rarr; Data Nasabah**.
2. Klik tombol **"Import Excel / CSV"** berwarna emerald di sudut kanan atas.
3. Pada jendela modal pop-up:
   - **Status Akun Default**: Pilih **"Otomatis"** (Siswa Kelas 10 diset *Belum Aktif* untuk menunggu verifikasi fisik & cetak buku; Siswa Kelas 11 & 12 langsung *Aktif*).
   - **Pilih Dokumen**: Pilih file workbook Excel (`.xlsx`), misalnya dokumen absen sekolah lengkap yang memuat sheet `X`, `XI`, `XII`.
4. Klik tombol **"Proses Import Data"**.
5. Sistem akan:
   - Mengekstraksi seluruh sheet kelas secara otomatis dalam transaksi basis data terisolasi (*atomic transaction*).
   - Memetakan rombel (contoh: `KELAS : X RPL 1`, `KELAS : XI BD`, `KELAS : XII LPS`) ke nama jurusan resmi (Rekayasa Perangkat Lunak, Bisnis Digital, Layanan Perbankan Syariah, dll).
   - Menghapus tanda bintang (`*`) dan membersihkan nama siswa.
   - Menerbitkan Nomor Rekening unik otomatis sesuai konfigurasi sekolah.
   - Menyinkronkan siswa lama jika NIS sudah ada di database tanpa membuat data ganda.
6. Notifikasi hasil impor akan memunculkan ringkasan: jumlah data baru terdaftar, jumlah data diperbarui, dan status baris dilewati.

### 4.3 Kebijakan Keamanan: Penonaktifan Tombol Hapus pada Nasabah Aktif
Untuk melindungi integritas keuangan dan mencegah hilangnya saldo nasabah secara tidak sah:
- **Tombol "Hapus" otomatis dihilangkan** untuk seluruh nasabah yang berstatus **Aktif**.
- Tombol "Hapus" hanya akan ditampilkan jika nasabah telah dialihkan ke status **Nonaktif** atau **Alumni**.
- Sistem backend secara ketat memvalidasi dan menolak setiap upaya penghapusan rekening aktif.
- Setiap penghapusan dilakukan secara **Soft Delete** (arsip aman) sehingga riwayat mutasi transaksi buku tabungan tetap utuh.

---

## 5. Prosedur Operasional Loket & Petugas Teller

### 5.1 Pembukaan Kas Loket Harian (*Opening Till*)
1. Petugas Teller login ke aplikasi web loket atau aplikasi mobile teller.
2. Memeriksa ketersediaan kas fisik di laci loket.
3. Jika membutuhkan modal tunai awal dari brankas utama, Teller mengajukan **Permohonan Droping Kas** kepada Supervisor.

### 5.2 Alur Transaksi Setor Tunai (*Cash Deposit*)
1. Nasabah menyerahkan uang tunai dan kartu tabungan / kartu pelajar QRIS.
2. Teller memasukkan NIS / Nomor Rekening atau mengarahkan pemindai kamera ke QR code nasabah.
3. Teller memverifikasi kecocokan identitas nasabah di layar monitor.
4. Teller menghitung uang fisik, menginputkan nominal setoran pada sistem, dan mengonfirmasi.
5. Sistem memproses transaksi secara atomik:
   - Saldo nasabah bertambah secara riil.
   - Saldo kas laci teller bertambah.
   - Jurnal akuntansi kas otomatis terbentuk (*Debit Kas Teller, Kredit Simpanan Nasabah*).
6. Teller mencetak slip setoran resmi dan membubuhkan stempel validasi.

### 5.3 Alur Transaksi Penarikan Tunai (*Cash Withdrawal*)
1. Nasabah mengajukan permohonan penarikan dan menyerahkan kartu / buku tabungan.
2. Teller menginputkan identitas rekening nasabah.
3. Teller menginputkan nominal penarikan yang diminta.
4. Sistem memverifikasi:
   - Saldo rekening mencukupi setelah dikurangi **Saldo Mengendap Minimum** (standar: Rp 10.000).
   - Saldo kas fisik di laci loket Teller mencukupi (*Pencegahan Negative Till*).
5. Nasabah memasukkan 6-digit PIN Keamanan pada sistem / keypad.
6. Teller menyerahkan uang tunai fisik dan slip bukti penarikan yang telah ditandatangani.

### 5.4 Penutupan Kas Harian Teller (*End of Day Closing*)
1. Di akhir jam operasional, Teller membuka menu **Tutup Kas Harian**.
2. Teller menghitung seluruh uang fisik pecahan (kertas & logam) di laci loket.
3. Teller menginput rincian lembar kas harian ke sistem.
4. Jika terjadi selisih (*over/short*), Teller wajib memberikan catatan penjelasan sebelum mengirimkan rekapitulasi ke Supervisor untuk diotorisasi.

---

## 6. Prosedur Operasional Supervisor (Kepala Bank Mini)

### 6.1 Otorisasi Permohonan Droping Kas
1. Buka menu **Supervisor &rarr; Persetujuan Droping Kas**.
2. Periksa rincian teller pemohon, alasan kebutuhan kas, dan ketersediaan brankas utama.
3. Lakukan konfirmasi persetujuan (*Approve*) dengan mekanisme kunci pesimistik (*pessimistic lock*) brankas.
4. Dana kas berpindah dari akun Kas Utama ke Kas Teller terkait.

### 6.2 Validasi Penutupan Kas Harian & Rekonsiliasi
1. Buka menu **Supervisor &rarr; Lembar Kas Harian**.
2. Lakukan pencocokan antara nominal fisik yang dilaporkan Teller dengan mutasi transaksi sistem.
3. Jika data cocok (*Balance*), berikan persetujuan penutupan kas.
4. Sistem membukukan penutupan buku harian perbankan.

### 6.3 Pengawasan Akuntansi & Laporan Keuangan
Supervisor dapat mengunduh dan mencetak laporan keuangan berkala:
- **Jurnal Umum Transaksi**: Rekaman kronologis seluruh debit dan kredit per transaksi.
- **Buku Besar Kas & Simpanan**: Rincian pergerakan akun kas brankas, kas teller, dan kewajiban simpanan.
- **Laporan Laba Rugi & Neraca**: Monitoring pendapatan administrasi dan beban bagi hasil bank mini sekolah.

---

## 7. Prosedur Operasional Nasabah (Aplikasi Mobile)

1. **Aktivasi Akun**: Siswa membuka aplikasi HanBank Mobile dan login menggunakan NIS serta password awal.
2. **Monitoring Keuangan**: Siswa dapat memantau saldo riil, melihat grafik pemasukan/pengeluaran, dan mengunduh mutasi rekening.
3. **Kartu Digital QRIS**: Siswa menunjukkan QR code dari aplikasi kepada Teller saat bertransaksi di sekolah.
4. **Target Menabung (*Saving Goals*)**: Siswa membuat pos tabungan rencana (misal: "Beli Laptop", "Study Tour") dan memantau progresnya.
5. **Keamanan Darurat (*Emergency Freeze*)**: Jika smartphone atau kartu fisik siswa hilang, siswa/orang tua dapat mengunci status rekening mandiri secara instan dari aplikasi.

---

## 8. Panduan Pencadangan Data (*Backup*) & Pemulihan Sistem

- **Pencadangan Basis Data**: Jalankan dump database secara berkala setiap akhir pekan:
  ```bash
  mysqldump -u root -p bank_mini_db > "backup_bankmini_$(date +%Y%m%d).sql"
  ```
- **Audit Logging**: Seluruh aktivitas modifikasi data, perubahan PIN, login gagal, dan pengarsipan rekening tersimpan permanen di tabel `audit_logs` dan tidak dapat diubah oleh pengguna loket.

