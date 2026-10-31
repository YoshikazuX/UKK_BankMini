<?php

namespace Tests\Feature;

use App\Models\ApprovalHistory;
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
use Tests\TestCase;

class SupervisorPenyempurnaanTest extends TestCase
{
    use RefreshDatabase;

    private User $teller1;

    private User $teller2;

    private User $supervisor;

    private Nasabah $nasabah;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teller1 = User::factory()->create([
            'nama_petugas' => 'Rina Teller 1',
            'username' => 'rina_teller1',
            'role' => 'teller',
        ]);

        $this->teller2 = User::factory()->create([
            'nama_petugas' => 'Andi Teller 2',
            'username' => 'andi_teller2',
            'role' => 'teller',
        ]);

        $this->supervisor = User::factory()->create([
            'nama_petugas' => 'Budi Supervisor',
            'username' => 'budi_spv',
            'role' => 'supervisor',
        ]);

        $this->nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-101',
            'nis' => '10001',
            'nama_siswa' => 'Citra Lestari',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'RPL',
            'username' => 'citra01',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 500000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // Inisialisasi kas brankas utama
        Brankas::create([
            'tanggal' => Carbon::today()->toDateString(),
            'saldo_awal' => 50000000,
            'masuk' => 0,
            'keluar' => 0,
            'total' => 50000000,
        ]);
    }

    /**
     * Skenario 1: Pusat Persetujuan (Approval Center)
     * Teller 1 mengajukan droping kas Rp500.000, Teller 2 mengirim Daily Report.
     * Keduanya tampil di pusat persetujuan dan filter jenis pekerjaan berfungsi.
     */
    public function test_skenario_1_pusat_persetujuan_menampilkan_seluruh_pekerjaan_pending(): void
    {
        // Teller 1 mengajukan kas
        $pengajuan = PengajuanKas::create([
            'user_id' => $this->teller1->id,
            'nominal' => 500000,
            'alasan' => 'Likuiditas loket pagi hari',
            'status' => 'pending',
            'tanggal' => Carbon::today()->toDateString(),
        ]);

        // Teller 2 mengirim daily report
        $dailyReport = DailyReport::create([
            'user_id' => $this->teller2->id,
            'report_date' => Carbon::today()->toDateString(),
            'opening_balance' => 200000,
            'system_balance' => 700000,
            'physical_balance' => 700000,
            'difference' => 0,
            'status' => 'pending',
            'notes' => 'Kas seimbang di loket 2',
        ]);

        // Supervisor mengakses Pusat Persetujuan
        $responseAll = $this->actingAs($this->supervisor)->get('/supervisor/approval-center');
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Pusat Persetujuan');
        $responseAll->assertSee('Rina Teller 1');
        $responseAll->assertSee('Andi Teller 2');
        $responseAll->assertSee('500.000');
        $responseAll->assertSee('700.000');

        // Filter: Hanya Pengajuan Kas
        $responseKas = $this->actingAs($this->supervisor)->get('/supervisor/approval-center?jenis=pengajuan_kas');
        $responseKas->assertStatus(200);
        $responseKas->assertSee('Rina Teller 1');
        $responseKas->assertDontSee('Kas seimbang di loket 2');

        // Filter: Hanya Daily Report
        $responseDaily = $this->actingAs($this->supervisor)->get('/supervisor/approval-center?jenis=daily_report');
        $responseDaily->assertStatus(200);
        $responseDaily->assertSee('Andi Teller 2');
        $responseDaily->assertDontSee('Likuiditas loket pagi hari');
    }

    /**
     * Skenario 2: Detail Pengajuan Kas
     * Memeriksa posisi kas teller, mutasi hari ini, dan riwayat pengajuan sebelumnya.
     */
    public function test_skenario_2_detail_pengajuan_menampilkan_posisi_kas_dan_riwayat(): void
    {
        // Buat riwayat pengajuan kas terdahulu yang disetujui kemarin
        PengajuanKas::create([
            'user_id' => $this->teller1->id,
            'nominal' => 300000,
            'alasan' => 'Droping kemarin pagi',
            'status' => 'disetujui',
            'tanggal' => Carbon::yesterday()->toDateString(),
            'disetujui_oleh' => $this->supervisor->id,
        ]);

        // Buat transaksi setoran hari ini oleh Teller 1
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'setor',
            'nominal' => 150000,
            'saldo_awal' => 500000,
            'saldo_akhir' => 650000,
            'status' => 'sukses',
            'created_at' => Carbon::today()->setHour(10),
        ]);

        // Buat pengajuan kas baru oleh Teller 1
        $pengajuanBaru = PengajuanKas::create([
            'user_id' => $this->teller1->id,
            'nominal' => 500000,
            'alasan' => 'Antrean penarikan meningkat',
            'status' => 'pending',
            'tanggal' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($this->supervisor)->get('/supervisor/pengajuan-kas/'.$pengajuanBaru->id);
        $response->assertStatus(200);
        $response->assertSee('Detail Pengajuan Droping Kas');
        $response->assertSee('Rina Teller 1');
        $response->assertSee('Antrean penarikan meningkat');
        $response->assertSee('300.000'); // riwayat sebelumnya
        $response->assertSee('150.000'); // mutasi masuk hari ini
    }

    /**
     * Skenario 3: Riwayat Persetujuan (Approval History)
     * Supervisor menyetujui pengajuan kas, tabel approval_histories mencatat keputusan lengkap.
     */
    public function test_skenario_3_riwayat_persetujuan_mencatat_keputusan_secara_lengkap(): void
    {
        $pengajuan = PengajuanKas::create([
            'user_id' => $this->teller1->id,
            'nominal' => 400000,
            'alasan' => 'Tambah kas loket',
            'status' => 'pending',
            'tanggal' => Carbon::today()->toDateString(),
        ]);

        $response = $this->actingAs($this->supervisor)->post('/supervisor/pengajuan-kas/'.$pengajuan->id.'/setujui', [
            'catatan' => 'Disetujui untuk likuiditas loket 1',
        ]);

        $response->assertRedirect(route('supervisor.pengajuan-kas.detail', $pengajuan->id));
        $response->assertSessionHas('success');

        // Pastikan status pengajuan terupdate
        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->status);
        $this->assertEquals($this->supervisor->id, $pengajuan->disetujui_oleh);

        // Pastikan tabel approval_histories mencatat mutasi status
        $history = ApprovalHistory::where('pekerjaan_type', 'pengajuan_kas')
            ->where('pekerjaan_id', $pengajuan->id)
            ->first();

        $this->assertNotNull($history);
        $this->assertEquals('disetujui', $history->jenis_keputusan);
        $this->assertEquals('pending', $history->status_sebelum);
        $this->assertEquals('disetujui', $history->status_sesudah);
        $this->assertEquals($this->supervisor->id, $history->supervisor_id);
        $this->assertEquals($this->supervisor->nama_petugas, $history->nama_supervisor);
        $this->assertEquals($this->teller1->id, $history->teller_id);
        $this->assertEquals('Disetujui untuk likuiditas loket 1', $history->catatan);
    }

    /**
     * Skenario 4: Alasan Penolakan dan Klarifikasi
     * Penolakan kas tanpa alasan harus gagal validasi. Penolakan dengan alasan tersimpan di DB & riwayat.
     * Daily report hold menyimpan catatan klarifikasi.
     */
    public function test_skenario_4_alasan_penolakan_dan_klarifikasi_wajib_diisi(): void
    {
        $pengajuan = PengajuanKas::create([
            'user_id' => $this->teller1->id,
            'nominal' => 1000000,
            'alasan' => 'Permintaan kas berlebih',
            'status' => 'pending',
            'tanggal' => Carbon::today()->toDateString(),
        ]);

        // Coba tolak tanpa alasan -> Harus validasi error
        $responseFail = $this->actingAs($this->supervisor)->post('/supervisor/pengajuan-kas/'.$pengajuan->id.'/tolak', [
            'alasan_penolakan' => '',
        ]);
        $responseFail->assertSessionHasErrors(['alasan_penolakan']);
        $pengajuan->refresh();
        $this->assertEquals('pending', $pengajuan->status);

        // Tolak dengan alasan sah
        $alasanSah = 'Kas loket masih mencukupi untuk estimasi transaksi hari ini.';
        $responseSuccess = $this->actingAs($this->supervisor)->post('/supervisor/pengajuan-kas/'.$pengajuan->id.'/tolak', [
            'alasan_penolakan' => $alasanSah,
        ]);
        $responseSuccess->assertRedirect(route('supervisor.pengajuan-kas.detail', $pengajuan->id));
        $pengajuan->refresh();
        $this->assertEquals('ditolak', $pengajuan->status);
        $this->assertEquals($alasanSah, $pengajuan->alasan_penolakan);

        // Verifikasi Daily Report Hold dengan catatan klarifikasi
        $dailyReport = DailyReport::create([
            'user_id' => $this->teller2->id,
            'report_date' => Carbon::today()->toDateString(),
            'opening_balance' => 100000,
            'system_balance' => 500000,
            'physical_balance' => 450000,
            'difference' => -50000,
            'status' => 'pending',
            'notes' => 'Kas fisik kurang Rp50.000',
        ]);

        // Coba hold tanpa catatan -> Gagal
        $responseHoldFail = $this->actingAs($this->supervisor)->post('/supervisor/daily-report/status/'.$dailyReport->id, [
            'status' => 'hold',
            'notes' => '',
        ]);
        $responseHoldFail->assertSessionHasErrors(['notes']);

        // Hold dengan catatan sah
        $catatanKlarifikasi = 'Mohon hitung ulang fisik kas di laci dan cek nota penarikan terakhir.';
        $responseHoldSuccess = $this->actingAs($this->supervisor)->post('/supervisor/daily-report/status/'.$dailyReport->id, [
            'status' => 'hold',
            'notes' => $catatanKlarifikasi,
        ]);
        $responseHoldSuccess->assertRedirect(route('supervisor.daily-report.detail', $dailyReport->id));

        $dailyReport->refresh();
        $this->assertEquals('hold', $dailyReport->status);
        $this->assertEquals($catatanKlarifikasi, $dailyReport->catatan_spv);
    }

    /**
     * Skenario 5: Monitoring Teller
     * 5 transaksi dibuat (3 sukses, 2 dibatalkan). Metrik harus membedakan volume & frekuensi.
     */
    public function test_skenario_5_monitoring_teller_memisahkan_transaksi_sukses_dan_void(): void
    {
        // Transaksi Sukses 1 (Setor 100.000)
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'setor',
            'nominal' => 100000,
            'saldo_awal' => 500000,
            'saldo_akhir' => 600000,
            'status' => 'sukses',
            'created_at' => Carbon::today()->setHour(9),
        ]);

        // Transaksi Sukses 2 (Setor 200.000)
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'setor',
            'nominal' => 200000,
            'saldo_awal' => 600000,
            'saldo_akhir' => 800000,
            'status' => 'sukses',
            'created_at' => Carbon::today()->setHour(10),
        ]);

        // Transaksi Sukses 3 (Tarik 50.000)
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'tarik',
            'nominal' => 50000,
            'saldo_awal' => 800000,
            'saldo_akhir' => 750000,
            'status' => 'sukses',
            'created_at' => Carbon::today()->setHour(11),
        ]);

        // Transaksi Dibatalkan 1
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'setor',
            'nominal' => 75000,
            'saldo_awal' => 750000,
            'saldo_akhir' => 750000,
            'status' => 'dibatalkan',
            'dibatalkan_oleh' => $this->teller1->id,
            'dibatalkan_at' => Carbon::today()->setHour(12),
            'alasan_batal' => 'Salah input nominal',
            'created_at' => Carbon::today()->setHour(12),
        ]);

        // Transaksi Dibatalkan 2
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'tarik',
            'nominal' => 25000,
            'saldo_awal' => 750000,
            'saldo_akhir' => 750000,
            'status' => 'dibatalkan',
            'dibatalkan_oleh' => $this->teller1->id,
            'dibatalkan_at' => Carbon::today()->setHour(13),
            'alasan_batal' => 'Nasabah membatalkan',
            'created_at' => Carbon::today()->setHour(13),
        ]);

        $response = $this->actingAs($this->supervisor)->get('/supervisor/monitoring-teller');
        $response->assertStatus(200);
        $response->assertSee('Monitoring Produktivitas');
        $response->assertSee('Rina Teller 1');
        // Total transaksi sukses = 3, dibatalkan = 2
        $response->assertSee('3 Sukses');
        $response->assertSee('2 Void');
    }

    /**
     * Skenario 6: Rekonsiliasi Kas Otomatis
     * Menghitung defisit, surplus, dan seimbang dengan akurat.
     */
    public function test_skenario_6_rekonsiliasi_kas_menghitung_selisih_secara_otomatis(): void
    {
        // 1. Kas defisit: sistem 1.500.000, fisik 1.450.000 -> selisih -50.000
        DailyReport::create([
            'user_id' => $this->teller1->id,
            'report_date' => Carbon::today()->toDateString(),
            'opening_balance' => 500000,
            'system_balance' => 1500000,
            'physical_balance' => 1450000,
            'difference' => -50000,
            'status' => 'hold',
            'notes' => 'Defisit 50k',
        ]);

        // 2. Kas surplus: sistem 1.000.000, fisik 1.050.000 -> selisih +50.000
        DailyReport::create([
            'user_id' => $this->teller2->id,
            'report_date' => Carbon::today()->toDateString(),
            'opening_balance' => 200000,
            'system_balance' => 1000000,
            'physical_balance' => 1050000,
            'difference' => 50000,
            'status' => 'hold',
            'notes' => 'Surplus 50k',
        ]);

        $response = $this->actingAs($this->supervisor)->get('/supervisor/rekonsiliasi');
        $response->assertStatus(200);
        $response->assertSee('Rekonsiliasi Kas');
        $response->assertSee('Defisit');
        $response->assertSee('Surplus');
        $response->assertSee('50.000');
    }

    /**
     * Skenario 7: Filter Laporan Supervisor
     * Menggabungkan tanggal, teller, dan jenis transaksi secara akurat.
     */
    public function test_skenario_7_filter_laporan_dapat_digabungkan_dan_berfungsi(): void
    {
        // Transaksi Teller 1: Setor 100.000 hari ini
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'setor',
            'nominal' => 100000,
            'saldo_awal' => 500000,
            'saldo_akhir' => 600000,
            'status' => 'sukses',
            'created_at' => Carbon::today()->setHour(9),
        ]);

        // Transaksi Teller 1: Tarik 40.000 hari ini
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller1->id,
            'nama_petugas' => $this->teller1->nama_petugas,
            'jenis_transaksi' => 'tarik',
            'nominal' => 40000,
            'saldo_awal' => 600000,
            'saldo_akhir' => 560000,
            'status' => 'sukses',
            'created_at' => Carbon::today()->setHour(10),
        ]);

        // Transaksi Teller 2: Setor 300.000 hari ini
        Transaksi::create([
            'nasabah_id' => $this->nasabah->id,
            'petugas_id' => $this->teller2->id,
            'nama_petugas' => $this->teller2->nama_petugas,
            'jenis_transaksi' => 'setor',
            'nominal' => 300000,
            'saldo_awal' => 560000,
            'saldo_akhir' => 860000,
            'status' => 'sukses',
            'created_at' => Carbon::today()->setHour(11),
        ]);

        // Filter: Hanya Teller 1 & Hanya Jenis Setor
        $response = $this->actingAs($this->supervisor)->get('/supervisor/laporan?petugas_id='.$this->teller1->id.'&jenis_transaksi=setor');
        $response->assertStatus(200);
        $response->assertSee('100.000');
        $response->assertDontSee('300.000');
        $response->assertDontSee('40.000');
    }

    /**
     * Skenario 8: Notifikasi Pekerjaan Supervisor
     * Notifikasi diterima ketika Teller membuat pengajuan kas dan daily report.
     * Supervisor dapat menandai notifikasi dibaca.
     */
    public function test_skenario_8_notifikasi_pekerjaan_diterima_supervisor_saat_teller_submit(): void
    {
        // Teller 1 submit pengajuan kas via endpoint store
        $responsePengajuan = $this->actingAs($this->teller1)->post('/teller/pengajuan-kas/kirim', [
            'nominal' => 750000,
            'alasan' => 'Uang kas modal kerja pagi',
        ]);
        $responsePengajuan->assertSessionHas('success');

        // Pastikan Supervisor menerima notifikasi di database
        $this->supervisor->refresh();
        $notif = $this->supervisor->notifications()->first();
        $this->assertNotNull($notif);
        $this->assertEquals('pengajuan_kas', $notif->data['task_type']);
        $this->assertStringContainsString('750.000', $notif->data['message']);

        // Supervisor membaca notifikasi melalui route baca
        $responseBaca = $this->actingAs($this->supervisor)->get('/supervisor/notifikasi/baca/'.$notif->id);
        $responseBaca->assertRedirect();

        // Notifikasi harus sudah ditandai read
        $notif->refresh();
        $this->assertNotNull($notif->read_at);
    }

    /**
     * Skenario 9: Pencegahan Double Approval dengan Row Locking
     * Pengajuan kas yang sudah berstatus disetujui tidak dapat disetujui kembali.
     */
    public function test_skenario_9_pencegahan_double_approval_dengan_locking(): void
    {
        $pengajuan = PengajuanKas::create([
            'user_id' => $this->teller1->id,
            'nominal' => 250000,
            'alasan' => 'Uang tambahan loket',
            'status' => 'pending',
            'tanggal' => Carbon::today()->toDateString(),
        ]);

        // Approval Pertama -> Berhasil
        $response1 = $this->actingAs($this->supervisor)->post('/supervisor/pengajuan-kas/'.$pengajuan->id.'/setujui', [
            'catatan' => 'Persetujuan pertama',
        ]);
        $response1->assertRedirect(route('supervisor.pengajuan-kas.detail', $pengajuan->id));
        $response1->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->status);

        $jurnalCountBefore = Jurnal::count();

        // Approval Kedua -> Harus dicegah / error
        $response2 = $this->actingAs($this->supervisor)->post('/supervisor/pengajuan-kas/'.$pengajuan->id.'/setujui', [
            'catatan' => 'Persetujuan kedua yang dilarang',
        ]);
        $response2->assertSessionHas('error');

        // Jumlah jurnal tidak boleh bertambah (mencegah double booking)
        $this->assertEquals($jurnalCountBefore, Jurnal::count());
    }
}
