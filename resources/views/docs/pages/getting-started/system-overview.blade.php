<x-docs.heading id="gambaran-umum-sistem" level="2">Gambaran Umum Sistem</x-docs.heading>
<p>
    HANBANK beroperasi sebagai sistem informasi perbankan mikro sekolah (<em>core mini-banking ecosystem</em>) yang berpusat pada backend Laravel. Seluruh kebenaran data saldo, catatan transaksi, status persetujuan kas, dan pengaturan sistem tersimpan terpusat pada basis data server, sehingga aplikasi web maupun aplikasi Android membaca dan memodifikasi keadaan data yang sama.
</p>

<x-docs.heading id="pengguna-sistem" level="2">Pengguna Sistem &amp; Peran</x-docs.heading>
<p>
    Berdasarkan kode pada <code>RoleGuard.php</code>, <code>DatabaseSeeder.php</code>, <code>AdminController.php</code>, serta model <code>User</code> dan <code>Nasabah</code>, terdapat empat peran utama yang aktif digunakan pada operasional sistem, disertai beberapa variasi peran pendukung yang dikenali oleh lapisan otorisasi:
</p>

<x-docs.table :headers="['Peran (Role)', 'Tabel Autentikasi', 'Kanal Akses Utama', 'Tanggung Jawab Aktual']">
    <tr>
        <td class="font-bold text-slate-900">Admin (Administrator)</td>
        <td><code>users</code> (<code>role = admin</code>)</td>
        <td>Web Dashboard &amp; Mobile Status View</td>
        <td>Mengelola data master nasabah (tambah/edit/import/soft delete alumni), mengelola akun pegawai (Admin/Teller/Supervisor), menyetujui reset password &amp; PIN, mengelola absensi/master avalis, melihat audit log, dan mengatur <code>pengaturan_sistem</code>. Diblokir dari endpoint transaksi kasir loket oleh <code>RoleGuard</code>.</td>
    </tr>
    <tr>
        <td class="font-bold text-slate-900">Teller (Petugas Loket)</td>
        <td><code>users</code> (<code>role = teller</code>)</td>
        <td>Web Loket &amp; Mobile Teller POS</td>
        <td>Memverifikasi kunjungan siswa, memproses setoran tunai &amp; penarikan tunai, mencetak slip dan buku tabungan, melakukan pembatalan (<em>void</em>) transaksi berotorisasi PIN supervisor, mengajukan droping kas, mengisi <em>daily report</em> tutup kas, dan melakukan check-in shift avalis.</td>
    </tr>
    <tr>
        <td class="font-bold text-slate-900">Supervisor / Accounting</td>
        <td><code>users</code> (<code>role = supervisor</code> / <code>accounting</code>)</td>
        <td>Web Eksekutif &amp; Mobile Supervisor Shell</td>
        <td>Meninjau dan memutus tiket pengajuan droping kas &amp; tutup kas harian (Setujui / Hold / Tolak), memantau kinerja dan selisih kas per loket teller, memeriksa laporan keuangan/jurnal, serta memantau presensi avalis. <code>RoleGuard</code> menormalisasi peran <code>accounting</code> setara dengan <code>supervisor</code>.</td>
    </tr>
    <tr>
        <td class="font-bold text-slate-900">Nasabah (Siswa / Guru)</td>
        <td><code>nasabah</code> (API &amp; mobile) / <code>users</code> (<code>role = nasabah</code> untuk portal web)</td>
        <td>Aplikasi Mobile Flutter &amp; Portal Web</td>
        <td>Memeriksa saldo efektif (dikurangi saldo mengendap), melihat mutasi &amp; grafik 6 bulan, menggunakan kartu QR digital di loket, mengelola <em>Saving Goals</em>, memantau <em>Badges</em>, membekukan rekening darurat, mengajukan lupa PIN, dan mengubah password/PIN.</td>
    </tr>
    <tr>
        <td class="font-bold text-slate-900">Customer Service (CS)</td>
        <td><code>users</code> (<code>role = customer_service</code> / <code>cs</code>)</td>
        <td>Rute Web Gabungan (<code>cs,admin</code> / <code>admin,teller,cs</code>)</td>
        <td>Diakui oleh <code>RoleGuard</code> (normalisasi <code>customer_service</code> &harr; <code>cs</code>) untuk akses pembacaan/pengelolaan nasabah dan buku tabungan apabila akun ber-role tersebut dibuat langsung di database (form <code>AdminController</code> membatasi pilihan UI menjadi <code>admin, teller, supervisor</code>).</td>
    </tr>
</x-docs.table>

<x-docs.heading id="kanal-akses" level="2">Kanal Akses Web dan Mobile</x-docs.heading>
<ul>
    <li><strong>Aplikasi Web Operasional (Laravel Blade):</strong> Ditujukan terutama bagi operasional di komputer loket sekolah (Admin, Teller, Supervisor), lengkap dengan pemindai kamera QR <code>html5-qrcode</code>, tampilan cetak HTML terformat untuk slip setoran/penarikan, buku tabungan, jurnal, dan laporan harian, serta portal informasi publik (<code>/</code>, <code>/download</code>, <code>/docs</code>).</li>
    <li><strong>Aplikasi Android Flutter (<code>ukk_bankmini</code> v2.4.0):</strong> Menggunakan satu gerbang login universal (<code>POST /api/auth/login</code>) yang mendeteksi apakah kredensial milik nasabah atau petugas, kemudian merutekan pengguna ke shell antarmuka masing-masing (<code>MainNavigation</code> untuk Nasabah, <code>TellerMainScreen</code> untuk Teller POS, <code>SupervisorMainScreen</code> untuk Supervisor, dan <code>AdminMainScreen</code> untuk ringkasan kesehatan server &amp; presensi).</li>
</ul>

<x-docs.heading id="fitur-utama-platform" level="2">Fitur Utama Platform</x-docs.heading>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-5">
    <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
        <div class="font-bold text-slate-900 text-sm">Transaksi Kasir &amp; Akuntansi</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">Setoran (kode <code>101</code>) dan penarikan (kode <code>201</code>) terhubung ke entri <code>jurnal</code> debit-kredit, pembaruan mutasi <code>brankas</code> harian, <em>idempotency key</em> anti-duplikasi, pembatalan <em>void</em> dengan jurnal pembalik, dan perlindungan saldo mengendap minimal Rp 10.000.</p>
    </div>
    <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
        <div class="font-bold text-slate-900 text-sm">Kontrol Kas &amp; Tutup Kas Harian</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">Siklus operasional kas dimulai dari tiket pengajuan droping kas teller, otorisasi supervisor dengan penguncian baris (<code>lockForUpdate</code>), hingga pelaporan tutup kas harian (<code>daily_reports</code>) dan otomasi <code>kas:auto-close</code> pada pukul 17:00.</p>
    </div>
    <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
        <div class="font-bold text-slate-900 text-sm">Ekosistem Digital Siswa</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">QR Card payload berbasis JSON, pengelolaan target menabung (<code>saving_goals</code>) tanpa mengunci saldo riil, evaluasi 6 tingkat lencana prestasi secara dinamis dari riwayat setoran, serta fitur keamanan pembekuan akun instan.</p>
    </div>
    <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
        <div class="font-bold text-slate-900 text-sm">Operasional Shift &amp; Kepatuhan</div>
        <p class="text-xs text-slate-600 mt-1 mb-0">Sinkronisasi otomatis data petugas teller ke master <code>avalis</code> dan jadwal presensi <em>Shift Pagi / Siang</em> lewat <code>AvalisSyncHelper</code>, serta perekaman jejak perubahan ke <code>audit_logs</code>.</p>
    </div>
</div>

