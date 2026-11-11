<x-docs.heading id="skenario-api-nasabah" level="2">Suite Uji Endpoint Nasabah Mobile</x-docs.heading>
<p>
    Pengujian endpoint REST API di repositori Laravel saat ini berfokus pada modul nasabah melalui 2 file Feature Test:
</p>
<x-docs.table :headers="['File API Test', 'Jumlah Skenario', 'Endpoint yang Diuji']">
    <tr>
        <td><code>NasabahApiTest.php</code></td>
        <td>13 method</td>
        <td>
            <code>POST /api/nasabah/login</code> (sukses &amp; gagal password), <code>POST /api/nasabah/logout</code> (dengan Bearer token), <code>GET /api/nasabah/saldo</code>, <code>POST /api/nasabah/verify-pin</code>, manajemen web admin nasabah &amp; login API, <code>GET /api/nasabah/cek-rekening</code>, pemblokiran <code>POST /api/nasabah/transfer</code> (HTTP 403), <code>POST /api/nasabah/ganti-password</code>, <code>POST /api/nasabah/ganti-pin</code>, serta <code>GET /api/nasabah/ringkasan</code>.
        </td>
    </tr>
    <tr>
        <td><code>NasabahMobileFiturLengkapTest.php</code></td>
        <td>6 method</td>
        <td>
            <code>POST /api/nasabah/emergency-freeze</code> (termasuk pencatatan <code>audit_logs</code>), CRUD lengkap <code>/api/nasabah/goals</code>, evaluasi 6 lencana pada <code>GET /api/nasabah/badges</code>, <code>GET /api/nasabah/sessions</code>, <code>GET/POST /api/notifikasi*</code>, serta tren 6 bulan pada <code>GET /api/nasabah/ringkasan</code>.
        </td>
    </tr>
</x-docs.table>

<x-docs.heading id="celah-cakupan-api" level="2">Endpoint yang Belum Memiliki Feature Test Otomatis</x-docs.heading>
<x-docs.callout type="warning" title="Gap Pengujian Backend REST API">
    Berdasarkan inventaris file pada <code>tests/Feature/</code>, endpoint API berikut <strong>belum memiliki PHPUnit Feature Test</strong> khusus di sisi backend Laravel (baru diuji di sisi mobile terhadap mock HTTP):
    <ul class="list-disc pl-5 space-y-1 mt-1">
        <li><code>/api/auth/login</code>, <code>/api/auth/logout</code>, <code>/api/auth/me</code> (<code>UniversalAuthController</code>).</li>
        <li>Seluruh grup <code>/api/petugas/*</code> (<code>AuthPetugasController</code>, <code>TellerApiController</code>: setor, tarik, kas, pengajuan-kas, daily-report).</li>
        <li>Seluruh grup <code>/api/supervisor/*</code> (<code>OperasionalKasApiController</code>).</li>
        <li>Seluruh grup <code>/api/avalis/*</code> (<code>AvalisApiController</code>) dan <code>/api/laporan/*</code> (<code>LaporanApiController</code>).</li>
    </ul>
</x-docs.callout>

