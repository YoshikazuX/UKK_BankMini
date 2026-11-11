<x-docs.heading id="dashboard-eksekutif-supervisor" level="2">Dashboard Pengawasan Operasional</x-docs.heading>
<p>
    Peran <strong>Supervisor</strong> (dan <strong>Accounting</strong> yang oleh <code>RoleGuard</code> dinormalisasi memiliki cakupan pengawasan setara) bertanggung jawab penuh atas fungsi pemeriksaan, otorisasi kas, rekonsiliasi selisih tutup kas, pemantauan kinerja loket Teller, serta pelaporan keuangan sekolah. Pada <code>/supervisor/dashboard</code>, Supervisor memantau 4 KPI utama: antrean pengajuan droping kas, antrean daily report tutup kas, ringkasan kinerja kas loket, serta notifikasi pekerjaan.
</p>

<x-docs.heading id="pusat-persetujuan-dan-otorisasi" level="2">Pusat Persetujuan (Setujui / Hold / Tolak)</x-docs.heading>
<p>
    Menu <strong>Pusat Persetujuan</strong> (<code>/supervisor/approval-center</code>), <strong>Pengajuan Droping Kas</strong> (<code>/supervisor/pengajuan-kas</code>), dan <strong>Daily Report Tutup Kas</strong> (<code>/supervisor/daily-report</code>) merupakan area kerja otorisasi Supervisor:
</p>
<x-docs.steps>
    <x-docs.step number="1" title="Periksa Detail Tiket &amp; Posisi Brankas">
        <p>Klik detail tiket pengajuan kas atau daily report (<code>/supervisor/pengajuan-kas/{id}</code> atau <code>/supervisor/daily-report/{id}</code>) untuk meninjau nominal yang diminta/dilaporkan, catatan Teller, dan posisi saldo kas fisik brankas saat ini.</p>
    </x-docs.step>
    <x-docs.step number="2" title="Tentukan Keputusan Otorisasi">
        <p>Tersedia tiga jenis keputusan yang dicatat ke <code>approval_histories</code> dan diamankan dengan <code>lockForUpdate()</code> terhadap kemungkinan klik ganda:</p>
        <ul>
            <li><strong>Setujui (Approved / Disetujui):</strong> Pada pengajuan kas, otomatis menambah saldo brankas masuk &amp; total serta membuat jurnal debit. Pada daily report, menutup status pelaporan harian menjadi disetujui.</li>
            <li><strong>Tahan / Klarifikasi (Hold):</strong> Menandai tiket menunggu penjelasan atau penghitungan ulang Teller. <em>Catatan Supervisor wajib diisi.</em></li>
            <li><strong>Tolak (Rejected / Ditolak):</strong> Menolak tiket tanpa mengubah brankas. <em>Alasan penolakan wajib diisi.</em></li>
        </ul>
    </x-docs.step>
    <x-docs.step number="3" title="Audit Riwayat Otorisasi Checker">
        <p>Seluruh riwayat keputusan sebelumnya dapat ditinjau pada menu <strong>Riwayat Otorisasi Checker</strong> (<code>/supervisor/riwayat-approval</code>) untuk keperluan pertanggungjawaban periodik.</p>
    </x-docs.step>
</x-docs.steps>

<x-docs.heading id="monitoring-aktivitas-dan-teller" level="2">Monitoring Teller, Transaksi &amp; Absensi Avalis</x-docs.heading>
<ul>
    <li><strong>Monitoring Kinerja Teller (<code>/supervisor/monitoring-teller</code>):</strong> Menampilkan rekapitulasi performa per petugas teller dengan pemisahan metrik antara transaksi berstatus <code>sukses</code> dan transaksi yang dibatalkan/void (<code>batal</code>), sehingga anomali pembatalan di loket mudah terdeteksi.</li>
    <li><strong>Monitoring Absensi Avalis (<code>/supervisor/absensi-avalis</code>):</strong> Supervisor dapat memantau daftar kehadiran petugas siswa pendamping loket pada shift Pagi maupun Siang. (Catatan: pengubahan/penghapusan master petugas avalis hanya menjadi hak Administrator).</li>
    <li><strong>Pusat Notifikasi (<code>/supervisor/notifikasi</code>):</strong> Menyimpan daftar pemberitahuan otomatis setiap kali Teller mengajukan droping kas atau mengirim daily report baru.</li>
</ul>

<x-docs.heading id="rekonsiliasi-dan-pelaporan" level="2">Rekonsiliasi Kas, Laporan &amp; Cetak Buku Harian</x-docs.heading>
<ul>
    <li><strong>Rekonsiliasi Kas Harian (<code>/supervisor/rekonsiliasi</code>):</strong> Membandingkan arus kas masuk/keluar tercatat terhadap saldo fisik kas loket serta menampilkan selisih (<em>overage / shortage</em>) secara otomatis.</li>
    <li><strong>Laporan Rekapitulasi (<code>/supervisor/laporan</code>):</strong> Filter gabungan multi-parameter (tanggal awal/akhir, jenis transaksi, status, petugas/nasabah) untuk menelusuri histori transaksi sekolah.</li>
    <li><strong>Cetak Buku Harian (<code>/supervisor/cetak-harian</code>):</strong> Menghasilkan lembar laporan operasional harian siap cetak untuk arsip pembimbing bank mini.</li>
    <li><strong>Laporan Akuntansi Terpusat:</strong> Supervisor berwenang membuka <strong>Buku Jurnal Akuntansi</strong> (<code>/laporan/jurnal</code> &amp; <code>/laporan/cetak-jurnal</code>), <strong>Laporan Saldo per Produk/Jurusan</strong> (<code>/laporan/saldo-produk</code>), dan <strong>Laporan Tahunan</strong> (<code>/laporan/tahunan</code>).</li>
</ul>
<x-docs.callout type="danger" title="Pemisahan Wewenang Supervisor (SoD)">
    Supervisor tidak berwenang memproses transaksi setoran maupun penarikan tunai di loket kasir (<code>/teller/transaksi*</code> diblokir oleh <code>RoleGuard</code>), tidak memiliki menu CRUD akun pegawai (<code>/admin/data-pegawai</code>), tidak dapat mengubah konfigurasi sistem global, serta tidak memiliki menu persetujuan reset password/PIN nasabah.
</x-docs.callout>

