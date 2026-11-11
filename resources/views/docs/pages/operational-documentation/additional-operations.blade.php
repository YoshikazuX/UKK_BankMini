<x-docs.heading id="referensi-kode-transaksi" level="2">Referensi Kode Sandi &amp; Prosedur Transaksi</x-docs.heading>
<p>
    Setiap pencatatan transaksi tabungan dan pembatalan pada tabel <code>transaksi</code> (serta cetakan buku tabungan pada <code>BukuTabunganController</code>) menggunakan kode sandi baku berikut:
</p>
<x-docs.table :headers="['Kode Sandi', 'Jenis Transaksi di DB', 'Efek Saldo Nasabah &amp; Brankas', 'Penggunaan']">
    <tr>
        <td><code>101</code></td>
        <td><code>setor</code></td>
        <td class="text-emerald-700 font-semibold">+ Kredit Nasabah / + Debit Kas Brankas</td>
        <td>Setoran tunai tabungan siswa reguler di loket Teller Web atau Mobile POS.</td>
    </tr>
    <tr>
        <td><code>201</code></td>
        <td><code>tarik</code></td>
        <td class="text-rose-700 font-semibold">- Debit Nasabah / - Kredit Kas Brankas</td>
        <td>Penarikan tunai ber-PIN 6 digit yang menyisakan saldo minimal mengendap (bawaan Rp 10.000).</td>
    </tr>
    <tr>
        <td><code>102</code></td>
        <td><code>koreksi_tarik</code> (atau layanan setoran lain)</td>
        <td class="text-emerald-700 font-semibold">+ Pembalikan Masuk</td>
        <td>Mutasi pembalik ketika transaksi penarikan sebelumnya dibatalkan (<em>void</em>) ber-PIN supervisor.</td>
    </tr>
    <tr>
        <td><code>202</code></td>
        <td><code>koreksi_setor</code> (atau layanan keluar lain)</td>
        <td class="text-rose-700 font-semibold">- Pembalikan Keluar</td>
        <td>Mutasi pembalik ketika transaksi setoran sebelumnya dibatalkan (<em>void</em>) ber-PIN supervisor.</td>
    </tr>
    <tr>
        <td><code>301</code></td>
        <td><code>admin</code> / biaya administrasi</td>
        <td>Penyesuaian administrasi buku</td>
        <td>Dicatat sesuai parameter biaya administrasi buku pada konfigurasi sistem.</td>
    </tr>
</x-docs.table>

<x-docs.heading id="panduan-saving-goals" level="2">Pengelolaan Target Tabungan (Saving Goals)</x-docs.heading>
<p>
    Fitur <strong>Saving Goals</strong> tersedia pada aplikasi mobile siswa (<code>SavingGoalsScreen</code> terhubung ke <code>/api/nasabah/goals</code>) untuk membantu siswa merencanakan target dana pendidikan atau kebutuhan sekolah:
</p>
<ol>
    <li><strong>Buat Target Baru:</strong> Tentukan nama target (mis. <em>Tabungan PKL</em>), nominal <code>target_amount</code>, ikon kategori, dan tenggat waktu opsional (<code>deadline</code>).</li>
    <li><strong>Perbarui Progres Tabungan:</strong> Perbarui nilai <code>current_amount</code> secara berkala seiring setoran Anda di loket. Saat <code>current_amount &gt;= target_amount</code>, status <code>is_completed</code> otomatis berubah menjadi selesai (100%).</li>
    <li><strong>Hapus Target:</strong> Target yang sudah tercapai atau batal dapat dihapus tanpa memengaruhi saldo riil di rekening utama Anda.</li>
</ol>

<x-docs.heading id="panduan-sistem-lencana" level="2">Sistem Lencana Prestasi Menabung (Badges)</x-docs.heading>
<p>
    Pada layar <strong>Lencana Prestasi</strong> (<code>BadgesScreen</code>) di aplikasi Android, siswa dapat memantau 6 pencapaian literasi finansial yang dievaluasi real-time dari server (<code>GET /api/nasabah/badges</code>):
</p>
<ul>
    <li><strong>Berdasarkan Konsistensi Menabung:</strong> <em>Langkah Pertama</em> (1x setor sukses), <em>Penabung Rutin</em> (5x setor sukses), dan <em>Master Menabung</em> (10x setor sukses).</li>
    <li><strong>Berdasarkan Akumulasi Saldo:</strong> <em>Tabungan Rp 100K</em> (saldo minimal Rp 100.000) dan <em>Tabungan Rp 500K</em> (saldo minimal Rp 500.000).</li>
    <li><strong>Status Akun:</strong> <em>Nasabah Terverifikasi</em> (otomatis terbuka bagi nasabah terdaftar).</li>
</ul>

<x-docs.heading id="troubleshooting-kendala-umum" level="2">Troubleshooting &amp; Penanganan Kendala Umum</x-docs.heading>
<x-docs.table :headers="['Kendala / Pesan Error', 'Penyebab Utama di Sistem', 'Langkah Penanganan Resmi']">
    <tr>
        <td class="font-semibold text-slate-900">Penarikan tunai ditolak padahal total saldo cukup</td>
        <td>Sisa saldo pasca penarikan jatuh di bawah <code>saldo_mengendap_min</code> (bawaan Rp 10.000), atau uang tunai di <code>brankas</code> hari ini lebih kecil dari nominal tarik.</td>
        <td>Kurangi nominal tarik sesuai batas <strong>Saldo Tersedia</strong>, atau jika kas brankas kurang, minta Teller mengajukan <strong>Droping Kas</strong> kepada Supervisor.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Rekening nasabah tidak bisa setor / tarik</td>
        <td>Status rekening masih <code>nonaktif</code> (siswa baru kelas X / pasca <em>Emergency Freeze</em>) atau berstatus <code>alumni</code>.</td>
        <td>Hubungi Administrator untuk memverifikasi dan mengaktifkan kembali status rekening di menu Manajemen Nasabah.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Akses halaman ditolak dengan Error 403</td>
        <td>Terhalang aturan <em>Separation of Duties</em> <code>RoleGuard</code> (misal Admin/Supervisor membuka menu kasir Teller atau Teller membuka menu Supervisor).</td>
        <td>Gunakan akun sesuai fungsi tugasnya masing-masing; jangan mengakses URL di luar peran.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Kamera Scan QR tidak muncul di browser loket</td>
        <td>Izin kamera browser ditolak, koneksi bukan HTTPS/localhost, atau fitur <code>scanner_kamera_aktif</code> dimatikan di konfigurasi Admin.</td>
        <td>Berikan izin kamera di browser, pastikan URL aman, cek Konfigurasi Sistem Global, atau gunakan input pencarian manual NIS / No Rekening.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Lupa PIN keamanan saat hendak menarik dana</td>
        <td>Nasabah lupa PIN 6 digit atau akun nasabah terblokir PIN salah.</td>
        <td>Ajukan reset PIN via aplikasi/portal &rarr; minta Admin menyetujui permohonan di menu Permohonan Reset &rarr; buat PIN baru mandiri dengan token reset.</td>
    </tr>
    <tr>
        <td class="font-semibold text-slate-900">Gagal unggah foto profil petugas</td>
        <td>Ukuran file &gt; 2 MB, ekstensi bukan JPG/PNG/WEBP, resolusi di luar 50–4000 px, atau gagal validasi MIME <code>ProfileImageService</code>.</td>
        <td>Gunakan foto JPG/PNG bersih di bawah 2 MB dengan dimensi wajar dan ulangi pengunggahan.</td>
    </tr>
</x-docs.table>

