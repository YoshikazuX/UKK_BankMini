<?php

namespace Tests\Feature;

use App\Models\Brankas;
use App\Models\DailyReport;
use App\Models\Nasabah;
use App\Models\PasswordResetRequest;
use App\Models\PengajuanKas;
use App\Models\Transaksi;
use App\Models\User;
use App\Services\DashboardWidgetService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $teller1;

    private User $teller2;

    private User $supervisor;

    private Nasabah $nasabahAktif;

    private Nasabah $nasabahNonaktif;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'nama_petugas' => 'Admin Utama',
            'username' => 'admin_utama',
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $this->teller1 = User::factory()->create([
            'nama_petugas' => 'Teller Satu',
            'username' => 'teller_satu',
            'role' => 'teller',
            'status' => 'aktif',
        ]);

        $this->teller2 = User::factory()->create([
            'nama_petugas' => 'Teller Dua',
            'username' => 'teller_dua',
            'role' => 'teller',
            'status' => 'aktif',
        ]);

        $this->supervisor = User::factory()->create([
            'nama_petugas' => 'Supervisor Pengawas',
            'username' => 'supervisor_pengawas',
            'role' => 'supervisor',
            'status' => 'aktif',
        ]);

        // User nonaktif untuk memastikan filter status akurat
        User::factory()->create([
            'nama_petugas' => 'Mantan Pegawai',
            'username' => 'mantan_pegawai',
            'role' => 'teller',
            'status' => 'nonaktif',
        ]);

        $this->nasabahAktif = Nasabah::create([
            'nama_siswa' => 'Ahmad Siswa',
            'nis' => '10001',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'RPL',
            'username' => 'ahmad10001',
            'password' => bcrypt('password'),
            'pin_keamanan' => bcrypt('123456'),
            'kategori' => 'siswa',
            'alamat' => 'Bantul',
            'no_rekening' => '1000199',
            'saldo' => 500000,
            'status' => 'aktif',
        ]);

        $this->nasabahNonaktif = Nasabah::create([
            'nama_siswa' => 'Budi Lulusan',
            'nis' => '10002',
            'kelas' => 'Alumni',
            'jurusan' => 'RPL',
            'username' => 'budi10002',
            'password' => bcrypt('password'),
            'pin_keamanan' => bcrypt('123456'),
            'kategori' => 'siswa',
            'alamat' => 'Yogyakarta',
            'no_rekening' => '1000299',
            'saldo' => 0,
            'status' => 'nonaktif',
        ]);
    }

    public function test_admin_dashboard_displays_primary_widgets_with_accurate_database_data(): void
    {
        // Setup Password Reset Request
        PasswordResetRequest::create([
            'username' => 'nasabah_lupa',
            'nama_pemohon' => 'Siswa Lupa Password',
            'metode' => 'whatsapp',
            'kontak' => '081234567890',
            'keterangan' => 'Lupa PIN aplikasi',
            'status' => 'pending',
        ]);

        PasswordResetRequest::create([
            'username' => 'nasabah_beres',
            'nama_pemohon' => 'Siswa Selesai',
            'metode' => 'whatsapp',
            'kontak' => '081234567891',
            'keterangan' => 'Sudah selesai',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);

        // 1. Widget Nasabah
        $response->assertSee('Total Nasabah Terdaftar');
        $response->assertSee('1 Aktif');
        $response->assertSee('1 Nonaktif');
        $response->assertSee(route('admin.nasabah.index'));

        // 2. Widget Pegawai Aktif (4 aktif, 1 nonaktif)
        $response->assertSee('Total Pegawai Aktif');
        $response->assertSee('4');
        $response->assertSee('Pegawai Aktif');
        $response->assertSee('Teller: 2');
        $response->assertSee('Spv: 1');
        $response->assertSee('Adm: 1');

        // 3. Widget Reset Password
        $response->assertSee('Permohonan Reset Password');
        $response->assertSee('1');
        $response->assertSee('Tiket Butuh Tindakan');
        $response->assertSee('nasabah_lupa');
        $response->assertSee(route('admin.reset.index'));

        // Memastikan widget kesehatan teknis server yang tidak relevan telah dihapus
        $response->assertDontSee('Peringatan & Kesehatan Sistem', false);
    }

    public function test_teller_dashboard_displays_all_four_primary_widgets_with_teller_specific_activities_and_alerts(): void
    {
        $today = Carbon::today()->toDateString();
        Brankas::getOrCreateToday($today, false);

        // Transaksi Teller 1
        Transaksi::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 75000,
            'status' => 'selesai',
            'petugas_id' => $this->teller1->id,
            'created_at' => Carbon::now(),
        ]);

        // Transaksi Teller 2 (tidak boleh muncul di widget teller 1)
        Transaksi::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'jenis_transaksi' => 'tarik',
            'nominal' => 200000,
            'status' => 'selesai',
            'petugas_id' => $this->teller2->id,
            'created_at' => Carbon::now(),
        ]);

        // Pengajuan Kas Teller 1
        PengajuanKas::create([
            'nomor_pengajuan' => 'PK-TEST-001',
            'user_id' => $this->teller1->id,
            'nominal' => 250000,
            'alasan' => 'Tambah modal loket',
            'status' => 'pending',
            'tanggal' => $today,
        ]);

        $response = $this->actingAs($this->teller1)->get('/teller/dashboard');

        $response->assertStatus(200);

        // 1. Pencarian Nasabah Paling Atas
        $response->assertSee('Pencarian Rekening & Layani Nasabah', false);
        $response->assertSee('Scan Kartu / QR Siswa');

        // 2. Posisi Kas Loket Terpadu
        $response->assertSee('Posisi Kas Loket Teller Hari Ini');
        $response->assertSee('Saldo Awal Loket');
        $response->assertSee('Kas Masuk (Setor)');
        $response->assertSee('Kas Keluar (Tarik)');
        $response->assertSee('Kas Fisik Tersedia');

        // 3. Aktivitas Transaksi Terakhir Saya (hanya aktivitas teller 1)
        $response->assertSee('Aktivitas Transaksi Terakhir Saya');
        $response->assertSee('Setoran Rp 75.000');
        $response->assertSee('Pengajuan Kas Rp 250.000');
        $response->assertDontSee('Penarikan Rp 200.000');

        // 4. Petugas Avalis Bertugas (Ringkas)
        $response->assertSee('Petugas Avalis Bertugas Hari Ini');

        // 5. Memastikan widget generik Pegawai Aktif & Total Nasabah dihapus dari dashboard Teller
        $response->assertDontSee('Rekan Petugas Aktif');
        $response->assertDontSee('Total Nasabah Sekolah');
    }

    public function test_supervisor_dashboard_displays_all_four_primary_widgets_with_cross_teller_monitoring_and_actionable_badges(): void
    {
        $today = Carbon::today()->toDateString();
        Brankas::getOrCreateToday($today, false);

        // Transaksi Teller 1
        Transaksi::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'jenis_transaksi' => 'setor',
            'nominal' => 120000,
            'status' => 'selesai',
            'petugas_id' => $this->teller1->id,
            'created_at' => Carbon::now()->subMinutes(10),
        ]);

        // Transaksi Teller 2
        Transaksi::create([
            'nasabah_id' => $this->nasabahAktif->id,
            'jenis_transaksi' => 'tarik',
            'nominal' => 50000,
            'status' => 'selesai',
            'petugas_id' => $this->teller2->id,
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        // Pengajuan Kas Pending (Harus bertanda action required)
        PengajuanKas::create([
            'nomor_pengajuan' => 'PK-SPV-001',
            'user_id' => $this->teller1->id,
            'nominal' => 400000,
            'alasan' => 'Droping Kas Ujian',
            'status' => 'pending',
            'tanggal' => $today,
        ]);

        // Daily Report Pending
        DailyReport::create([
            'user_id' => $this->teller2->id,
            'report_date' => $today,
            'opening_balance' => 1000000,
            'total_cash_in' => 0,
            'total_cash_out' => 50000,
            'system_balance' => 950000,
            'physical_balance' => 950000,
            'difference' => 0,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->supervisor)->get('/supervisor/dashboard');

        $response->assertStatus(200);

        // 1. KPI Pengambilan Keputusan Utama
        $response->assertSee('Pengajuan Droping Kas');
        $response->assertSee('Daily Report Menunggu Verifikasi');
        $response->assertSee('Selisih Kas Rekonsiliasi');
        $response->assertSee('Kas Fisik Tersedia Loket');

        // 2. Pusat Persetujuan Terpadu dengan Tabs
        $response->assertSee('Pekerjaan Menunggu Tindakan (Approval Center)');
        $response->assertSee('Periksa Detail');

        // 3. Aktivitas dan Jejak Keputusan Terbaru
        $response->assertSee('Aktivitas dan Jejak Keputusan Terbaru');
        $response->assertSee('Setoran Rp 120.000');
        $response->assertSee('Penarikan Rp 50.000');
    }

    public function test_admin_employee_management_page_is_accessible_to_admin_and_lists_employees(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/data-pegawai');

        $response->assertStatus(200);
        $response->assertSee('Manajemen Pegawai & Hak Akses', false);
        $response->assertSee('Teller Satu');
        $response->assertSee('Supervisor Pengawas');
        $response->assertSee('Admin Utama');
        $response->assertSee('Edit Akses');
        $response->assertSee('Tambah Pegawai Baru');
    }

    public function test_global_aggregates_consistency_across_admin_teller_and_supervisor(): void
    {
        $widgetService = app(DashboardWidgetService::class);

        $nasabahSummary = $widgetService->getNasabahSummary();
        $pegawaiSummary = $widgetService->getPegawaiSummary();

        $this->assertEquals(2, $nasabahSummary['total']);
        $this->assertEquals(1, $nasabahSummary['aktif']);
        $this->assertEquals(1, $nasabahSummary['nonaktif']);

        $this->assertEquals(4, $pegawaiSummary['total_aktif']);
        $this->assertEquals(5, $pegawaiSummary['total_semua']);
        $this->assertEquals(1, $pegawaiSummary['nonaktif']);
        $this->assertEquals(2, $pegawaiSummary['roles']['teller']);
        $this->assertEquals(1, $pegawaiSummary['roles']['supervisor']);
        $this->assertEquals(1, $pegawaiSummary['roles']['admin']);
    }

    public function test_password_reset_widget_does_not_expose_passwords_or_tokens(): void
    {
        PasswordResetRequest::create([
            'username' => 'nasabah_rahasia',
            'nama_pemohon' => 'Siswa Rahasia',
            'metode' => 'email',
            'kontak' => 'rahasia@sekolah.id',
            'keterangan' => 'Permohonan tes keamanan',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('nasabah_rahasia');

        // Memastikan token / password tidak terekspos di markup widget
        $response->assertDontSee('password_reset_token');
        $response->assertDontSee('otp_code');
    }

    public function test_teller_cannot_access_employee_management(): void
    {
        $response = $this->actingAs($this->teller1)->get('/admin/tambah-pegawai');
        // Teller diarahkan atau ditolak karena pembatasan middleware role
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }
}
