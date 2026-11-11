<x-docs.heading id="dashboard-loket-teller" level="2">Dashboard Loket Teller &amp; Peringatan Harian</x-docs.heading>
<p>
    Petugas Teller bertugas melayani seluruh transaksi nasabah secara tatap muka di loket serta menjaga keseimbangan kas fisik loket. Saat masuk ke <code>/teller/dashboard</code>, Teller disuguhi informasi posisi kas harian (<code>brankas</code>), metrik transaksi hari ini, kotak peringatan operasional otomatis (seperti pengingat tutup kas belum selesai atau status pengajuan droping kas), serta fitur pencarian &amp; pemindaian QR nasabah.
</p>
<x-docs.callout type="info" title="Kebijakan Privasi Layar Loket Teller">
    Saat <code>/teller/dashboard</code> dibuka tanpa kata kunci pencarian atau hasil scan QR, sistem <strong>tidak menampilkan daftar seluruh siswa</strong> di layar utama demi menjaga privasi saldo nasabah dan fokus antrean loket. Hanya nasabah yang dicari (NIS / No Rekening / Nama) atau dipindai QR-nya yang ditampilkan.
</x-docs.callout>

<x-docs.heading id="prosedur-pelayanan-nasabah" level="2">Prosedur Pelayanan Nasabah &amp; Scan QR</x-docs.heading>
<x-docs.steps>
    <x-docs.step number="1" title="Scan QR Kartu Nasabah atau Pencarian Manual">
        <p>Klik tombol <strong>Scan Cepat QR Siswa</strong> pada dashboard (atau buka menu <strong>Scan QR</strong> di <code>/teller/scan</code>) untuk memindai QR Code dari aplikasi HanBank Mobile siswa menggunakan pustaka kamera <code>html5-qrcode</code>. Sebagai alternatif, ketik NIS, nomor rekening, atau nama siswa pada kolom pencarian.</p>
    </x-docs.step>
    <x-docs.step number="2" title="Buka Formulir Pelayanan &amp; Verifikasi Status">
        <p>Pilih nasabah untuk masuk ke formulir <code>/teller/transaksi/{id}</code>. Pastikan status rekening adalah <code>aktif</code>. Apabila rekening berstatus <code>nonaktif</code>, <code>alumni</code>, atau dibekukan darurat, sistem akan memblokir proses transaksi.</p>
    </x-docs.step>
    <x-docs.step number="3" title="Verifikasi Kunjungan &amp; Buka Buku Tabungan">
        <p>Catat kunjungan siswa pada hari berjalan apabila diperlukan melalui panel kunjungan atau tombol verifikasi kunjungan (<code>/teller/verifikasi-kunjungan</code>). Teller juga dapat meninjau maupun mencetak buku tabungan nasabah lewat <code>/buku-tabungan/{id}</code>.</p>
    </x-docs.step>
</x-docs.steps>

<x-docs.heading id="prosedur-setoran-dan-penarikan" level="2">Proses Setoran, Penarikan &amp; Cetak Slip</x-docs.heading>
<ul>
    <li><strong>Proses Setoran Tunai (Kode <code>101</code>):</strong>
        Masukkan nominal setoran minimal <strong>Rp 5.000</strong> (pada form web) beserta jenis layanan (default <code>Tabungan</code>). Terima uang fisik dari nasabah, klik proses transaksi, lalu cetak slip resmi melalui tombol <strong>Cetak Slip Setoran</strong> (<code>/teller/slip-setor/{id}</code>).</li>
    <li><strong>Proses Penarikan Tunai (Kode <code>201</code>):</strong>
        Sistem menampilkan <strong>Saldo Tersedia (Efektif)</strong> setelah dikurangi minimal saldo mengendap (bawaan Rp 10.000). Teller wajib memeriksa kecukupan saldo tersedia nasabah, kecukupan uang tunai di <code>brankas</code> loket, serta meminta nasabah memasukkan/mengonfirmasi <strong>PIN Keamanan 6 digit</strong> pada kolom <code>pin_nasabah</code>. Setelah sukses, cetak bukti lewat <code>/teller/slip-tarik/{id}</code>.</li>
    <li><strong>Ketahanan Koneksi (Idempotency):</strong> Jangan menyegarkan paksa halaman secara berulang saat sinyal lambat; form sudah dilindungi <code>idempotency_key</code> unik untuk mencegah pencatatan saldo ganda apabila pengiriman form sempat terputus.</li>
</ul>

<x-docs.heading id="pemeriksaan-riwayat-dan-void" level="2">Riwayat Transaksi &amp; Pembatalan (Void)</x-docs.heading>
<p>
    Melalui menu <strong>Riwayat Transaksi</strong> (<code>/teller/riwayat</code>), Teller dapat menyaring seluruh transaksi berdasarkan rentang tanggal, jenis transaksi, dan status (<code>sukses</code>, <code>batal</code>, <code>gagal</code>).
</p>
<p>
    Apabila terjadi kesalahan input nominal atau rekening pada transaksi yang sudah terlanjur berstatus <code>sukses</code>, Teller dapat menekan aksi <strong>Batalkan / Void</strong> dengan syarat:
</p>
<ul>
    <li>Mengisi alasan pembatalan secara jelas (wajib).</li>
    <li>Memasukkan <strong>PIN Otorisasi Supervisor</strong> sebagai kendali ganda.</li>
    <li>Sistem tidak menghapus transaksi lama, melainkan menandainya sebagai <code>batal</code> dan otomatis menerbitkan baris transaksi koreksi pembalik (<code>koreksi_setor</code> / <code>koreksi_tarik</code>) serta menyesuaikan ulang saldo nasabah dan brankas.</li>
</ul>

<x-docs.heading id="manajemen-kas-dan-tutup-kas" level="2">Pengajuan Droping Kas &amp; Tutup Kas Harian</x-docs.heading>
<ul>
    <li><strong>Pengajuan Droping Kas (<code>/teller/pengajuan-kas</code>):</strong> Apabila kas tunai loket mulai menipis sebelum melayani penarikan besar, ajukan nominal tambahan kas beserta alasannya. Tiket bernomor <code>PK-YYYYMMDD-XXX</code> akan langsung memicu notifikasi ke Supervisor untuk disetujui sebelum uang fisik berpindah.</li>
    <li><strong>Tutup Kas Harian / Daily Report (<code>/teller/daily-report</code>):</strong> Di akhir jam layanan, buka menu Tutup Kas untuk membandingkan saldo kas hasil hitung sistem melawan uang fisik yang dihitung Teller di laci loket. Jika terdapat selisih (<code>difference != 0</code>), Teller wajib mengisi kolom catatan klarifikasi sebelum mengirim laporan ke Supervisor, serta dapat mencetak formulir tutup kas via <code>/daily-report/cetak/{id}</code>.</li>
    <li><strong>Presensi Shift Avalis (<code>/teller/absensi-avalis</code>):</strong> Teller dapat memeriksa jadwal shift Pagi/Siang dan mencatat status kehadirannya pada halaman ini.</li>
</ul>
<x-docs.callout type="warning" title="Batasan Akses Teller">
    Petugas Teller tidak memiliki akses ke menu manajemen nasabah/pegawai (<code>/admin/*</code>), pusat persetujuan Supervisor (<code>/supervisor/*</code>), maupun laporan pembukuan manajerial (<code>/laporan/*</code>).
</x-docs.callout>

