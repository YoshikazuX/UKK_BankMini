<x-docs.heading id="arsitektur-klien-flutter" level="2">Arsitektur Klien Flutter (ukk_bankmini)</x-docs.heading>
<p>
    Repositori aplikasi mobile <code>d:\Project UKK\ukk_bankmini</code> (versi paket <code>2.4.0+1</code>) menerapkan pola arsitektur berlapis yang terpisah bersih antara lapisan jaringan, penyimpanan sesi, model domain, service API, dan layar antarmuka per peran:
</p>
<ul>
    <li><strong><code>lib/core/constants/api_constants.dart</code>:</strong> Mendefinisikan seluruh konstanta endpoint REST API dan deteksi <code>defaultBaseUrl</code> otomatis (<code>https://hanbank.my.id/api</code> untuk rilis, <code>http://10.0.2.2:8000/api</code> untuk emulator Android debug, dan <code>http://127.0.0.1:8000/api</code> untuk desktop/web debug, atau override via <code>--dart-define=BASE_URL</code>).</li>
    <li><strong><code>lib/core/network/api_client.dart</code> &amp; <code>api_response.dart</code>:</strong> Singleton HTTP client berbasis <code>Dio</code> (timeout connect 12 dtk, receive 15 dtk) dengan wrapper generik <code>ApiResponse&lt;T&gt;</code>.</li>
    <li><strong><code>lib/core/storage/session_storage.dart</code>:</strong> Mengelola persistensi token Sanctum, role aktif, snapshot JSON <code>NasabahModel</code>/<code>PetugasModel</code>, serta custom Base URL di <code>SharedPreferences</code>.</li>
    <li><strong><code>lib/services/</code>:</strong> Terdiri dari <code>AuthService</code>, <code>NasabahApiService</code>, <code>TellerApiService</code>, dan <code>SupervisorApiService</code> yang menegaskan kebijakan <em>Zero Dummy Data Runtime</em> (tidak memalsukan data saat offline).</li>
</ul>

<x-docs.heading id="siklus-request-dio" level="2">Siklus Request Dio &amp; AuthInterceptor</x-docs.heading>
<x-docs.code-block lang="dart" title="lib/core/network/auth_interceptor.dart (Alur Injeksi Token)">
@override
Future&lt;void&gt; onRequest(RequestOptions options, RequestInterceptorHandler handler) async {
  options.headers['Accept'] = 'application/json';
  options.headers['Content-Type'] = 'application/json';

  final token = await SessionStorage.getToken();
  if (token != null && token.isNotEmpty) {
    options.headers['Authorization'] = 'Bearer $token';
  }
  return handler.next(options);
}

@override
void onError(DioException err, ErrorInterceptorHandler handler) async {
  if (err.response?.statusCode == 401) {
    await SessionStorage.clearSession();
    if (onSessionExpired != null) onSessionExpired!();
  }
  return handler.next(err);
}
</x-docs.code-block>

<x-docs.heading id="pemetaan-fitur-mobile-backend" level="2">Pemetaan Modul Flutter ke Backend API</x-docs.heading>
<x-docs.table :headers="['Layar / Shell di Flutter', 'Service / Endpoint yang Dipanggil', 'Controller Laravel Penjawab']">
    <tr>
        <td><code>LoginScreen</code> &amp; <code>SplashScreen</code></td>
        <td><code>AuthService.login()</code> &rarr; <code>POST /api/auth/login</code> &amp; <code>GET /api/auth/me</code></td>
        <td><code>UniversalAuthController</code></td>
    </tr>
    <tr>
        <td><code>MainNavigation</code> (Nasabah: Dashboard, Mutasi, Kartu Digital, Goals, Badges, Freeze, Session, Chart)</td>
        <td><code>NasabahApiService</code> &rarr; <code>/api/nasabah/me</code>, <code>saldo</code>, <code>riwayat</code>, <code>ringkasan</code>, <code>qris-payload</code>, <code>goals*</code>, <code>badges</code>, <code>sessions</code>, <code>emergency-freeze</code>, <code>verify-pin</code></td>
        <td><code>ProfilNasabahController</code>, <code>TransaksiNasabahController</code>, <code>SavingGoalApiController</code>, <code>AuthNasabahController</code></td>
    </tr>
    <tr>
        <td><code>NotifikasiScreen</code></td>
        <td><code>GET /api/notifikasi</code>, <code>/unread-count</code>, <code>/read-all</code>, <code>/{id}/read</code></td>
        <td><code>NotificationApiController</code></td>
    </tr>
    <tr>
        <td><code>TellerMainScreen</code> (POS Setor/Tarik, Scan, Droping Kas, Daily Report)</td>
        <td><code>TellerApiService</code> &rarr; <code>/api/petugas/nasabah*</code>, <code>/transaksi/setor</code>, <code>/transaksi/tarik</code>, <code>/kas</code>, <code>/pengajuan-kas</code>, <code>/daily-report</code></td>
        <td><code>TellerApiController</code>, <code>OperasionalKasApiController</code></td>
    </tr>
    <tr>
        <td><code>SupervisorMainScreen</code></td>
        <td><code>SupervisorApiService</code> &rarr; <code>/api/supervisor/pengajuan-kas*</code>, <code>/supervisor/daily-report*</code>, <code>/laporan/*</code></td>
        <td><code>OperasionalKasApiController</code>, <code>LaporanApiController</code></td>
    </tr>
    <tr>
        <td><code>AdminMainScreen</code></td>
        <td>Cek status server <code>GET /api/health</code> &amp; daftar presensi staf <code>GET /api/avalis/absensi</code></td>
        <td><code>SystemApiController::health</code>, <code>AvalisApiController::absensi</code></td>
    </tr>
</x-docs.table>

<x-docs.heading id="sinkronisasi-qr-dan-kontrak" level="2">Integrasi QR Nasabah &amp; Verifikasi Kontrak</x-docs.heading>
<p>
    Aplikasi siswa mengambil payload identitas QR resmi dari endpoint <code>GET /api/nasabah/qris-payload</code> lalu me-rendernya memakai paket <code>qr_flutter</code> pada <code>KartuDigitalScreen</code>. Di loket kasir web (<code>teller/dashboard.blade.php</code> menggunakan <code>html5-qrcode</code>) ataupun Mobile POS Teller (<code>scan_nasabah_screen.dart</code> menggunakan <code>mobile_scanner</code>), QR dipindai untuk langsung memuat data rekening tujuan tanpa pengetikan manual.
</p>
<x-docs.callout type="warning" title="Selisih Kontrak Minor yang Teridentifikasi">
    <ul class="list-disc pl-5 space-y-1 my-1">
        <li><strong>Minimal Setoran:</strong> Web <code>TellerController</code> mewajibkan minimal setor <code>Rp 5.000</code>, sementara API <code>TellerApiController::setor</code> mengizinkan mulai <code>Rp 1.000</code> (<code>min:1000</code>).</li>
        <li><strong>Scope Layar Admin di Mobile:</strong> <code>AdminMainScreen</code> di Flutter berfokus sebagai monitor kesehatan server (<code>/api/health</code>) dan daftar presensi avalis (<code>/api/avalis/absensi</code>); operasi CRUD pegawai, import XLSX, approval reset PIN/password, dan audit CSV sepenuhnya tersedia melalui aplikasi web Admin.</li>
    </ul>
</x-docs.callout>

