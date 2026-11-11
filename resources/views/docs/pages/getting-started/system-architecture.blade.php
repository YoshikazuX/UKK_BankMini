<x-docs.heading id="arsitektur-keseluruhan" level="2">Arsitektur Keseluruhan Sistem</x-docs.heading>
<p>
    HANBANK menerapkan arsitektur <strong>monolitik terpadu untuk web sekaligus penyedia REST API terpusat</strong> di sisi backend Laravel, yang melayani dua kelompok klien: antarmuka browser (Blade + Alpine.js + Tailwind CSS) dan klien mobile native Android (Flutter + Dio HTTP client). Kedua kanal terhubung ke satu skema basis data relasional yang sama.
</p>

<x-docs.heading id="diagram-arsitektur" level="2">Diagram Arsitektur Ekosistem</x-docs.heading>
<div class="my-6 p-4 sm:p-6 rounded-2xl bg-white border border-slate-200 shadow-sm overflow-x-auto">
    <svg viewBox="0 0 860 440" class="w-full min-w-[680px] h-auto" role="img" aria-label="Diagram Arsitektur Sistem HANBANK">
        <defs>
            <marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                <path d="M 0 1 L 10 5 L 0 9 z" fill="#475569" />
            </marker>
            <marker id="arrow-emerald" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                <path d="M 0 1 L 10 5 L 0 9 z" fill="#059669" />
            </marker>
        </defs>

        {{-- Layer Labels --}}
        <text x="20" y="26" fill="#64748B" font-size="11" font-weight="700" font-family="Plus Jakarta Sans, sans-serif">LAPISAN KLIEN (PRESENTATION)</text>
        <text x="315" y="26" fill="#64748B" font-size="11" font-weight="700" font-family="Plus Jakarta Sans, sans-serif">LAPISAN APLIKASI LARAVEL 13</text>
        <text x="665" y="26" fill="#64748B" font-size="11" font-weight="700" font-family="Plus Jakarta Sans, sans-serif">DATA &amp; INFRASTRUKTUR</text>

        {{-- Client 1: Web Browser --}}
        <rect x="20" y="45" width="230" height="165" rx="12" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="20" y="45" width="230" height="34" rx="12" fill="#0F172A" />
        <text x="35" y="66" fill="#FFFFFF" font-size="12" font-weight="700" font-family="Plus Jakarta Sans, sans-serif">Web Client (Browser)</text>
        <text x="35" y="100" fill="#334155" font-size="11" font-weight="600">• Landing, Download &amp; Docs (/docs)</text>
        <text x="35" y="122" fill="#334155" font-size="11">• Dashboard Admin, Teller, Supervisor</text>
        <text x="35" y="144" fill="#334155" font-size="11">• HTML5 QR Scanner &amp; Cetak Slip</text>
        <text x="35" y="166" fill="#0F766E" font-size="10.5" font-weight="700">Laravel Blade + Tailwind + Alpine.js</text>
        <text x="35" y="190" fill="#64748B" font-size="10">Auth: Session Cookie + CSRF Token</text>

        {{-- Client 2: Flutter Mobile App --}}
        <rect x="20" y="240" width="230" height="175" rx="12" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="20" y="240" width="230" height="34" rx="12" fill="#0F766E" />
        <text x="35" y="261" fill="#FFFFFF" font-size="12" font-weight="700" font-family="Plus Jakarta Sans, sans-serif">Mobile App (Flutter v2.4.0)</text>
        <text x="35" y="295" fill="#334155" font-size="11" font-weight="600">• Mobile Banking Siswa (QR, Goals)</text>
        <text x="35" y="317" fill="#334155" font-size="11">• Mobile POS Teller &amp; Kas</text>
        <text x="35" y="339" fill="#334155" font-size="11">• Approval Supervisor &amp; Admin Health</text>
        <text x="35" y="361" fill="#0F766E" font-size="10.5" font-weight="700">Dio HTTP Client + AuthInterceptor</text>
        <text x="35" y="385" fill="#64748B" font-size="10">Auth: Sanctum Bearer Token</text>
        <text x="35" y="401" fill="#64748B" font-size="10">(SharedPreferences Cache)</text>

        {{-- Laravel Core --}}
        <rect x="315" y="45" width="290" height="370" rx="14" fill="#FFFFFF" stroke="#0F172A" stroke-width="1.8" />
        <rect x="315" y="45" width="290" height="36" rx="12" fill="#0F172A" />
        <text x="332" y="68" fill="#10B981" font-size="12" font-weight="800" font-family="Plus Jakarta Sans, sans-serif">Laravel 13 Backend (routes/web &amp; api)</text>

        {{-- Routing & Middleware Box --}}
        <rect x="332" y="96" width="256" height="62" rx="8" fill="#F1F5F9" stroke="#CBD5E1" />
        <text x="344" y="116" fill="#0F172A" font-size="11" font-weight="700">HTTP Routing &amp; Guard Layer</text>
        <text x="344" y="133" fill="#475569" font-size="10">• Web: session auth + RoleGuard (SoD)</text>
        <text x="344" y="148" fill="#475569" font-size="10">• API: /api/* (CSRF exempt) + auth:sanctum</text>

        {{-- Controllers Box --}}
        <rect x="332" y="170" width="256" height="72" rx="8" fill="#ECFDF5" stroke="#A7F3D0" />
        <text x="344" y="190" fill="#065F46" font-size="11" font-weight="700">Controllers (12 Web + 12 API + Docs)</text>
        <text x="344" y="207" fill="#065F46" font-size="10">• Web: Admin, Teller, Supervisor, Laporan</text>
        <text x="344" y="222" fill="#065F46" font-size="10">• API: UniversalAuth, Nasabah, TellerPOS,</text>
        <text x="344" y="235" fill="#065F46" font-size="10">  OperasionalKas, Avalis, Notification</text>

        {{-- Domain Services & Helpers Box --}}
        <rect x="332" y="254" width="256" height="78" rx="8" fill="#F8FAFC" stroke="#CBD5E1" />
        <text x="344" y="274" fill="#0F172A" font-size="11" font-weight="700">Services, Helpers &amp; Scheduler</text>
        <text x="344" y="291" fill="#475569" font-size="10">• DashboardWidgetService, ProfileImageService</text>
        <text x="344" y="306" fill="#475569" font-size="10">• AuditHelper, AvalisSyncHelper, SettingHelper</text>
        <text x="344" y="321" fill="#475569" font-size="10">• Artisan kas:auto-close (Daily 17:00)</text>

        {{-- Models Box --}}
        <rect x="332" y="344" width="256" height="56" rx="8" fill="#F1F5F9" stroke="#CBD5E1" />
        <text x="344" y="364" fill="#0F172A" font-size="11" font-weight="700">Eloquent Models (17 Model Inti)</text>
        <text x="344" y="381" fill="#475569" font-size="10">User, Nasabah, Transaksi, Brankas, Jurnal,</text>
        <text x="344" y="394" fill="#475569" font-size="10">DailyReport, PengajuanKas, SavingGoal, AuditLog</text>

        {{-- Database & Storage --}}
        <rect x="665" y="70" width="175" height="165" rx="12" fill="#F8FAFC" stroke="#0F172A" stroke-width="1.5" />
        <rect x="665" y="70" width="175" height="32" rx="10" fill="#1E293B" />
        <text x="680" y="91" fill="#FFFFFF" font-size="11.5" font-weight="700">Database RDBMS</text>
        <text x="680" y="122" fill="#334155" font-size="10.5" font-weight="600">SQLite (.env.example /</text>
        <text x="680" y="137" fill="#334155" font-size="10.5" font-weight="600">:memory: tests) / MySQL</text>
        <text x="680" y="160" fill="#475569" font-size="10">• 29 File Migrasi</text>
        <text x="680" y="176" fill="#475569" font-size="10">• personal_access_tokens</text>
        <text x="680" y="192" fill="#475569" font-size="10">• sessions &amp; notifications</text>
        <text x="680" y="208" fill="#475569" font-size="10">• Pessimistic row locks</text>

        {{-- Storage & CI/CD --}}
        <rect x="665" y="265" width="175" height="150" rx="12" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
        <rect x="665" y="265" width="175" height="32" rx="10" fill="#334155" />
        <text x="680" y="286" fill="#FFFFFF" font-size="11.5" font-weight="700">Storage &amp; CI/CD</text>
        <text x="680" y="316" fill="#334155" font-size="10.5" font-weight="600">• Public Disk (Foto GD)</text>
        <text x="680" y="335" fill="#475569" font-size="10">• Public Assets / Vite Build</text>
        <text x="680" y="354" fill="#475569" font-size="10">• GitHub Actions FTPS Deploy</text>
        <text x="680" y="373" fill="#475569" font-size="10">• APK Release Pointer</text>
        <text x="680" y="392" fill="#64748B" font-size="9.5">(hanbank.my.id / DomaiNesia)</text>

        {{-- Arrows --}}
        <line x1="250" y1="125" x2="313" y2="125" stroke="#475569" stroke-width="2" marker-end="url(#arrow)" />
        <line x1="250" y1="320" x2="313" y2="210" stroke="#059669" stroke-width="2" marker-end="url(#arrow-emerald)" />
        <line x1="605" y1="150" x2="663" y2="150" stroke="#475569" stroke-width="2" marker-end="url(#arrow)" />
        <line x1="605" y1="305" x2="663" y2="335" stroke="#475569" stroke-width="2" marker-end="url(#arrow)" />
    </svg>
</div>

<x-docs.heading id="komunikasi-komponen" level="2">Hubungan Frontend, Backend, API &amp; Database</x-docs.heading>
<ul>
    <li><strong>Web Request Flow:</strong> Permintaan HTTP menuju <code>routes/web.php</code> melewati middleware session Laravel dan <code>RoleGuard</code> (untuk rute terotentikasi). Controller melakukan query Eloquent / DB Transaction, mencatat <code>AuditHelper</code>, dan merender file view Blade di <code>resources/views/</code>.</li>
    <li><strong>API Request Flow:</strong> Permintaan HTTP menuju <code>routes/api.php</code> (prefix otomatis <code>/api</code> dari <code>bootstrap/app.php</code> serta pengecualian CSRF pada <code>api/*</code>) mengembalikan respons JSON terstruktur berisi field <code>success</code>, <code>message</code>, dan <code>data</code>.</li>
    <li><strong>Transaksional &amp; Concurrency:</strong> Operasi kritis seperti setoran, penarikan, pembatalan transaksi, dan persetujuan droping kas dibungkus dengan <code>DB::transaction(...)</code> dan <code>-&gt;lockForUpdate()</code> pada baris <code>Nasabah</code>, <code>Brankas</code>, <code>PengajuanKas</code>, dan <code>DailyReport</code> untuk mencegah <em>race condition</em> maupun pengurangan saldo atau persetujuan ganda.</li>
</ul>

<x-docs.heading id="integrasi-aplikasi-mobile" level="2">Integrasi Aplikasi Mobile (Flutter)</x-docs.heading>
<p>
    Aplikasi Flutter <code>ukk_bankmini</code> berkomunikasi langsung dengan REST API melalui <code>ApiClient</code> (singleton Dio dengan timeout koneksi 12 detik &amp; baca 15 detik). Setiap permintaan secara otomatis disisipi header <code>Accept: application/json</code> dan <code>Authorization: Bearer &lt;token&gt;</code> oleh <code>AuthInterceptor</code>.
</p>
<p>
    Pada mode rilis (<code>kReleaseMode</code>), <code>ApiConstants.defaultBaseUrl</code> menunjuk ke <code>https://hanbank.my.id/api</code> (kecuali di-override menggunakan <code>--dart-define=BASE_URL=...</code> atau URL kustom di <code>SessionStorage</code>), sedangkan pada mode pengembangan otomatis memilih <code>http://10.0.2.2:8000/api</code> untuk Android Emulator atau <code>http://127.0.0.1:8000/api</code> untuk desktop/web.
</p>

<x-docs.heading id="pipeline-deployment" level="2">Deployment &amp; Lingkungan Produksi</x-docs.heading>
<p>
    File <code>.github/workflows/deploy.yml</code> mendefinisikan pipeline CI/CD otomatis saat <em>push</em> ke branch <code>main</code> (atau eksekusi manual <code>workflow_dispatch</code>). Pipeline menjalankan PHP 8.4, <code>composer install --no-dev --optimize-autoloader</code>, <code>php artisan route:cache</code>, build frontend <code>npm ci &amp;&amp; npm run build</code> dengan Node.js 22, memisahkan folder <code>public/</code> dan kode inti untuk diunggah via FTPS ke hosting DomaiNesia (sejalan dengan logika path <code>public_html</code> pada <code>bootstrap/app.php</code>).
</p>

