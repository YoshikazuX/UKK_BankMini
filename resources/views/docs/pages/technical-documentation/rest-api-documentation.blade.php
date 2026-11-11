<x-docs.heading id="konvensi-umum-api" level="2">Konvensi Request, Response &amp; Base URL</x-docs.heading>
<p>
    Seluruh endpoint REST API didefinisikan dalam <code>routes/api.php</code> dengan prefix otomatis <code>/api</code> serta dikecualikan dari proteksi CSRF (<code>$middleware-&gt;validateCsrfTokens(except: ['api/*'])</code> di <code>bootstrap/app.php</code>). Pada environment produksi aplikasi mobile, Base URL menunjuk ke <code>https://hanbank.my.id/api</code>.
</p>
<p>
    Setiap respons menggunakan header <code>Content-Type: application/json</code> dan umumnya mengikuti struktur <code>{"success": bool, "message": string, "data": ...}</code>. Endpoint spesifikasi mesin interaktif juga tersedia pada <code>GET /api/docs</code> (<code>SystemApiController::docs</code>).
</p>

<x-docs.heading id="endpoint-sistem-dan-auth" level="2">Sistem Health &amp; Universal Auth</x-docs.heading>
<x-docs.endpoint method="GET" path="/api/" auth="Public" controller="SystemApiController::index" summary="Identitas API, status layanan, dan versi release API." />
<x-docs.endpoint method="GET" path="/api/health" auth="Public" controller="SystemApiController::health" summary="Pemeriksaan koneksi database real-time (SELECT 1) beserta statistik agregat nasabah &amp; transaksi." />
<x-docs.endpoint method="GET" path="/api/docs" auth="Public" controller="SystemApiController::docs" summary="Dokumentasi spesifikasi API dalam format JSON." />

<x-docs.endpoint method="POST" path="/api/auth/login" auth="Public" controller="UniversalAuthController::login" summary="Pintu masuk tunggal mobile Flutter. Backend mendeteksi apakah kredensial milik nasabah (tabel nasabah) atau petugas (tabel users) dan mengembalikan role + Bearer token Sanctum." />
<x-docs.code-block lang="json" title="Contoh Request &amp; Response — POST /api/auth/login">
// Request Body
{
  "username": "Handa",
  "password": "password123"
}

// Response 200 OK (Nasabah / Petugas)
{
  "success": true,
  "message": "Login berhasil!",
  "role": "nasabah",
  "token": "1|SanctumPlainTextTokenValue...",
  "token_type": "Bearer",
  "data": {
    "id_nasabah": 1,
    "username": "Handa",
    "nama_siswa": "Dewi Handayani Nur Halimah",
    "no_rekening": "202601001",
    "status": "aktif"
  }
}
</x-docs.code-block>

<x-docs.endpoint method="POST" path="/api/auth/logout" auth="auth:sanctum" controller="UniversalAuthController::logout" summary="Mencabut token Bearer aktif milik pemanggil saat ini." />
<x-docs.endpoint method="GET" path="/api/auth/me" auth="auth:sanctum" controller="UniversalAuthController::me" summary="Mengembalikan role aktif dan profil lengkap tokenable (User atau Nasabah)." />

<x-docs.heading id="endpoint-nasabah-mobile" level="2">Endpoint Modul Nasabah (Mobile Banking)</x-docs.heading>
<x-docs.callout type="warning" title="Status Middleware Route /api/nasabah/*">
    Pada <code>routes/api.php</code> saat audit, hanya <code>POST /api/nasabah/logout</code> yang memakai middleware <code>auth:sanctum</code>. Endpoint lain memakai metode internal <code>resolveNasabah()</code> yang menerima token Sanctum maupun parameter <code>no_rekening</code> / <code>id_nasabah</code> pada request.
</x-docs.callout>

<x-docs.table :headers="['Method &amp; URI', 'Middleware Route', 'Controller Method', 'Ringkasan Fungsi']">
    <tr>
        <td><code>POST /api/nasabah/login</code></td>
        <td>Public</td>
        <td><code>AuthNasabahController::login</code></td>
        <td>Autentikasi khusus nasabah (menerbitkan token <code>mobile-nasabah</code>).</td>
    </tr>
    <tr>
        <td><code>POST /api/nasabah/logout</code></td>
        <td><code>auth:sanctum</code></td>
        <td><code>AuthNasabahController::logout</code></td>
        <td>Menghapus token aktif nasabah.</td>
    </tr>
    <tr>
        <td><code>GET /api/nasabah/me</code> &amp; <code>/profil</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>ProfilNasabahController::me</code></td>
        <td>Profil rekening, kelas, jurusan, dan status.</td>
    </tr>
    <tr>
        <td><code>POST /api/nasabah/update-profil</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>ProfilNasabahController::updateProfil</code></td>
        <td>Perbarui <code>email</code>, <code>no_hp</code>, atau <code>nama_siswa</code>.</td>
    </tr>
    <tr>
        <td><code>POST /api/nasabah/verify-pin</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>AuthNasabahController::verifyPin</code></td>
        <td>Verifikasi PIN 6 digit (<code>Hash::check</code> atau fallback awal).</td>
    </tr>
    <tr>
        <td><code>POST /api/nasabah/ganti-password</code> &amp; <code>/ganti-pin</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>AuthNasabahController::gantiPassword / gantiPin</code></td>
        <td>Ubah password / PIN keamanan berdasarkan <code>no_rekening</code>.</td>
    </tr>
    <tr>
        <td><code>POST /api/nasabah/lupa-pin/ajukan</code> &amp; <code>/set-pin</code><br><code>GET /api/nasabah/lupa-pin/status</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>AuthNasabahController::requestPinReset / setNewPinWithToken / statusPinReset</code></td>
        <td>Alur pengajuan lupa PIN -&gt; cek persetujuan admin -&gt; atur PIN baru dengan <code>reset_token</code>.</td>
    </tr>
    <tr>
        <td><code>GET /api/nasabah/saldo</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>ProfilNasabahController::saldo</code></td>
        <td>Saldo total, saldo mengendap (dari <code>SettingHelper</code>), dan saldo tersedia.</td>
    </tr>
    <tr>
        <td><code>GET /api/nasabah/cek-rekening</code> &amp; <code>/qris-payload</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>ProfilNasabahController::cekRekening / qrisPayload</code></td>
        <td>Validasi tujuan rekening dan JSON payload QR identitas rekening siswa.</td>
    </tr>
    <tr>
        <td><code>GET /api/nasabah/riwayat</code> &amp; <code>/ringkasan</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>TransaksiNasabahController::riwayat / ringkasan</code></td>
        <td>Daftar mutasi (filter jenis) &amp; tren pemasukan/pengeluaran 6 bulan terakhir.</td>
    </tr>
    <tr>
        <td><code>POST /api/nasabah/transfer</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>TransaksiNasabahController::transfer</code></td>
        <td>Secara sengaja diblokir dengan HTTP <code>403</code> (transfer P2P dinonaktifkan sesuai kebijakan sekolah).</td>
    </tr>
    <tr>
        <td><code>POST /api/nasabah/emergency-freeze</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>ProfilNasabahController::emergencyFreeze</code></td>
        <td>Membekukan rekening (<code>status = nonaktif</code>) dan menulis entri ke <code>audit_logs</code>.</td>
    </tr>
    <tr>
        <td><code>GET /api/nasabah/badges</code> &amp; <code>/sessions</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>ProfilNasabahController::badges / sessions</code></td>
        <td>Evaluasi 6 lencana prestasi secara dinamis &amp; daftar token sesi aktif di <code>personal_access_tokens</code>.</td>
    </tr>
    <tr>
        <td><code>GET/POST /api/nasabah/goals</code><br><code>PUT/DELETE /api/nasabah/goals/{id}</code></td>
        <td>Tanpa <code>auth:sanctum</code></td>
        <td><code>SavingGoalApiController</code></td>
        <td>CRUD target tabungan siswa; mengembalikan status HTTP 404 jika goal tidak ditemukan.</td>
    </tr>
</x-docs.table>

<x-docs.code-block lang="json" title="Contoh Response — GET /api/nasabah/badges &amp; GET /api/nasabah/saldo">
// GET /api/nasabah/saldo?no_rekening=202601001
{
  "success": true,
  "data": {
    "no_rekening": "202601001",
    "nama_siswa": "Dewi Handayani Nur Halimah",
    "saldo": 150000,
    "saldo_mengendap": 10000,
    "saldo_tersedia": 140000
  }
}
</x-docs.code-block>

<x-docs.heading id="endpoint-notifikasi" level="2">Endpoint Notifikasi Universal</x-docs.heading>
<p>
    Menangani notifikasi milik <code>User</code> (berdasarkan Sanctum Bearer token) maupun <code>Nasabah</code> (token atau fallback parameter <code>no_rekening</code> / <code>id_nasabah</code>) pada <code>NotificationApiController</code>:
</p>
<ul>
    <li><code>GET /api/notifikasi</code> — Daftar notifikasi (dengan <code>unread_count</code>).</li>
    <li><code>GET /api/notifikasi/unread-count</code> — Jumlah notifikasi belum dibaca.</li>
    <li><code>POST /api/notifikasi/{id}/read</code> — Tandai satu notifikasi telah dibaca.</li>
    <li><code>POST /api/notifikasi/read-all</code> — Tandai seluruh notifikasi telah dibaca.</li>
</ul>

<x-docs.heading id="endpoint-petugas-dan-teller" level="2">Endpoint Petugas &amp; Loket Teller POS</x-docs.heading>
<x-docs.table :headers="['Method &amp; URI', 'Middleware Route', 'Controller Method', 'Keterangan Bisnis Aktual']">
    <tr>
        <td><code>POST /api/petugas/login</code></td>
        <td>Public</td>
        <td><code>AuthPetugasController::login</code></td>
        <td>Login khusus petugas (mengembalikan data petugas + token Sanctum).</td>
    </tr>
    <tr>
        <td><code>POST /api/petugas/logout</code> &amp; <code>GET /me</code></td>
        <td><code>auth:sanctum</code></td>
        <td><code>AuthPetugasController</code></td>
        <td>Profil petugas aktif dan pencabutan token.</td>
    </tr>
    <tr>
        <td><code>GET /api/petugas/nasabah</code> &amp; <code>/{identifier}</code></td>
        <td><code>auth:sanctum</code></td>
        <td><code>TellerApiController::searchNasabah / detailNasabah</code></td>
        <td>Cari nasabah berdasarkan nama/NIS/no_rekening; detail mengembalikan saldo tersedia dan riwayat 10 transaksi terakhir.</td>
    </tr>
    <tr>
        <td><code>POST /api/petugas/transaksi/setor</code></td>
        <td><code>auth:sanctum</code></td>
        <td><code>TellerApiController::setor</code></td>
        <td>Validasi minimal <code>1000</code> (berbeda dengan web minimal 5000), update saldo + jurnal + brankas, kode <code>101</code>.</td>
    </tr>
    <tr>
        <td><code>POST /api/petugas/transaksi/tarik</code></td>
        <td><code>auth:sanctum</code></td>
        <td><code>TellerApiController::tarik</code></td>
        <td>Cek status rekening aktif, saldo mencukupi di atas saldo mengendap, kas fisik <code>brankas</code> cukup, PIN opsional jika diisi, kode <code>201</code>.</td>
    </tr>
    <tr>
        <td><code>GET /api/petugas/transaksi/hari-ini</code> &amp; <code>GET /api/petugas/kas</code></td>
        <td><code>auth:sanctum</code></td>
        <td><code>TellerApiController::transaksiHariIni / kas</code></td>
        <td>Ringkasan transaksi tanggal berjalan dan posisi <code>brankas</code>.</td>
    </tr>
    <tr>
        <td><code>GET/POST /api/petugas/pengajuan-kas</code><br><code>GET/POST /api/petugas/daily-report</code></td>
        <td><code>auth:sanctum</code></td>
        <td><code>OperasionalKasApiController</code></td>
        <td>Daftar &amp; ajukan tiket droping kas / kirim laporan tutup kas harian dari Mobile POS.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="endpoint-supervisor-avalis-laporan" level="2">Endpoint Supervisor, Avalis &amp; Laporan</x-docs.heading>
<ul>
    <li><strong>Supervisor (<code>auth:sanctum</code> via <code>OperasionalKasApiController</code>):</strong> <code>GET /api/supervisor/pengajuan-kas</code>, <code>POST /api/supervisor/pengajuan-kas/{id}/setujui</code>, <code>POST /api/supervisor/pengajuan-kas/{id}/tolak</code>, <code>GET /api/supervisor/daily-report</code>, <code>POST /api/supervisor/daily-report/{id}/status</code>.</li>
    <li><strong>Avalis &amp; Kunjungan Siswa (tanpa <code>auth:sanctum</code> via <code>AvalisApiController</code>):</strong> <code>GET /api/avalis/absensi</code>, <code>GET /api/avalis/jadwal-hari-ini</code>, <code>POST /api/avalis/absensi/check-in</code>, <code>GET /api/avalis/kunjungan-siswa</code>, <code>POST /api/avalis/kunjungan-siswa/verifikasi</code>.</li>
    <li><strong>Laporan Eksekutif (tanpa <code>auth:sanctum</code> via <code>LaporanApiController</code>):</strong> <code>GET /api/laporan/ringkasan-harian</code>, <code>GET /api/laporan/saldo-per-kelas</code>, <code>GET /api/laporan/mutasi-jurnal</code>.</li>
</ul>

