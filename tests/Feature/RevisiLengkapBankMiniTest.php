<?php

namespace Tests\Feature;

use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Nasabah;
use App\Models\PinResetRequest;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RevisiLengkapBankMiniTest extends TestCase
{
    use RefreshDatabase;

    private User $teller;

    private User $supervisor;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teller = User::factory()->create([
            'nama_petugas' => 'Teller Satu',
            'username' => 'teller1',
            'role' => 'teller',
        ]);

        $this->supervisor = User::factory()->create([
            'nama_petugas' => 'Supervisor Satu',
            'username' => 'spv1',
            'role' => 'supervisor',
        ]);

        $this->admin = User::factory()->create([
            'nama_petugas' => 'Admin Utama',
            'username' => 'admin1',
            'role' => 'admin',
        ]);
    }

    public function test_teller_dashboard_hanya_menampilkan_nasabah_yang_dituju_dan_ada_qr_scanner(): void
    {
        $nasabahTarget = Nasabah::create([
            'no_rekening' => '26-RPL-001',
            'nis' => '10001',
            'nama_siswa' => 'Siswa Target Scan',
            'kelas' => 'XI RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '10001',
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 50000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        $nasabahLain = Nasabah::create([
            'no_rekening' => '26-RPL-002',
            'nis' => '10002',
            'nama_siswa' => 'Siswa Lain Tidak Dicari',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '10002',
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 75000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // 1. Saat dashboard dibuka tanpa pencarian, tidak menampilkan daftar seluruh siswa
        $resEmpty = $this->actingAs($this->teller)->get('/teller/dashboard');
        $resEmpty->assertStatus(200);
        $resEmpty->assertSee('Scan Cepat QR Siswa');
        $resEmpty->assertSee('modal-qr-scan');
        $resEmpty->assertDontSee('Siswa Target Scan');
        $resEmpty->assertDontSee('Siswa Lain Tidak Dicari');

        // 2. Saat hasil scan/cari mengarah ke rekening target, hanya nasabah target yang tampil
        $resScan = $this->actingAs($this->teller)->get('/teller/dashboard?cari=26-RPL-001');
        $resScan->assertStatus(200);
        $resScan->assertSee('Siswa Target Scan');
        $resScan->assertDontSee('Siswa Lain Tidak Dicari');
    }

    public function test_minimal_setoran_5000_dan_saldo_mengendap_10000(): void
    {
        Brankas::getOrCreateToday(Carbon::today()->toDateString())->update([
            'saldo_awal' => 500000,
            'total' => 500000,
        ]);

        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-010',
            'nis' => '10010',
            'nama_siswa' => 'Andi Penabung',
            'kelas' => 'XI RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '10010',
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 25000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // 1. Setoran di bawah 5000 (misal 3000) wajib ditolak
        $resSetorKurang = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 3000,
        ]);
        $resSetorKurang->assertSessionHas('error');
        $this->assertEquals(25000, $nasabah->fresh()->saldo);

        // 2. Setoran 5000 berhasil
        $resSetorValid = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 5000,
        ]);
        $resSetorValid->assertSessionHas('success');
        $this->assertEquals(30000, $nasabah->fresh()->saldo);

        // 3. Penarikan yang menghabiskan saldo mengendap 10.000 (saldo 30.000, tarik 25.000 -> sisa 5.000) wajib ditolak
        $resTarikLebih = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'tarik',
            'nominal' => 25000,
            'pin_nasabah' => '123456',
        ]);
        $resTarikLebih->assertSessionHas('error');
        $this->assertEquals(30000, $nasabah->fresh()->saldo);

        // 4. Penarikan menyisakan tepat 10.000 (tarik 20.000) berhasil
        $resTarikValid = $this->actingAs($this->teller)->post('/teller/transaksi/proses', [
            'nasabah_id' => $nasabah->id,
            'jenis_transaksi' => 'tarik',
            'nominal' => 20000,
            'pin_nasabah' => '123456',
        ]);
        $resTarikValid->assertSessionHas('success');
        $this->assertEquals(10000, $nasabah->fresh()->saldo);
    }

    public function test_tutup_kas_hari_ini_tanpa_setoran_dan_transaksi_gagal_tidak_dihitung(): void
    {
        $today = Carbon::today()->toDateString();
        Brankas::getOrCreateToday($today)->update([
            'saldo_awal' => 1000000,
            'total' => 850000,
        ]);

        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-020',
            'nis' => '10020',
            'nama_siswa' => 'Budi Tarik',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '10020',
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 500000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        // Hari ini HANYA ada penarikan sukses Rp 150.000 (tidak ada setoran)
        Transaksi::create([
            'nasabah_id' => $nasabah->id,
            'petugas_id' => $this->teller->id,
            'jenis_transaksi' => 'tarik',
            'kode_sandi' => '201',
            'nominal' => 150000,
            'saldo_akhir' => 350000,
            'status' => 'sukses',
        ]);

        // Transaksi gagal / pending Rp 200.000 TIDAK BOLEH dihitung
        Transaksi::create([
            'nasabah_id' => $nasabah->id,
            'petugas_id' => $this->teller->id,
            'jenis_transaksi' => 'setor',
            'kode_sandi' => '101',
            'nominal' => 200000,
            'saldo_akhir' => 550000,
            'status' => 'gagal',
        ]);

        // 1. Coba tutup kas dengan tanggal kemarin -> wajib ditolak
        $yesterday = Carbon::yesterday()->toDateString();
        $resYesterday = $this->actingAs($this->teller)->post('/teller/daily-report/simpan', [
            'report_date' => $yesterday,
            'physical_balance' => 850000,
        ]);
        $resYesterday->assertSessionHas('error');

        // 2. Tutup kas hari ini: Saldo awal 1.000.000 - Penarikan 150.000 = 850.000 (transaksi gagal 200.000 diabaikan)
        $resToday = $this->actingAs($this->teller)->post('/teller/daily-report/simpan', [
            'physical_balance' => 850000,
            'notes' => 'Hanya penarikan hari ini, kas seimbang',
        ]);
        $resToday->assertSessionHas('success');

        $report = DailyReport::where('user_id', $this->teller->id)->first();
        $this->assertNotNull($report);
        $this->assertEquals(1000000, $report->opening_balance);
        $this->assertEquals(0, $report->total_deposit);
        $this->assertEquals(150000, $report->total_withdrawal);
        $this->assertEquals(850000, $report->system_balance);
        $this->assertEquals(0, $report->difference);
    }

    public function test_auto_close_kas_command_berhasil_jika_seimbang_dan_menolak_jika_selisih(): void
    {
        $today = Carbon::today()->toDateString();
        Brankas::getOrCreateToday($today)->update([
            'saldo_awal' => 500000,
            'total' => 500000,
        ]);

        $teller2 = User::factory()->create([
            'nama_petugas' => 'Teller Dua Selisih',
            'username' => 'teller2',
            'role' => 'teller',
        ]);

        // Teller 2 punya laporan dengan selisih Rp -50.000
        DailyReport::create([
            'user_id' => $teller2->id,
            'report_date' => $today,
            'opening_balance' => 500000,
            'total_deposit' => 0,
            'total_cash_drop' => 0,
            'total_withdrawal' => 0,
            'system_balance' => 500000,
            'physical_balance' => 450000,
            'difference' => -50000,
            'status' => 'pending',
            'notes' => 'Selisih kurang 50rb',
        ]);

        // Jalankan command auto-close
        $this->artisan('kas:auto-close')->assertExitCode(0);

        // Teller 1 (belum lapor & seimbang) otomatis dibuatkan laporan status approved
        $rep1 = DailyReport::where('user_id', $this->teller->id)->first();
        $this->assertNotNull($rep1);
        $this->assertEquals('approved', $rep1->status);
        $this->assertEquals(0, $rep1->difference);

        // Teller 2 (ada selisih) TIDAK BOLEH otomatis di-approve (tetap pending)
        $rep2 = DailyReport::where('user_id', $teller2->id)->first();
        $this->assertEquals('pending', $rep2->status);
    }

    public function test_alur_lupa_pin_nasabah_ajukan_admin_approve_nasabah_buat_pin_mandiri(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-055',
            'nis' => '10055',
            'nama_siswa' => 'Citra Lupa PIN',
            'kelas' => 'XI RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '10055',
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 100000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        $nasabahUser = User::factory()->create([
            'nama_petugas' => 'Citra Lupa PIN',
            'username' => '10055',
            'role' => 'nasabah',
        ]);

        // 1. Nasabah mengajukan permohonan lupa PIN
        $resReq = $this->actingAs($nasabahUser)->post('/nasabah/lupa-pin/ajukan', [
            'no_rekening' => $nasabah->no_rekening,
            'alasan' => 'Saya lupa PIN kartu saat ingin menarik tabungan',
        ]);
        $resReq->assertSessionHas('success');

        $pinReq = PinResetRequest::where('nasabah_id', $nasabah->id)->first();
        $this->assertNotNull($pinReq);
        $this->assertEquals('pending', $pinReq->status);

        // 2. Nasabah mencoba buat PIN baru sebelum di-approve Admin -> wajib gagal
        $resSetBeforeApprove = $this->actingAs($nasabahUser)->post('/nasabah/lupa-pin/set-pin', [
            'no_rekening' => $nasabah->no_rekening,
            'pin_baru' => '654321',
            'pin_baru_confirmation' => '654321',
        ]);
        $resSetBeforeApprove->assertSessionHas('error');

        // 3. Admin menyetujui permohonan reset PIN (tanpa menginput PIN)
        $resApprove = $this->actingAs($this->admin)->post('/admin/pin-reset/approve/'.$pinReq->id);
        $resApprove->assertSessionHas('success');
        $this->assertEquals('approved', $pinReq->fresh()->status);

        // 4. Nasabah membuat PIN 6 digit baru secara mandiri
        $resSetAfterApprove = $this->actingAs($nasabahUser)->post('/nasabah/lupa-pin/set-pin', [
            'no_rekening' => $nasabah->no_rekening,
            'pin_baru' => '654321',
            'pin_baru_confirmation' => '654321',
        ]);
        $resSetAfterApprove->assertSessionHas('success');

        $this->assertEquals('completed', $pinReq->fresh()->status);
        $this->assertTrue(Hash::check('654321', $nasabah->fresh()->pin_keamanan));
    }

    public function test_admin_import_siswa_baru_kelas_10_nonaktif_dan_hapus_alumni_tanpa_hilang_riwayat(): void
    {
        // 1. Import CSV berisi siswa kelas 10 (X RPL 1) dan kelas 11 (XI RPL 1)
        $csvContent = "NIS;Nama Lengkap;Kelas;Jurusan\n";
        $csvContent .= "20001;Murid Baru Kelas 10;X RPL 1;Rekayasa Perangkat Lunak\n";
        $csvContent .= "20002;Murid Kelas 11;XI RPL 1;Rekayasa Perangkat Lunak\n";

        $file = UploadedFile::fake()->createWithContent('siswa_baru.csv', $csvContent);

        $resImport = $this->actingAs($this->admin)->post('/admin/import-nasabah', [
            'file_csv' => $file,
            'status_default' => 'auto',
        ]);
        $resImport->assertSessionHas('success');

        $siswaKelas10 = Nasabah::where('nis', '20001')->first();
        $siswaKelas11 = Nasabah::where('nis', '20002')->first();

        $this->assertNotNull($siswaKelas10);
        $this->assertEquals('nonaktif', $siswaKelas10->status); // Kelas 10 otomatis belum aktif (nonaktif)

        $this->assertNotNull($siswaKelas11);
        $this->assertEquals('aktif', $siswaKelas11->status);

        // 2. Murid kelas 12 lulus (alumni) memiliki riwayat transaksi, lalu dihapus oleh Admin
        $alumni = Nasabah::create([
            'no_rekening' => '24-RPL-099',
            'nis' => '15099',
            'nama_siswa' => 'Kakak Alumni Kelas 12',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '15099',
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 0,
            'status' => 'alumni',
            'kategori' => 'alumni',
        ]);

        $trxAlumni = Transaksi::create([
            'nasabah_id' => $alumni->id,
            'petugas_id' => $this->teller->id,
            'jenis_transaksi' => 'setor',
            'kode_sandi' => '101',
            'nominal' => 50000,
            'saldo_akhir' => 50000,
            'status' => 'sukses',
        ]);

        // Admin menghapus alumni
        $resDelete = $this->actingAs($this->admin)->post('/admin/hapus-nasabah/'.$alumni->id);
        $resDelete->assertRedirect('/admin/data-nasabah');

        // Alumni ter-soft-delete
        $this->assertSoftDeleted('nasabah', ['id' => $alumni->id]);

        // Riwayat transaksi alumni TETAP ADA dan relasi nasabah tetap terbaca
        $trxCheck = Transaksi::with('nasabah')->find($trxAlumni->id);
        $this->assertNotNull($trxCheck);
        $this->assertNotNull($trxCheck->nasabah);
        $this->assertEquals('Kakak Alumni Kelas 12', $trxCheck->nasabah->nama_siswa);
    }
}
