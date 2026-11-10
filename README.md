# Sistem Informasi Bank Mini Sekolah Enterprise (HanBank)
### *Full-Stack Omnichannel Core Micro-Banking, Accounting Ledger, Teller POS & Mobile Student Banking System*

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13" />
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4" />
  <img src="https://img.shields.io/badge/Flutter-3.x-02569B?style=for-the-badge&logo=flutter&logoColor=white" alt="Flutter 3" />
  <img src="https://img.shields.io/badge/TailwindCSS-Vite-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/MySQL-InnoDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
  <img src="https://img.shields.io/badge/Status-Production--Live-22C55E?style=for-the-badge" alt="Production Live" />
</p>

---

## 📌 Ringkasan Eksekutif

**HanBank (LKM Mitra Siswa Abadi)** adalah aplikasi perbankan mikro terpadu (*Core Banking System*) yang dikembangkan untuk laboratorium perbankan mini institusi Sekolah Menengah Kejuruan (SMK) dan standar **Uji Kompetensi Keahlian (UKK)** Nasional. 

Sistem ini merefleksikan alur kerja industri perbankan riil dengan menerapkan prinsip **Maker-Checker**, pembukuan akuntansi berpasangan otomatis (*Double-Entry Bookkeeping*), pencegahan kas minus (*Negative Till Guard*), pemindai QRIS kamera terintegrasi, serta aplikasi mobile native bagi siswa dan teller.

- 🌐 **Production Live**: [https://hanbank.my.id](https://hanbank.my.id)
- 📱 **Unduh Aplikasi Android (APK)**: [BankMini-v2.4.0-universal.apk](https://github.com/YoshikazuX/UKK_BankMini/releases/latest/download/BankMini-v2.4.0-universal.apk)
- 🏢 **Laboratorium Perbankan**: SMK Negeri 1 Bantul (SMK Pusat Keunggulan)

---

## ✨ Fitur Unggulan Sistem

### 1. 🏛️ Portal Web Back-Office & Akuntansi
- **Portal Publik Modern (`/` dan `/download`)**: Showcase mockup 3D interaktif *HanBank Mobile*, profil tabungan mikro, kalkulator simulasi, panduan operasional, dan pusat unduh APK resmi.
- **Manajemen Nasabah Cerdas**:
  - Pendaftaran nasabah siswa & guru secara instan dengan penomoran rekening otomatis berbasis jurusan & tahun ajaran (contoh: `26-RPL-002`, `26-BD-037`).
  - **Impor Massal Dokumen Absensi Excel (`.xlsx`, `.xls`, `.csv`)**: Otomatis mendeteksi multi-sheet (`X`, `XI`, `XII`), membaca seluruh rombel/kelas (`KELAS : X RPL 1`, `KELAS : XI BD`, `KELAS : XII LPS`, dll.), membersihkan tanda bintang non-Muslim (`*`), dan menyinkronkan data (teruji instan pada **1.711 siswa dari 48 rombel**).
  - **Proteksi Rekening Aktif**: Tombol hapus otomatis dihilangkan untuk nasabah berstatus Aktif guna mencegah kehilangan dana tabungan secara tidak disengaja.
  - Arsip *Soft-Delete* aman dan pemulihan (*restore*) data nasabah tanpa merusak mutasi buku tabungan.
- **Loket Teller POS Terpadu**:
  - Pemindai kamera QRIS/Barcode kartu pelajar bebas efek cermin (*non-mirror* dengan toggle flip).
  - Setor tunai cepat dengan verifikasi identitas real-time.
  - Tarik tunai aman dengan validasi PIN 6-digit nasabah, saldo mengendap minimum (Rp 10.000), dan validasi ketersediaan kas laci loket.
  - Cetak slip kuitansi transaksi resmi dan cetak mutasi buku tabungan.
- **Supervisor & Akuntansi Berpasangan (*Double-Entry Ledger*)**:
  - Otorisasi permohonan droping kas teller dengan mekanisme kunci pesimistik (*pessimistic lock*).
  - Validasi lembar kas harian teller dan rekonsiliasi kas penutupan harian (*Daily Cash Reconciliation*).
  - Pembukuan jurnal otomatis berpasangan: Kas Teller, Kas Utama, Simpanan Nasabah, Beban Bunga, dan Pendapatan Administrasi.
  - Laporan keuangan otomatis: Jurnal Umum, Buku Besar Akun, Neraca Saldo, dan Laporan Laba Rugi.
- **Keamanan & Jejak Audit (*Audit Trails*)**:
  - Jejak audit mendalam (`audit_logs`) mencatat waktu, IP address, user, modul, aksi, nilai lama, dan nilai baru.
  - Pusat persetujuan reset PIN mandiri nasabah melalui tiket admin.

### 2. 📱 Aplikasi Native Mobile (Flutter)
- **Multi-Role Login**: Mendukung login Nasabah (Siswa), Petugas Loket Teller, Supervisor, dan Admin.
- **Dashboard Finansial Siswa**: Monitoring saldo tabungan riil, grafik arus kas bulanan, dan mutasi debit/kredit.
- **Kartu Identitas QRIS Digital**: Kartu pelajar digital dengan QR Code terenkripsi (`BANKMINI:REK:...:NIS:...:NAMA:...`) untuk transaksi cepat di loket.
- **Target Menabung (*Saving Goals*)**: Menetapkan target impian menabung disertai bilah kemajuan (*progress bar*) dan estimasi pencapaian.
- **Gamifikasi (*Badges*)**: Penghargaan pencapaian konsistensi menabung siswa.
- **Fitur Kunci Darurat (*Emergency Freeze*)**: Kemampuan mengunci status rekening mandiri secara instan jika smartphone atau kartu fisik hilang.

---

## 🛠️ Arsitektur Teknologi (*Tech Stack*)

| Lapisan Sistem | Teknologi yang Digunakan |
|---|---|
| **Backend Core Framework** | Laravel 13.x (PHP 8.4+) |
| **Database Management** | MySQL 8.0+ / MariaDB 10.6+ (InnoDB Engine, ACID Transactions) |
| **Otentikasi & API Gateway** | Laravel Sanctum v4 (Stateful Token & Bearer Token RESTful API) |
| **Excel & Spreadsheet Engine** | `phpoffice/phpspreadsheet` v5.11 |
| **Frontend Back-Office** | Blade Templating, Tailwind CSS, Vite 8, Alpine.js |
| **Mobile Client Framework** | Flutter 3.x, Dart 3.x |
| **Mobile Libraries** | Dio 5.x, MobileScanner 7.x, FL Chart 0.70, QR Flutter |
| **Hosting & Infrastruktur** | Cloud Shared Hosting (DomaiNesia), GitHub Actions CI/CD FTPS Pipeline |

---

## 🚀 Panduan Memulai Cepat (*Quick Start Guide*)

### 1. Kloning Repositori & Instalasi Dependensi
```bash
git clone https://github.com/YoshikazuX/UKK_BankMini.git
cd UKK_BankMini/belajar_laravel_handa

# Instal dependensi backend PHP
composer install

# Instal dependensi frontend Node.js
npm install
```

### 2. Konfigurasi Lingkungan (`.env`)
Salin file contoh konfigurasi dan sesuaikan kredensial basis data:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan variabel penting telah terisi pada `.env`:
```env
APP_NAME="Bank Mini Sekolah"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bank_mini_db
DB_USERNAME=root
DB_PASSWORD=

APK_DOWNLOAD_URL=https://github.com/YoshikazuX/UKK_BankMini/releases/latest/download/BankMini-v2.4.0-universal.apk
```

### 3. Migrasi & Seed Database
Jalankan migrasi tabel perbankan dan seeder data awal:
```bash
php artisan migrate --seed
```

### 4. Kompilasi Aset Frontend
```bash
npm run build
# Atau untuk mode pengembang dengan hot-reload:
npm run dev
```

### 5. Jalankan Server Web Lokal
```bash
php artisan serve
```
Akses portal melalui peramban web di `http://127.0.0.1:8000`.

---

## 👥 Kredensial Pengguna Bawaan (*Default Accounts*)

| Role | Username / Identifier | Password Default | Hak Akses |
|---|---|---|---|
| **Administrator** | `admin` | `password` / `admin123` | Konfigurasi sistem, direktori, audit, impor massal. |
| **Supervisor** | `supervisor` | `password` / `supervisor123` | Otorisasi kas, closing harian, laporan akuntansi. |
| **Teller Loket** | `teller` | `password` / `teller123` | Layanan setor, tarik, scanner QR, cetak slip kasir. |
| **Avalis (Piket)** | `avalis` | `password` / `avalis123` | Rekapitulasi tabungan rombel dan monitoring binaan. |
| **Nasabah (Siswa)** | Sesuai NIS (contoh: `18910`, `17763`) | `123456` *(PIN: `123456`)* | Akses mobile banking, cek saldo, mutasi, kartu QR. |

---

## 📑 Struktur Dokumentasi Proyek

Dokumentasi operasional terperinci, laporan evaluasi, dan spesifikasi arsitektur tersedia di folder [`docs/`](file:///d:/UKK%20Bank%20Mini/belajar_laravel_handa/docs):

- 📘 [`docs/DOKUMENTASI_OPERASIONAL.md`](file:///d:/UKK%20Bank%20Mini/belajar_laravel_handa/docs/DOKUMENTASI_OPERASIONAL.md): **Standard Operating Procedure (SOP)** & Manual Pengguna untuk Administrator, Supervisor, Teller, Avalis, dan Nasabah Mobile.
- 📊 [`docs/LAPORAN_EVALUASI_PROYEK.md`](file:///d:/UKK%20Bank%20Mini/belajar_laravel_handa/docs/LAPORAN_EVALUASI_PROYEK.md): **Laporan Evaluasi Proyek**, analisis hasil uji beban impor 1.711 siswa dari 48 rombel, pengujian keamanan, mitigasi race condition, dan kepatuhan rubrik UKK.
- 📐 [`docs/PRD_Sistem_Bank_Mini_Sekolah.md`](file:///d:/UKK%20Bank%20Mini/belajar_laravel_handa/docs/PRD_Sistem_Bank_Mini_Sekolah.md): Dokumen Kebutuhan Produk (PRD) Sistem Core Banking Inti.
- 📱 [`docs/PRD_Mobile_App_Bank_Mini.md`](file:///d:/UKK%20Bank%20Mini/belajar_laravel_handa/docs/PRD_Mobile_App_Bank_Mini.md): Dokumen Kebutuhan Produk (PRD) Aplikasi Mobile Flutter Nasabah & POS Teller.

---

## 🔒 Kebijakan Keamanan & Kepatuhan Perbankan

1. **Aturan Deletion Guard**: Rekening yang berstatus **Aktif** tidak dapat dihapus baik melalui antarmuka admin maupun permintaan langsung ke server. Rekening harus berstatus *Nonaktif* atau *Alumni* sebelum dapat diarsipkan secara *Soft-Delete*.
2. **Pencegahan Kas Negatif**: Setiap transaksi penarikan tunai divalidasi ganda terhadap kecukupan saldo nasabah (dikurangi saldo mengendap minimum) dan ketersediaan fisik uang kas di loket teller terkait.
3. **Pessimistic Locking**: Permintaan dana kas dan penarikan diproteksi dengan `DB::raw` / `lockForUpdate()` untuk mencegah anomali konkurensi data.
4. **Enkripsi Kredensial**: Password dan PIN 6-digit terenkripsi menggunakan algoritma `bcrypt` satu arah.

---

## 📄 Lisensi
Sistem Informasi Bank Mini Sekolah dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
Dikembangkan untuk sarana pembelajaran vokasi dan Uji Kompetensi Keahlian (UKK) SMK.
