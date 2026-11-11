<x-docs.heading id="pengenalan-hanbank" level="2">Pengenalan HANBANK</x-docs.heading>
<p>
    <strong>HANBANK (Bank Mini Sekolah — LKM Mitra Siswa Abadi)</strong> adalah sistem informasi perbankan mini sekolah terintegrasi yang dikembangkan untuk mendukung pengelolaan tabungan siswa, operasional loket kasir (teller), pengawasan keuangan (supervisor/accounting), serta layanan mobile banking siswa pada lingkungan <strong>SMK Negeri 1 Bantul</strong>.
</p>
<p>
    Aplikasi ini terdiri atas dua repositori yang saling terhubung dalam satu ekosistem: backend dan aplikasi web operasional berbasis <strong>Laravel 13.24.0</strong> (<code>belajar_laravel_handa</code>) serta aplikasi Android klien multi-peran berbasis <strong>Flutter 3.41.9</strong> (<code>ukk_bankmini</code> v2.4.0).
</p>

<x-docs.callout type="info" title="Sumber Kebenaran Dokumentasi">
    Seluruh konten di HANBANK Documentation Center disusun langsung berdasarkan audit kode aktual pada repositori backend Laravel dan aplikasi mobile Flutter, bukan berdasarkan asumsi atau dokumen PRD lama. Perbedaan antara dokumen terdahulu dan implementasi kode dicatat secara eksplisit pada masing-masing bagian teknis dan evaluasi.
</x-docs.callout>

<x-docs.heading id="latar-belakang-pengembangan" level="2">Latar Belakang Pengembangan</x-docs.heading>
<p>
    Operasional Bank Mini Sekolah membutuhkan ketelitian pencatatan transaksi setoran dan penarikan, kontrol kas fisik di loket teller, pembagian wewenang yang jelas antara petugas pelaksana dan pemeriksa (<em>checker</em>), serta transparansi mutasi bagi nasabah siswa maupun guru/karyawan. Pencatatan manual atau sistem yang terfragmentasi berisiko menimbulkan selisih kas, lambatnya rekonsiliasi tutup kas harian, dan kurangnya visibilitas riwayat saldo oleh siswa.
</p>
<p>
    Untuk menjawab kebutuhan tersebut, HANBANK dirancang guna menyatukan proses pelayanan loket web, pencatatan buku kas (<code>brankas</code> dan <code>jurnal</code>), mekanisme pengajuan dan tutup kas berjenjang (<code>pengajuan_kas</code> dan <code>daily_reports</code>), pelacakan presensi petugas siswa/avalis, serta akses saldo digital siswa melalui smartphone.
</p>

<x-docs.heading id="tujuan-sistem" level="2">Tujuan Sistem</x-docs.heading>
<ul>
    <li><strong>Akuntabilitas Keuangan:</strong> Setiap setoran maupun penarikan tunai dicatat secara atomik pada tabel <code>transaksi</code>, memperbarui saldo nasabah, menghasilkan entri <code>jurnal</code> debit-kredit, serta menghitung posisi kas fisik di tabel <code>brankas</code>.</li>
    <li><strong>Penerapan Separation of Duties (SoD):</strong> Memisahkan secara tegas wewenang operasional antara Administrator (pengelola master data &amp; konfigurasi), Teller (pelayanan transaksi kasir), dan Supervisor/Accounting (pemeriksa, otorisasi droping kas, dan rekonsiliasi harian).</li>
    <li><strong>Layanan Nasabah Modern:</strong> Menyediakan kartu rekening QR digital, riwayat mutasi, target menabung (<em>Saving Goals</em>), sistem lencana pencapaian (<em>Badges</em>), pembekuan darurat rekening (<em>Emergency Freeze</em>), dan permohonan reset PIN mandiri di aplikasi mobile serta portal web.</li>
    <li><strong>Jejak Audit Terstruktur:</strong> Merekam perubahan data penting dan keputusan otorisasi pada tabel <code>audit_logs</code> dan <code>approval_histories</code> lengkap dengan penyamaran data sensitif melalui <code>AuditHelper</code>.</li>
</ul>

<x-docs.heading id="ruang-lingkup-sistem" level="2">Ruang Lingkup Sistem</x-docs.heading>
<x-docs.table :headers="['Domain Sistem', 'Cakupan Implementasi Aktual', 'Referensi Modul']">
    <tr>
        <td class="font-semibold text-slate-900">Portal Publik &amp; Edukasi</td>
        <td>Landing page resmi institusi, halaman unduh APK mobile (<code>/download</code>), halaman informasi, dan HANBANK Documentation Center (<code>/docs</code>).</td>
        <td><code>PageController</code>, <code>DocsController</code></td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Administrasi &amp; Master Data</td>
        <td>CRUD nasabah, generator nomor rekening berformat jurusan/tahun ajaran, import CSV/XLSX native, arsip alumni &amp; <em>soft delete</em>, manajemen pegawai, persetujuan reset password/PIN, absensi avalis, jejak audit, dan konfigurasi global.</td>
        <td><code>AdminController</code>, <code>NasabahController</code>, <code>AvalisPetugasController</code></td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Loket Pelayanan Teller</td>
        <td>Pemindaian QR nasabah (<code>html5-qrcode</code>), verifikasi kunjungan harian, setoran tunai (kode <code>101</code>), penarikan tunai ber-PIN &amp; batas saldo mengendap (kode <code>201</code>), cetak slip/buku tabungan, <em>idempotency key</em>, pembatalan transaksi (<em>void</em>), pengajuan droping kas, dan tutup kas harian.</td>
        <td><code>TellerController</code>, <code>BukuTabunganController</code>, <code>DailyReportController</code></td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Pengawasan &amp; Laporan Supervisor</td>
        <td>Pusat persetujuan (setuju, tahan/<em>hold</em>, tolak dengan penguncian pesimistik), monitoring loket teller, rekonsiliasi fisik vs sistem, notifikasi database, buku jurnal akuntansi, laporan saldo per jurusan/kelas, dan cetak harian.</td>
        <td><code>SupervisorController</code>, <code>LaporanKeuanganController</code>, <code>PengajuanKasController</code></td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Aplikasi Mobile Flutter</td>
        <td>Login universal multi-peran berbasis Laravel Sanctum, dashboard nasabah, kartu QRIS payload, mutasi, chart keuangan, saving goals, badges, kontrol freeze, notifikasi, log sesi, serta antarmuka operasional Teller POS &amp; Supervisor/Admin ringkas.</td>
        <td><code>ukk_bankmini/lib/</code> &amp; <code>app/Http/Controllers/Api/</code></td>
    </tr>
</x-docs.table>

<x-docs.heading id="arsitektur-dokumentasi" level="2">Peta Dokumentasi</x-docs.heading>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-5">
    <a href="{{ route('docs.show', ['section' => 'getting-started', 'page' => 'system-overview']) }}" class="no-prose-link block p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-500/60 shadow-sm transition">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">A · Getting Started</div>
        <div class="font-bold text-slate-900 mt-1">System Overview &amp; Architecture</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">Memahami pengguna sistem, diagram arsitektur web-mobile, dan daftar versi teknologi aktual.</p>
    </a>
    <a href="{{ route('docs.show', ['section' => 'technical-documentation', 'page' => 'rest-api-documentation']) }}" class="no-prose-link block p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-500/60 shadow-sm transition">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">B · Technical Docs</div>
        <div class="font-bold text-slate-900 mt-1">Database, REST API &amp; Business Logic</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">Bedah tuntas skema database, ERD, daftar endpoint API, otorisasi RoleGuard, dan aturan bisnis.</p>
    </a>
    <a href="{{ route('docs.show', ['section' => 'operational-documentation', 'page' => 'teller-guide']) }}" class="no-prose-link block p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-500/60 shadow-sm transition">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">C · Operational Docs</div>
        <div class="font-bold text-slate-900 mt-1">Panduan Berbasis Peran (Role Guide)</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">SOP langkah demi langkah untuk Admin, Teller, Supervisor, dan manajemen fitur tambahan.</p>
    </a>
    <a href="{{ route('docs.show', ['section' => 'project-evaluation', 'page' => 'project-evaluation-report']) }}" class="no-prose-link block p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-500/60 shadow-sm transition">
        <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">D &amp; E · QA &amp; Evaluation</div>
        <div class="font-bold text-slate-900 mt-1">Testing &amp; Project Evaluation</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">Hasil uji terverifikasi, evaluasi keamanan &amp; integrasi, serta rekomendasi perbaikan proyek.</p>
    </a>
</div>

