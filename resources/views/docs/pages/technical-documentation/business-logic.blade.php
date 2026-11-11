<x-docs.heading id="logika-pengelolaan-nasabah" level="2">1. Pengelolaan Nasabah &amp; Penomoran Rekening</x-docs.heading>
<p>
    <code>NasabahController</code> dan <code>AdminController</code> menangani siklus hidup nasabah mulai dari pendaftaran tunggal hingga impor massal:
</p>
<ul>
    <li><strong>Generator Nomor Rekening Dinamis (<code>generateNoRekening</code>):</strong> Membaca pola dari <code>pengaturan_sistem.format_no_rekening</code> dan tahun ajaran aktif (<code>2026/2027</code> &rarr; <code>26</code>), memetakan nama jurusan ke kode resmi via <code>TerbilangHelper::getKodeJurusan()</code> (<code>RPL</code>, <code>TKJ</code>, <code>AKL</code>, <code>LPS</code>, <code>BR</code>, <code>BD</code>, <code>MP</code>, <code>DKV</code>, <code>GK</code>), lalu menyusun urutan numerik 3 digit berikutnya (misal <code>26-RPL-001</code> atau format <code>JURUSAN-NIS</code>).</li>
    <li><strong>Import Massal CSV &amp; XLSX Native (<code>importCsv</code>):</strong> Mendukung file <code>.csv</code> dan <code>.xlsx</code> (dibaca langsung menggunakan <code>ZipArchive</code> + XML parser <code>parseXlsxNative()</code> tanpa dependensi library Excel eksternal). Kelas tingkat X otomatis berstatus <code>nonaktif</code> sampai diverifikasi aktivasi; siswa lama yang lulus berstatus <code>alumni</code>.</li>
    <li><strong>Perlindungan Penghapusan &amp; Soft Delete (<code>destroy</code> &amp; <code>restore</code>):</strong> Rekening dengan <code>status === 'aktif'</code> diblokir agar tidak terhapus. Akun nonaktif atau alumni dihapus secara lunak (<code>SoftDeletes</code> mengisi <code>deleted_at</code>) dan dapat dipulihkan kembali dari filter <code>status=terhapus</code> agar seluruh catatan ledger tetap utuh.</li>
</ul>

<x-docs.heading id="logika-rekening-tabungan" level="2">2. Pengelolaan Rekening &amp; Saldo Mengendap</x-docs.heading>
<p>
    HANBANK membedakan antara <strong>Saldo Total</strong> (<code>nasabah.saldo</code>) dan <strong>Saldo Tersedia (Efektif)</strong> yang dapat ditarik tunai. Nilai minimal saldo mengendap diambil secara dinamis melalui <code>SettingHelper::getSettings()-&gt;saldo_mengendap_min</code> (nilai bawaan <strong>Rp 10.000</strong>).
</p>
<x-docs.code-block lang="php" title="Rumus Saldo Efektif di TellerController &amp; ProfilNasabahController">
$saldoMengendap = \App\Helpers\SettingHelper::getSettings()-&gt;saldo_mengendap_min ?? 10000;
$saldoEfektif = max(0, (int) $nasabah-&gt;saldo - (int) $saldoMengendap);
// Penarikan ditolak jika: $nominal &gt; $saldoEfektif (sisa saldo tidak boleh kurang dari $saldoMengendap)
</x-docs.code-block>
<p>
    Rekening dengan status <code>nonaktif</code>, <code>alumni</code>, atau <code>terblokir</code> dilarang melakukan transaksi penarikan maupun setoran di loket teller (<code>TellerController::formTransaksi</code> dan <code>prosesTransaksi</code>), dan siswa dapat membekukan rekeningnya sendiri secara darurat dari aplikasi mobile (<code>POST /api/nasabah/emergency-freeze</code>).
</p>

<x-docs.heading id="logika-setoran" level="2">3. Setoran Tunai &amp; Jurnal Ganda</x-docs.heading>
<p>
    Setoran tunai dicatat dengan kode sandi <code>101</code> (atau <code>102</code> untuk setoran jenis layanan lain). Saat teller memproses setoran dalam <code>DB::transaction()</code>:
</p>
<ol>
    <li><strong>Validasi Nominal Minimum:</strong> Pada web <code>TellerController::prosesTransaksi</code> minimal setoran adalah <code>Rp 5.000</code> (pada API <code>TellerApiController::setor</code> minimal <code>Rp 1.000</code>).</li>
    <li><strong>Penguncian &amp; Penambahan Saldo:</strong> Baris nasabah dikunci dengan <code>lockForUpdate()</code>, kemudian <code>saldo</code> bertambah sebesar <code>nominal</code>.</li>
    <li><strong>Pembukuan Kas &amp; Jurnal:</strong> Baris <code>transaksi</code> dibuat berstatus <code>sukses</code>; tabel <code>brankas</code> hari ini menambah <code>masuk</code> &amp; <code>total</code>; dan tabel <code>jurnal</code> mencatat <code>debit = nominal</code> beserta keterangan setoran nasabah.</li>
</ol>

<x-docs.heading id="logika-penarikan" level="2">4. Penarikan Tunai &amp; Penguncian Kas Fisik</x-docs.heading>
<p>
    Penarikan tunai berkode sandi <code>201</code> melewati tiga lapis penjagaan sebelum saldo berkurang:
</p>
<ol>
    <li><strong>Verifikasi PIN 6 Digit:</strong> Di loket web, input <code>pin_nasabah</code> wajib cocok dengan <code>Hash::check</code> terhadap <code>nasabah.pin_keamanan</code> (atau PIN default <code>123456</code>). Di API <code>TellerApiController::tarik</code>, verifikasi PIN hanya dieksekusi apabila field <code>pin</code> dikirim.</li>
    <li><strong>Validasi Saldo Tersedia:</strong> Jika <code>($nasabah-&gt;saldo - $nominal) &lt; $saldoMengendap</code>, penarikan ditolak dan sistem menandai upaya gagal jika dicatat.</li>
    <li><strong>Validasi Likuiditas Brankas:</strong> Sistem mengunci baris <code>brankas</code> tanggal berjalan dengan <code>lockForUpdate()</code>. Jika <code>$brankas-&gt;total &lt; $nominal</code>, penarikan ditolak dengan pesan kas fisik loket tidak mencukupi (teller diarahkan mengajukan droping kas ke supervisor). Apabila lolos, <code>nasabah.saldo</code> berkurang, <code>brankas.keluar</code> bertambah, <code>brankas.total</code> berkurang, dan entri <code>jurnal</code> (<code>kredit = nominal</code>) tercipta.</li>
</ol>

<x-docs.heading id="logika-transaksi-void" level="2">5. Transaksi, Idempotency Key &amp; Pembatalan (Void)</x-docs.heading>
<ul>
    <li><strong>Pencegahan Duplikasi (<code>idempotency_key</code>):</strong> Form transaksi web membangkitkan UUID unik pada setiap pemuatan halaman dan mendukung endpoint polling <code>GET /teller/transaksi/status/{key}</code>. Apabila koneksi putus dan form dikirim ulang dengan key yang sama, constraint unik kolom <code>idempotency_key</code> dan pengecekan pra-eksekusi mengembalikan referensi transaksi awal tanpa menggandakan saldo.</li>
    <li><strong>Pembatalan Transaksi (<code>voidTransaksi</code>):</strong> Teller hanya dapat membatalkan transaksi yang berstatus <code>sukses</code> jika memasukkan otorisasi PIN supervisor dan alasan batal. Sistem mengubah status transaksi asal menjadi <code>batal</code>, membuat <strong>transaksi pembalik baru</strong> (<code>koreksi_setor</code> kode <code>202</code> atau <code>koreksi_tarik</code> kode <code>102</code>) dengan <code>ref_transaksi_id</code> menunjuk transaksi asal, membalikkan saldo nasabah &amp; kas brankas, membuat jurnal pembalik, dan menulis audit log.</li>
</ul>

<x-docs.heading id="logika-manajemen-pegawai" level="2">6. Manajemen Pegawai &amp; Jejak Audit</x-docs.heading>
<p>
    <code>AdminController</code> mengelola penambahan, pembaruan, pengaktifan/penonaktifan status, serta penghapusan pegawai (dengan larangan menghapus akun Admin yang sedang login sendiri). Perubahan nama/kontak/status petugas yang ber-role <code>teller</code> memicu sinkronisasi master avalis. Seluruh operasi sensitif dicatat ke <code>audit_logs</code> lewat <code>AuditHelper::log()</code> yang menyensor key rahasia seperti <code>password</code>, <code>pin</code>, dan <code>otp_code</code> menjadi <code>[REDACTED]</code>.
</p>

<x-docs.heading id="logika-manajemen-shift" level="2">7. Manajemen Shift &amp; Sinkronisasi Avalis</x-docs.heading>
<p>
    <code>AvalisSyncHelper::autoSync()</code> membaca seluruh <code>User</code> ber-role <code>teller</code> dan <code>avalis</code> yang aktif, memastikan profilnya ada di tabel <code>avalis</code>, lalu otomatis mempopulasikan lembar absensi harian di <code>absensi_avalis</code> untuk tanggal hari ini. Pembagian shift dilakukan deterministik berdasarkan urutan daftar: petugas indeks genap masuk <code>Shift Pagi (07.30 - 11.30)</code> dan indeks ganjil masuk <code>Shift Siang (11.30 - 15.30)</code>, dengan opsi ubah shift, bulk tambah petugas, dan check-in mobile.
</p>

<x-docs.heading id="logika-saving-goals" level="2">8. Target Tabungan (Saving Goals)</x-docs.heading>
<p>
    Fitur <code>SavingGoalApiController</code> memungkinkan nasabah membuat beberapa target menabung (misalnya tabungan PKL atau perlengkapan sekolah) yang tersimpan di tabel <code>saving_goals</code>. Alokasi <code>current_amount</code> bersifat pelacakan target progres mandiri per goal: saat <code>current_amount &gt;= target_amount</code>, flag <code>is_completed</code> otomatis bernilai <code>true</code>. Mekanisme ini bersifat <em>goal tracker</em> (tidak memotong atau mengunci saldo riil pada kolom <code>nasabah.saldo</code>).
</p>

<x-docs.heading id="logika-badges" level="2">9. Sistem Lencana (Dynamic Badges)</x-docs.heading>
<p>
    Lencana prestasi tidak disimpan dalam tabel statis tersendiri, melainkan <strong>dievaluasi secara dinamis saat runtime</strong> oleh <code>ProfilNasabahController::badges()</code> berdasarkan jumlah transaksi setor berstatus <code>sukses</code>, saldo aktual nasabah, dan kepatuhan verifikasi. Terdapat 6 lencana berjenjang:
</p>
<x-docs.table :headers="['ID Lencana', 'Nama Tampilan', 'Tier', 'Syarat Evaluasi Kode Aktual']">
    <tr><td><code>1</code></td><td>Langkah Pertama</td><td>Bronze</td><td>Minimal <code>1</code> transaksi setor berstatus <code>sukses</code>.</td></tr>
    <tr><td><code>2</code></td><td>Penabung Rutin</td><td>Silver</td><td>Minimal <code>5</code> transaksi setor berstatus <code>sukses</code>.</td></tr>
    <tr><td><code>3</code></td><td>Master Menabung</td><td>Gold</td><td>Minimal <code>10</code> transaksi setor berstatus <code>sukses</code>.</td></tr>
    <tr><td><code>4</code></td><td>Tabungan Rp 100K</td><td>Silver</td><td><code>nasabah.saldo &gt;= 100000</code>.</td></tr>
    <tr><td><code>5</code></td><td>Tabungan Rp 500K</td><td>Gold</td><td><code>nasabah.saldo &gt;= 500000</code>.</td></tr>
    <tr><td><code>6</code></td><td>Nasabah Terverifikasi</td><td>Emerald</td><td>Selalu terbuka bagi akun terotentikasi (<code>unlocked: true</code>).</td></tr>
</x-docs.table>

<x-docs.heading id="logika-dashboard-laporan" level="2">10. Dashboard Widgets &amp; Pelaporan Keuangan</x-docs.heading>
<p>
    <code>DashboardWidgetService</code> menyediakan metrik terstandarisasi per peran (ringkasan total nasabah aktif/nonaktif/alumni, statistik pegawai, tiket reset tanpa mengekspos OTP/token, status brankas, dan peringatan antrean tutup kas teller). Untuk pelaporan keuangan resmi, <code>LaporanKeuanganController</code> dan <code>SupervisorController</code> menyajikan 4 jenis laporan:
</p>
<ul>
    <li><strong>Buku Jurnal (<code>/laporan/jurnal</code>):</strong> Mutasi debit-kredit kronologis dari tabel <code>jurnal</code>.</li>
    <li><strong>Laporan Saldo per Produk/Kategori (<code>/laporan/saldo-produk</code>):</strong> Agregasi saldo berdasarkan jurusan/kategori siswa vs guru/karyawan.</li>
    <li><strong>Rekapitulasi Tahunan (<code>/laporan/tahunan</code>):</strong> Ringkasan arus masuk/keluar tahunan dan versi cetak PDF/HTML siap arsip.</li>
    <li><strong>Buku Harian Supervisor (<code>/supervisor/cetak-harian</code>):</strong> Ringkasan transaksi sukses, total setoran/penarikan, posisi brankas, dan status daily report pada tanggal tertentu.</li>
</ul>

