<x-docs.heading id="dualisme-guard-autentikasi" level="2">Web Session Guard vs Sanctum Bearer Token</x-docs.heading>
<p>
    HANBANK memisahkan mekanisme autentikasi antara aplikasi web dan REST API:
</p>
<ul>
    <li><strong>Web Session Guard (<code>config/auth.php</code>):</strong> Menggunakan guard default <code>web</code> dengan provider <code>users</code> (<code>App\Models\User</code>) dan driver session database (<code>SESSION_DRIVER=database</code> di <code>.env.example</code>).</li>
    <li><strong>Laravel Sanctum API Token (<code>config/sanctum.php</code>):</strong> Menggunakan guard array <code>['web']</code> dengan <code>expiration =&gt; null</code> (token tidak otomatis kedaluwarsa hingga pengguna logout atau dicabut dari tabel <code>personal_access_tokens</code>).</li>
    <li><strong>Dual Tokenable Model:</strong> Baik model <code>User</code> (Admin, Teller, Supervisor) maupun model <code>Nasabah</code> menggunakan trait <code>Laravel\Sanctum\HasApiTokens</code>, sehingga tabel <code>personal_access_tokens</code> menampung token dari kedua entitas secara polimorfik.</li>
</ul>

<x-docs.heading id="alur-penerbitan-token" level="2">Alur Login Universal &amp; Token Lifecycle</x-docs.heading>
<p>
    Aplikasi Flutter memanggil gerbang masuk universal <code>POST /api/auth/login</code> (<code>UniversalAuthController::login</code>) alih-alih meminta pengguna memilih role di UI terlebih dahulu:
</p>
<x-docs.steps>
    <x-docs.step number="1" title="Pencarian Kredensial Berurutan">
        <p>Server mencari kecocokan <code>username</code> terlebih dahulu pada tabel <code>users</code> (petugas). Apabila tidak ditemukan atau bukan petugas, pengecekan berlanjut ke tabel <code>nasabah</code> berdasarkan <code>username</code> atau <code>nis</code>.</p>
    </x-docs.step>
    <x-docs.step number="2" title="Verifikasi Password &amp; Status Akun">
        <p>Password divalidasi menggunakan <code>Hash::check()</code>. Akun petugas dengan <code>status === 'nonaktif'</code> ditolak dengan HTTP 403. Token lama dibersihkan lalu dibuat token baru via <code>createToken('mobile-' . $role)-&gt;plainTextToken</code>.</p>
    </x-docs.step>
    <x-docs.step number="3" title="Penyimpanan &amp; Injeksi Header Bearer">
        <p>Klien Flutter menyimpan token di <code>SharedPreferences</code> melalui <code>SessionStorage</code>. Seluruh panggilan <code>ApiClient</code> berikutnya menyertakan <code>Authorization: Bearer &lt;token&gt;</code> oleh <code>AuthInterceptor</code>. Jika server mengembalikan HTTP <code>401</code>, interceptor otomatis menghapus sesi lokal.</p>
    </x-docs.step>
</x-docs.steps>

<x-docs.heading id="audit-proteksi-route-api" level="2">Audit Cakupan Middleware auth:sanctum</x-docs.heading>
<p>
    Hasil pemeriksaan langsung pada <code>routes/api.php</code> dan controller API menunjukkan variasi tingkat proteksi token di level routing:
</p>

<x-docs.table :headers="['Kelompok Endpoint', 'Middleware di routes/api.php', 'Mekanisme Resolusi Pengguna Aktual']">
    <tr>
        <td><code>/api/auth/logout</code>, <code>/api/auth/me</code></td>
        <td><span class="font-semibold text-emerald-700"><code>auth:sanctum</code> aktif</span></td>
        <td>Mengambil token aktif via <code>$request-&gt;user()</code> dan mendeteksi kelas <code>User</code> vs <code>Nasabah</code>.</td>
    </tr>
    <tr>
        <td><code>/api/petugas/*</code> (kecuali <code>/login</code>) &amp; <code>/api/supervisor/*</code></td>
        <td><span class="font-semibold text-emerald-700"><code>auth:sanctum</code> aktif</span></td>
        <td>Token petugas wajib hadir; namun <strong>belum</strong> memakai middleware <code>RoleGuard</code> di level route API.</td>
    </tr>
    <tr>
        <td><code>/api/nasabah/logout</code></td>
        <td><span class="font-semibold text-emerald-700"><code>auth:sanctum</code> aktif</span></td>
        <td>Mencabut token Bearer saat ini.</td>
    </tr>
    <tr>
        <td><code>/api/nasabah/*</code> (selain <code>login</code> &amp; <code>logout</code>)</td>
        <td><span class="font-semibold text-amber-700">Tidak memakai <code>auth:sanctum</code> di router</span></td>
        <td>Menggunakan helper internal <code>resolveNasabah()</code> yang membaca Bearer token <em>atau</em> menerima parameter query/body <code>no_rekening</code> / <code>id_nasabah</code> (dan <code>verifyPin</code>/<code>gantiPassword</code>/<code>gantiPin</code> menerima <code>no_rekening</code> langsung).</td>
    </tr>
    <tr>
        <td><code>/api/avalis/*</code> &amp; <code>/api/laporan/*</code></td>
        <td><span class="font-semibold text-rose-700">Tanpa <code>auth:sanctum</code></span></td>
        <td>Endpoint terbuka tanpa middleware autentikasi route agar mudah diakses atau perlu penguatan di fase produksi lanjutan.</td>
    </tr>
</x-docs.table>

<x-docs.callout type="danger" title="Temuan Keamanan Penting">
    Mekanisme fallback parameter <code>no_rekening</code> pada grup <code>/api/nasabah/*</code> serta tidak terpasangnya <code>auth:sanctum</code> pada <code>/api/avalis/*</code> dan <code>/api/laporan/*</code> mempermudah pengujian/kompatibilitas awal, tetapi berisiko menimbulkan <em>Insecure Direct Object Reference (IDOR)</em> jika dibiarkan terbuka pada jaringan publik tanpa pengetatan middleware Sanctum.
</x-docs.callout>

