<x-docs.heading id="arsitektur-roleguard" level="2">Arsitektur RoleGuard &amp; Normalisasi Role</x-docs.heading>
<p>
    Kontrol akses berbasis peran (RBAC) di sisi web ditegakkan oleh middleware <code>App\Http\Middleware\RoleGuard</code> yang didaftarkan dengan alias <code>'role'</code> pada <code>bootstrap/app.php</code>. Middleware menerima satu atau lebih parameter role yang dipisahkan koma (<code>role:admin,teller</code> atau gabungan string <code>"supervisor|accounting"</code>).
</p>
<p>
    Untuk menjaga kompatibilitas penamaan peran historis dan multi-divisi, <code>RoleGuard</code> menerapkan normalisasi otomatis sebagai berikut:
</p>
<ul>
    <li>Peran <code>supervisor</code> dan <code>accounting</code> dianggap saling setara (<code>supervisor</code> &harr; <code>accounting</code>).</li>
    <li>Peran <code>customer_service</code> dan <code>cs</code> dianggap saling setara (<code>customer_service</code> &harr; <code>cs</code>).</li>
    <li>Apabila pengguna belum terotentikasi, permintaan dialihkan ke <code>route('login')</code>; bila sudah login namun tidak memiliki izin, pengguna mendapatkan halaman error <code>403</code> beserta pengalihan ke dashboard perannya saat mengakses rute tidak sah.</li>
</ul>

<x-docs.heading id="aturan-separation-of-duties" level="2">Aturan Separation of Duties (SoD)</x-docs.heading>
<p>
    Inti tata kelola Bank Mini Sekolah di HANBANK adalah mencegah pemusatan wewenang pada satu petugas (*maker* tidak boleh sekaligus menjadi *checker* maupun pengelola master data keuangan):
</p>
<x-docs.code-block lang="php" title="Logika Pemisahan Wewenang di App\Http\Middleware\RoleGuard">
// 1. Admin &amp; Supervisor DILARANG mengakses rute transaksi loket Teller
if ($request-&gt;is('teller/transaksi*')) {
    if ($userRole === 'teller') {
        return $next($request);
    }
    abort(403, 'Akses Ditolak (Separation of Duties): Halaman transaksi kasir loket hanya diperuntukkan bagi petugas Teller...');
}

// 2. Role Teller DILARANG memegang fungsi Accounting / Supervisor
if ($userRole === 'teller' &amp;&amp; (in_array('accounting', $allowedRoles) || in_array('supervisor', $allowedRoles))) {
    abort(403, 'Akses Ditolak (Separation of Duties): Petugas Teller tidak berwenang mengakses halaman Accounting/Supervisor.');
}

// 3. Admin memiliki akses administratif penuh KECUALI rute teller/transaksi*
if ($userRole === 'admin') {
    return $next($request);
}
</x-docs.code-block>

<x-docs.callout type="info" title="Implikasi Nyata Aturan Admin &amp; Accounting">
    Karena blokir <code>teller/transaksi*</code> dievaluasi <strong>sebelum</strong> pengecekan <code>if ($userRole === 'admin')</code>, Administrator sekalipun tidak dapat memproses setoran/penarikan di loket teller. Sebaliknya, pada rute web non-transaksi seperti pengawasan dan laporan, Admin diizinkan masuk untuk kepentingan audit &amp; administrasi.
</x-docs.callout>

<x-docs.heading id="matriks-hak-akses-web" level="2">Matriks Hak Akses Rute Web Aktual</x-docs.heading>
<x-docs.table :headers="['Modul &amp; Rute Web', 'Admin', 'Teller', 'Supervisor / Accounting', 'Nasabah', 'CS']">
    <tr>
        <td><code>/admin/*</code> (Pegawai, Konfigurasi, Audit Log, Reset Password &amp; PIN)</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
    </tr>
    <tr>
        <td>Manajemen Nasabah (<code>/admin/data-nasabah*</code>, <code>/admin/tambah-nasabah</code>, dll.)</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
        <td class="font-bold text-emerald-700">Ya (<code>cs,admin</code>)</td>
    </tr>
    <tr>
        <td>Transaksi Loket Teller (<code>/teller/transaksi*</code>, Setor/Tarik/Void/Idempotency)</td>
        <td class="text-rose-600">Diblokir (SoD)</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Diblokir (SoD)</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
    </tr>
    <tr>
        <td>Loket Teller Lain (<code>/teller/dashboard</code>, Scan QR, Riwayat, Daily Report, Pengajuan Kas)</td>
        <td class="font-semibold text-amber-700">Bypass Admin*</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
    </tr>
    <tr>
        <td>Pengawasan &amp; Approval (<code>/supervisor/*</code>)</td>
        <td class="font-semibold text-amber-700">Bypass Admin*</td>
        <td class="text-rose-600">Diblokir (SoD)</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
    </tr>
    <tr>
        <td>Laporan Keuangan (<code>/laporan/jurnal</code>, <code>saldo-produk</code>, <code>tahunan</code>)</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Tidak</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
    </tr>
    <tr>
        <td>Buku Tabungan (<code>/buku-tabungan/{id}</code>, cetak buku &amp; slip)</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="font-bold text-emerald-700">Ya</td>
    </tr>
    <tr>
        <td>Portal Nasabah &amp; Lupa PIN (<code>/nasabah/dashboard</code>, <code>/nasabah/lupa-pin/*</code>)</td>
        <td class="font-semibold text-amber-700">Bypass Admin*</td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
        <td class="font-bold text-emerald-700">Ya</td>
        <td class="text-rose-600">Tidak</td>
    </tr>
    <tr>
        <td>CRUD Absensi &amp; Master Avalis Penuh (<code>/absensi-avalis/*</code>, <code>/avalis/petugas/*</code>)</td>
        <td class="font-bold text-emerald-700">Ya (Only Admin)</td>
        <td class="text-slate-500">Read/Check-in di <code>/teller/absensi-avalis</code></td>
        <td class="text-slate-500">Monitoring di <code>/supervisor/absensi-avalis</code></td>
        <td class="text-rose-600">Tidak</td>
        <td class="text-rose-600">Tidak</td>
    </tr>
</x-docs.table>
<p class="text-xs text-slate-500">
    *Catatan: Pada <code>RoleGuard.php</code> baris 61, user ber-role <code>admin</code> mendapat bypass umum ke seluruh rute web ber-middleware <code>role</code> selain <code>teller/transaksi*</code>; namun menu navigasi <code>&lt;x-navbar /&gt;</code> hanya menampilkan menu administratif milik Admin.
</p>

<x-docs.heading id="keterbatasan-rbac-api" level="2">Catatan RBAC pada Endpoint REST API</x-docs.heading>
<p>
    Berbeda dengan rute web, grup rute di <code>routes/api.php</code> (seperti <code>/api/petugas/*</code> dan <code>/api/supervisor/*</code>) hanya memasang middleware <code>auth:sanctum</code> tanpa middleware <code>role:...</code>. Di level controller API pun hanya <code>TellerApiController</code> dan <code>OperasionalKasApiController</code> yang membaca <code>$request-&gt;user()</code> secara langsung tanpa menolak keras silang-role di middleware. Selain itu, UI tambah/edit pegawai pada <code>AdminController::store/update</code> membatasi pilihan role pada validasi <code>in:admin,teller,supervisor</code> meskipun <code>RoleGuard</code> mendukung <code>accounting</code> dan <code>cs</code>.
</p>

