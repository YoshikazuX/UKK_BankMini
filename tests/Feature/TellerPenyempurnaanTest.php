<?php

namespace Tests\Feature;

use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Jurnal;
use App\Models\Nasabah;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TellerPenyempurnaanTest extends TestCase
{
    use RefreshDatabase;

    private User $teller;

    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teller = User::factory()->create([
            'nama_petugas' => 'Rina Teller Loket',
            'username' => 'rina_teller',
            'role' => 'teller',
        ]);

        $this->supervisor = User::factory()->create([
            'nama_petugas' => 'Budi Supervisor',
            'username' => 'budi_spv',
            'role' => 'supervisor',
        ]);
    }

    /**
     * Skenario 1: Detail Rekening Sebelum Transaksi
     */
    public function test_skenario_1_detail_rekening_ditampilkan_dan_rekening_nonaktif_dilarang(): void
    {
        $nasabahAktif = Nasabah::create([
            'no_rekening' => '26-RPL-001',
            'nis' => '1001',
            'nama_siswa' => 'Ahmad Santoso',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => 'ahmad01',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 150000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        $nasabahNonaktif = Nasabah::create([
            'no_rekening' => '26-AKL-002',
            'nis' => '1002',
            'nama_siswa' => 'Siti Nonaktif',
            'kelas' => 'XII AKL 2',
            'jurusan' => 'Akuntansi',
            'username' => 'siti02',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 200000,
            'status' => 'nonaktif',
            'kategori' => 'siswa',
        ]);

        // 1. Akses form transaksi nasabah aktif menampilkan informasi lengkap
        $response = $this->actingAs($this->teller)->get('/teller/transaksi/'.$nasabahAktif->id);
        $response->assertStatus(200);
        $response->assertSee('Ahmad Santoso');
        $response->assertSee('26-RPL-001');
        $response->assertSee('1001');
        $response->assertSee('XII RPL 1');
        $response->assertSee('Rekayasa Perangkat Lunak');
        $response->assertSee('Rekening Aktif');
        $response->assertSee('Saldo Siap Ditarik');
        $response->assertDontSee($nasabahAktif->password);
        $response->assertDontSee($nasabahAktif->pin_keamanan);

        // 2. Akses form nasabah nonaktif menampilkan indikator peringatan nonaktif
        $responseNonaktif = $this->actingAs($this->teller)->get('/teller/transaksi/'.$nasabahNonaktif->id);
        $responseNonaktif->assertStatus(200);
        $responseNonaktif->assertSee('Nonaktif / Ditangguhkan');
        $responseNonaktif->assertSee('Rekening Tidak Dapat Melakukan Transaksi Finansial');

        // 3. Backend menolak transaksi pada nasabah nonaktif
        $responseProses = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabahNonaktif->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 50000,
        ]);
        $responseProses->assertRedirect();
        $responseProses->assertSessionHas('error');
        $this->assertDatabaseMissing('transaksi', [
            'nasabah_id' => $nasabahNonaktif->id,
            'nominal' => 50000,
        ]);
    }

    /**
     * Skenario 2: Validasi Saldo Sebelum Penarikan (Saldo Tersedia = Saldo - Saldo Mengendap)
     */
    public function test_skenario_2_penarikan_ditolak_jika_melebihi_saldo_tersedia(): void
    {
        // Saldo = 50.000, Saldo Mengendap Min = 10.000 -> Saldo Tersedia = 40.000
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-010',
            'nis' => '1010',
            'nama_siswa' => 'Doni Penarik',
            'kelas' => 'XI RPL 1',
            'jurusan' => 'RPL',
            'username' => 'doni10',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 50000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // Kas loket tersedia banyak
        Brankas::create([
            'tanggal' => now()->toDateString(),
            'saldo_awal' => 500000,
            'masuk' => 0,
            'keluar' => 0,
            'total' => 500000,
        ]);

        // Percobaan penarikan Rp 45.000 (melebihi saldo tersedia Rp 40.000)
        $response = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'tarik',
            'nominal' => 45000,
            'pin_nasabah' => '123456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Penarikan ditolak. Saldo tersedia nasabah hanya Rp 40.000.');

        // Pastikan saldo nasabah TIDAK berubah
        $nasabah->refresh();
        $this->assertEquals(50000, $nasabah->saldo);

        // Pastikan kas loket TIDAK berkurang
        $brankas = Brankas::whereDate('tanggal', now()->toDateString())->first();
        $this->assertEquals(500000, $brankas->total);

        // Pastikan TIDAK ADA mutasi jurnal atau transaksi yang dicatat
        $this->assertEquals(0, Transaksi::where('nasabah_id', $nasabah->id)->count());
        $this->assertEquals(0, Jurnal::count());
    }

    /**
     * Skenario 3: Validasi Ketersediaan Kas Fisik Loket
     */
    public function test_skenario_3_penarikan_ditolak_jika_kas_fisik_loket_tidak_mencukupi(): void
    {
        // Nasabah memiliki saldo besar (Rp 1.000.000)
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-020',
            'nis' => '1020',
            'nama_siswa' => 'Bagus Kaya',
            'kelas' => 'XII RPL 2',
            'jurusan' => 'RPL',
            'username' => 'bagus20',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 1000000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // Kas fisik loket hanya Rp 100.000
        Brankas::create([
            'tanggal' => now()->toDateString(),
            'saldo_awal' => 100000,
            'masuk' => 0,
            'keluar' => 0,
            'total' => 100000,
        ]);

        // Nasabah ingin menarik Rp 150.000
        $response = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'tarik',
            'nominal' => 150000,
            'pin_nasabah' => '123456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Penarikan tidak dapat diproses. Kas operasional teller tidak mencukupi. Silakan lakukan pengajuan droping kas kepada Supervisor.');

        // Pastikan kas TIDAK menjadi negatif
        $brankas = Brankas::whereDate('tanggal', now()->toDateString())->first();
        $this->assertEquals(100000, $brankas->total);

        // Pastikan saldo nasabah utuh
        $nasabah->refresh();
        $this->assertEquals(1000000, $nasabah->saldo);
    }

    /**
     * Skenario 4: Pencegahan Transaksi Ganda (Idempotency Protection)
     */
    public function test_skenario_4_pencegahan_transaksi_ganda_dengan_idempotency_key(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-030',
            'nis' => '1030',
            'nama_siswa' => 'Eka Setor',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
            'username' => 'eka30',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 20000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        Brankas::create([
            'tanggal' => now()->toDateString(),
            'saldo_awal' => 100000,
            'masuk' => 0,
            'keluar' => 0,
            'total' => 100000,
        ]);

        $idempotencyKey = (string) Str::uuid();

        // 1. Submit pertama
        $response1 = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 30000,
            'idempotency_key' => $idempotencyKey,
        ]);
        $response1->assertRedirect('/teller/dashboard');

        // 2. Submit kedua dengan kunci idempotensi yang sama persis (misal double-click / retry)
        $response2 = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 30000,
            'idempotency_key' => $idempotencyKey,
        ]);
        $response2->assertRedirect('/teller/dashboard');
        $response2->assertSessionHas('success');

        // Verifikasi HANYA ADA 1 record transaksi
        $this->assertEquals(1, Transaksi::where('idempotency_key', $idempotencyKey)->count());

        // Verifikasi saldo HANYA bertambah 1 kali: 20.000 + 30.000 = 50.000
        $nasabah->refresh();
        $this->assertEquals(50000, $nasabah->saldo);

        // Verifikasi kas HANYA bertambah 1 kali: 100.000 + 30.000 = 130.000
        $brankas = Brankas::whereDate('tanggal', now()->toDateString())->first();
        $this->assertEquals(130000, $brankas->total);

        // Verifikasi jurnal HANYA ada 1 kali
        $this->assertEquals(1, Jurnal::count());
    }

    /**
     * Skenario 5: Penanganan Koneksi Terputus & Verifikasi Status Transaksi
     */
    public function test_skenario_5_cek_status_idempotency_setelah_koneksi_terputus(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-040',
            'nis' => '1040',
            'nama_siswa' => 'Fajar Koneksi',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'RPL',
            'username' => 'fajar40',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 100000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        $idempotencyKey = (string) Str::uuid();

        // 1. Sebelum transaksi terkirim, cek status mengembalikan 404 not found
        $responseCheck1 = $this->actingAs($this->teller)->getJson('/teller/transaksi/status-idempotency/'.$idempotencyKey);
        $responseCheck1->assertStatus(404);
        $responseCheck1->assertJson(['status' => 'not_found']);

        // 2. Transaksi diproses oleh server
        $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 25000,
            'idempotency_key' => $idempotencyKey,
        ]);

        // 3. Saat teller memeriksa kembali dengan kunci yang sama, server mengembalikan status sukses
        $responseCheck2 = $this->actingAs($this->teller)->getJson('/teller/transaksi/status-idempotency/'.$idempotencyKey);
        $responseCheck2->assertStatus(200);
        $responseCheck2->assertJson([
            'success' => true,
            'status' => 'sukses',
            'data' => [
                'nominal' => 25000,
                'no_rekening' => '26-RPL-040',
                'nama_nasabah' => 'Fajar Koneksi',
            ],
        ]);
    }

    /**
     * Skenario 6: Status Transaksi Berhasil vs Gagal
     */
    public function test_skenario_6_status_transaksi_berhasil_commit_ke_sukses(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-050',
            'nis' => '1050',
            'nama_siswa' => 'Gita Sukses',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'RPL',
            'username' => 'gita50',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 50000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        $response = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 50000,
        ]);

        $response->assertRedirect('/teller/dashboard');
        $response->assertSessionHas('success');
        $response->assertSessionHas('slip_url');

        $trx = Transaksi::where('nasabah_id', $nasabah->id)->first();
        $this->assertNotNull($trx);
        $this->assertEquals('sukses', $trx->status);
        $this->assertEquals('101', $trx->kode_sandi);
    }

    /**
     * Skenario 7: Riwayat Pembatalan dan Koreksi (Void Transaksi)
     */
    public function test_skenario_7_void_transaksi_menghasilkan_mutasi_pembalik_dan_audit(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-060',
            'nis' => '1060',
            'nama_siswa' => 'Hadi Void',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'RPL',
            'username' => 'hadi60',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 100000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // Setor Rp 50.000 -> saldo jadi 150.000
        $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 50000,
        ]);

        $trxAsli = Transaksi::where('nasabah_id', $nasabah->id)->first();
        $this->assertEquals('sukses', $trxAsli->status);

        // Eksekusi Void dengan PIN Supervisor 123456
        $responseVoid = $this->actingAs($this->teller)->post('/teller/transaksi/void/'.$trxAsli->id, [
            'pin_supervisor' => '123456',
            'alasan_batal' => 'Nasabah membatalkan setoran karena uang robek',
        ]);

        $responseVoid->assertRedirect();
        $responseVoid->assertSessionHas('success');

        // 1. Transaksi asli berubah status menjadi dibatalkan
        $trxAsli->refresh();
        $this->assertEquals('dibatalkan', $trxAsli->status);
        $this->assertEquals('Nasabah membatalkan setoran karena uang robek', $trxAsli->alasan_batal);
        $this->assertEquals($this->teller->id, $trxAsli->dibatalkan_oleh);
        $this->assertNotNull($trxAsli->dibatalkan_at);

        // 2. Terbentuk transaksi koreksi pembalik [102]
        $reversalTrx = Transaksi::where('ref_transaksi_id', $trxAsli->id)->first();
        $this->assertNotNull($reversalTrx);
        $this->assertEquals('koreksi_setor', $reversalTrx->jenis_transaksi);
        $this->assertEquals('102', $reversalTrx->kode_sandi);
        $this->assertEquals(50000, $reversalTrx->nominal);

        // 3. Saldo nasabah kembali ke semula (150.000 - 50.000 = 100.000)
        $nasabah->refresh();
        $this->assertEquals(100000, $nasabah->saldo);

        // 4. Audit Log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'jenis_aktivitas' => 'VOID_TRANSAKSI',
            'objek_id' => 'TRX-'.$trxAsli->id,
        ]);

        // 5. Transaksi yang sudah dibatalkan tidak boleh dibatalkan kembali
        $responseVoidAgain = $this->actingAs($this->teller)->post('/teller/transaksi/void/'.$trxAsli->id, [
            'pin_supervisor' => '123456',
            'alasan_batal' => 'Coba batalkan ulang',
        ]);
        $responseVoidAgain->assertSessionHas('error');
    }

    /**
     * Skenario 8: Rekonsiliasi Kas Teller (Daily Report)
     */
    public function test_skenario_8_rekonsiliasi_kas_menghitung_selisih_dan_wajib_catatan(): void
    {
        $today = Carbon::today()->toDateString();

        // Saldo awal 500.000
        Brankas::create([
            'tanggal' => $today,
            'saldo_awal' => 500000,
            'masuk' => 0,
            'keluar' => 0,
            'total' => 500000,
        ]);

        // Droping kas yang disetujui Rp 500.000
        PengajuanKas::create([
            'nomor_pengajuan' => 'PK-20261031-001',
            'user_id' => $this->teller->id,
            'tanggal' => $today,
            'nominal' => 500000,
            'status' => 'disetujui',
            'disetujui_oleh' => $this->supervisor->id,
        ]);

        // Kas sistem = Saldo Awal (500.000) + Droping (500.000) = 1.000.000

        // Teller input kas fisik 950.000 (Selisih -50.000) TANPA catatan -> Wajib ditolak
        $responseTanpaCatatan = $this->actingAs($this->teller)->post('/teller/daily-report/simpan', [
            'physical_balance' => 950000,
            'notes' => '',
        ]);
        $responseTanpaCatatan->assertSessionHas('error');
        $this->assertDatabaseMissing('daily_reports', ['user_id' => $this->teller->id]);

        // Teller input kas fisik 950.000 DENGAN catatan penjelasan alasan selisih
        $responseDenganCatatan = $this->actingAs($this->teller)->post('/teller/daily-report/simpan', [
            'physical_balance' => 950000,
            'notes' => 'Kekurangan Rp 50.000 karena selisih kembalian pada antrean pagi',
        ]);
        $responseDenganCatatan->assertRedirect();
        $responseDenganCatatan->assertSessionHas('success');

        $report = DailyReport::where('user_id', $this->teller->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals(1000000, $report->system_balance);
        $this->assertEquals(950000, $report->physical_balance);
        $this->assertEquals(-50000, $report->difference);
        $this->assertEquals(500000, $report->total_cash_drop);
    }

    /**
     * Skenario 9: Filter Riwayat Transaksi Teller
     */
    public function test_skenario_9_filter_riwayat_berdasarkan_tanggal_jenis_dan_status(): void
    {
        $nasabah1 = Nasabah::create([
            'no_rekening' => '26-RPL-071',
            'nis' => '1071',
            'nama_siswa' => 'Indah Setor',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
            'username' => 'indah71',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 200000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        $nasabah2 = Nasabah::create([
            'no_rekening' => '26-RPL-072',
            'nis' => '1072',
            'nama_siswa' => 'Joko Tarik',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'RPL',
            'username' => 'joko72',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 300000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // Trx 1: Setor Sukses (Kemarin)
        $trx1 = Transaksi::create([
            'nasabah_id' => $nasabah1->id,
            'petugas_id' => $this->teller->id,
            'nama_petugas' => $this->teller->nama_petugas,
            'jenis_transaksi' => 'setor',
            'kode_sandi' => '101',
            'nominal' => 50000,
            'status' => 'sukses',
        ]);
        $trx1->forceFill(['created_at' => Carbon::parse('2026-10-30 10:00:00')])->save();

        // Trx 2: Tarik Sukses (Hari ini)
        $trx2 = Transaksi::create([
            'nasabah_id' => $nasabah2->id,
            'petugas_id' => $this->teller->id,
            'nama_petugas' => $this->teller->nama_petugas,
            'jenis_transaksi' => 'tarik',
            'kode_sandi' => '201',
            'nominal' => 25000,
            'status' => 'sukses',
        ]);
        $trx2->forceFill(['created_at' => Carbon::parse('2026-10-31 09:00:00')])->save();

        // Trx 3: Setor Dibatalkan (Hari ini)
        $trx3 = Transaksi::create([
            'nasabah_id' => $nasabah1->id,
            'petugas_id' => $this->teller->id,
            'nama_petugas' => $this->teller->nama_petugas,
            'jenis_transaksi' => 'setor',
            'kode_sandi' => '101',
            'nominal' => 70000,
            'status' => 'dibatalkan',
            'alasan_batal' => 'Salah nasabah',
        ]);
        $trx3->forceFill(['created_at' => Carbon::parse('2026-10-31 11:00:00')])->save();

        // 1. Filter jenis_transaksi = tarik
        $responseTarik = $this->actingAs($this->teller)->get('/teller/riwayat?jenis_transaksi=tarik');
        $responseTarik->assertStatus(200);
        $responseTarik->assertSee('Joko Tarik');
        $responseTarik->assertDontSee('Indah Setor');

        // 2. Filter status = dibatalkan
        $responseBatal = $this->actingAs($this->teller)->get('/teller/riwayat?status=dibatalkan');
        $responseBatal->assertStatus(200);
        $responseBatal->assertSee('Indah Setor');
        $responseBatal->assertSee('Salah nasabah');
        $responseBatal->assertDontSee('Joko Tarik');

        // 3. Filter tanggal tunggal 2026-10-30
        $responseTgl = $this->actingAs($this->teller)->get('/teller/riwayat?tanggal=2026-10-30');
        $responseTgl->assertStatus(200);
        $responseTgl->assertSee('Indah Setor');
        $responseTgl->assertDontSee('Joko Tarik');

        // 4. Filter rentang tanggal
        $responseRentang = $this->actingAs($this->teller)->get('/teller/riwayat?tanggal_mulai=2026-10-31&tanggal_akhir=2026-10-31');
        $responseRentang->assertStatus(200);
        $responseRentang->assertSee('Joko Tarik');
        $responseRentang->assertSee('Indah Setor'); // Trx 3 ada di tanggal 31

        // 5. Pencarian kata kunci (nama, no rekening, atau kata kunci)
        $responseCari = $this->actingAs($this->teller)->get('/teller/riwayat?cari=Joko');
        $responseCari->assertStatus(200);
        $responseCari->assertSee('Joko Tarik');
        $responseCari->assertDontSee('Indah Setor');
    }
}
