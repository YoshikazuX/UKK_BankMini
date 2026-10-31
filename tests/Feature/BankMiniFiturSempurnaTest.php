<?php

namespace Tests\Feature;

use App\Helpers\AuditHelper;
use App\Models\AbsensiAvalis;
use App\Models\AuditLog;
use App\Models\Nasabah;
use App\Models\PasswordResetRequest;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BankMiniFiturSempurnaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $supervisor;

    private User $teller;

    private User $customerService;

    private User $avalis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'nama_petugas' => 'Admin Utama',
            'username' => 'admin_test',
            'role' => 'admin',
        ]);

        $this->supervisor = User::factory()->create([
            'nama_petugas' => 'Supervisor Operasional',
            'username' => 'spv_test',
            'role' => 'supervisor',
        ]);

        $this->teller = User::factory()->create([
            'nama_petugas' => 'Teller Loket 1',
            'username' => 'teller_test',
            'role' => 'teller',
        ]);

        $this->customerService = User::factory()->create([
            'nama_petugas' => 'Customer Service 1',
            'username' => 'cs_test',
            'role' => 'customer_service',
        ]);

        $this->avalis = User::factory()->create([
            'nama_petugas' => 'Petugas Avalis 1',
            'username' => 'avalis_test',
            'role' => 'avalis',
        ]);
    }

    /**
     * FITUR 1: Filter dan Pencarian Data Nasabah
     */
    public function test_fitur_1_filter_dan_pencarian_nasabah(): void
    {
        Nasabah::create([
            'no_rekening' => '26-RPL-001',
            'nis' => '1001',
            'nama_siswa' => 'Ahmad Santoso',
            'kelas' => 'XII RPL 1',
            'jurusan' => 'RPL',
            'username' => 'ahmadrpl',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 150000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        Nasabah::create([
            'no_rekening' => '26-AKL-002',
            'nis' => '1002',
            'nama_siswa' => 'Budi Guru',
            'kelas' => 'Guru',
            'jurusan' => 'AKL',
            'username' => 'budiguru',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 500000,
            'status' => 'nonaktif',
            'kategori' => 'guru',
        ]);

        // 1. Search by name
        $response = $this->actingAs($this->admin)->get('/admin/data-nasabah?cari=Ahmad');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Santoso');
        $response->assertDontSee('Budi Guru');

        // 2. Filter by status & kategori
        $response = $this->actingAs($this->admin)->get('/admin/data-nasabah?status=nonaktif&kategori=guru');
        $response->assertStatus(200);
        $response->assertSee('Budi Guru');
        $response->assertDontSee('Ahmad Santoso');
    }

    /**
     * FITUR 2: Halaman Detail Nasabah
     */
    public function test_fitur_2_halaman_detail_nasabah_menampilkan_saldo_dan_riwayat(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-003',
            'nis' => '1003',
            'nama_siswa' => 'Citra Lestari',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'RPL',
            'username' => 'citra',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 200000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        Transaksi::create([
            'nasabah_id' => $nasabah->id,
            'petugas_id' => $this->teller->id,
            'nama_petugas' => $this->teller->nama_petugas,
            'jenis_transaksi' => 'setor',
            'nominal' => 100000,
            'status' => 'sukses',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/data-nasabah/'.$nasabah->id);
        $response->assertStatus(200);
        $response->assertSee('Citra Lestari');
        $response->assertSee('26-RPL-003');
        $response->assertSee('Saldo Mengendap');
        $response->assertSee('Saldo Dapat Ditarik');
        // Pastikan password & pin tidak bocor di view
        $response->assertDontSee($nasabah->password);
    }

    /**
     * FITUR 3: Riwayat Perubahan Data (Audit Trail)
     */
    public function test_fitur_3_audit_trail_mencatat_aktivitas_dan_menyensor_kredensial(): void
    {
        AuditHelper::log(
            jenisAktivitas: 'UPDATE_NASABAH',
            modul: 'Nasabah',
            objekId: '26-RPL-001',
            oldValues: ['nama_siswa' => 'Nama Lama', 'password' => 'secret_old_pwd', 'pin_keamanan' => '123456'],
            newValues: ['nama_siswa' => 'Nama Baru', 'password' => 'secret_new_pwd', 'pin_keamanan' => '654321'],
            user: $this->admin
        );

        $log = AuditLog::first();
        $this->assertNotNull($log);
        $this->assertEquals('UPDATE_NASABAH', $log->jenis_aktivitas);
        $this->assertEquals('Nama Baru', $log->new_values['nama_siswa']);

        // Verifikasi penyensoran data sensitif
        $this->assertEquals('[DIHAPUS / TERLINDUNGI]', $log->old_values['password']);
        $this->assertEquals('[DIHAPUS / TERLINDUNGI]', $log->new_values['password']);
        $this->assertEquals('[DIHAPUS / TERLINDUNGI]', $log->old_values['pin_keamanan']);
        $this->assertEquals('[DIHAPUS / TERLINDUNGI]', $log->new_values['pin_keamanan']);

        // Verifikasi akses halaman audit
        $response = $this->actingAs($this->admin)->get('/admin/audit');
        $response->assertStatus(200);
        $response->assertSee('UPDATE_NASABAH');
        $response->assertSee('26-RPL-001');
    }

    /**
     * FITUR 4: RBAC & Segregation of Duties (SoD)
     */
    public function test_fitur_4_rbac_dan_sod_pembatasan_wewenang(): void
    {
        // 1. Admin & Supervisor DILARANG akses teller/transaksi (SoD)
        $response = $this->actingAs($this->admin)->get('/teller/transaksi/1');
        $response->assertRedirect('/admin/dashboard');

        $response = $this->actingAs($this->supervisor)->get('/teller/transaksi/1');
        $response->assertRedirect('/supervisor/dashboard');

        // 2. Customer Service HANYA boleh akses manajemen nasabah, DITOLAK dari audit log & konfigurasi
        $response = $this->actingAs($this->customerService)->get('/admin/data-nasabah');
        $response->assertStatus(200);

        $response = $this->actingAs($this->customerService)->get('/admin/audit');
        $response->assertStatus(403);

        // 3. Avalis DITOLAK dari transaksi keuangan loket
        $response = $this->actingAs($this->avalis)->get('/teller/dashboard');
        $response->assertStatus(403);
    }

    /**
     * FITUR 5: Pengelolaan Permohonan Reset Password
     */
    public function test_fitur_5_pengelolaan_permohonan_reset_password(): void
    {
        $targetUser = User::factory()->create([
            'username' => 'petugas_lupa',
            'nama_petugas' => 'Petugas Lupa',
            'role' => 'teller',
        ]);

        $ticket = PasswordResetRequest::create([
            'user_id' => $targetUser->id,
            'username' => $targetUser->username,
            'nama_pemohon' => $targetUser->nama_petugas,
            'role' => $targetUser->role,
            'metode' => 'tiket_admin',
            'kontak' => '081234567890',
            'keterangan' => 'Lupa password setelah cuti',
            'status' => 'pending',
        ]);

        // Verifikasi admin melihat tiket
        $response = $this->actingAs($this->admin)->get('/admin/reset-password-requests');
        $response->assertStatus(200);
        $response->assertSee('petugas_lupa');

        // Admin menyetujui tiket
        $response = $this->actingAs($this->admin)->post('/admin/reset-password-request/'.$ticket->id.'/approve');
        $response->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('completed', $ticket->status);
        $this->assertEquals($this->admin->id, $ticket->handled_by);
        $this->assertNotNull($ticket->handled_at);

        // Verifikasi audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'jenis_aktivitas' => 'RESET_PASSWORD_APPROVE',
        ]);
    }

    /**
     * FITUR 6: Monitoring dan Rekapitulasi Absensi Avalis
     */
    public function test_fitur_6_monitoring_dan_rekapitulasi_absensi(): void
    {
        $today = Carbon::today()->toDateString();

        AbsensiAvalis::create([
            'tanggal' => $today,
            'nama' => 'Petugas Satu',
            'kelas' => 'XII RPL 1',
            'no_wa' => '081234567890',
            'shift_jaga' => 'Shift Pagi (07.30 - 11.30)',
            'status' => 'Hadir',
            'keterangan' => 'Loket 1',
        ]);

        AbsensiAvalis::create([
            'tanggal' => $today,
            'nama' => 'Petugas Dua',
            'kelas' => 'XII RPL 2',
            'no_wa' => '081234567891',
            'shift_jaga' => 'Shift Siang (11.30 - 15.30)',
            'status' => 'Izin',
            'keterangan' => 'Dispensasi lomba',
        ]);

        // Verifikasi data absensi dan rekapitulasi bulanan di view
        $response = $this->actingAs($this->admin)->get('/absensi-avalis?shift=Pagi');
        $response->assertStatus(200);
        $response->assertSee('Petugas Satu');
        $response->assertSee('Rekapitulasi Bulanan Petugas Avalis');

        // Filter rentang tanggal
        $response = $this->actingAs($this->admin)->get('/absensi-avalis?tgl_dari='.$today.'&tgl_sampai='.$today);
        $response->assertStatus(200);
        $response->assertSee('Petugas Satu');
        $response->assertSee('Petugas Dua');
    }

    /**
     * FITUR 8: Validasi Pengaturan Profil
     */
    public function test_fitur_8_validasi_pengaturan_profil_dan_audit(): void
    {
        // 1. Admin update profil dengan nama dan email valid
        $response = $this->actingAs($this->admin)->post('/admin/profil/update', [
            'nama_petugas' => 'Admin Utama Diperbarui',
            'email' => 'admin.baru@banksekolah.id',
            'no_hp' => '081299998888',
        ]);
        $response->assertRedirect();

        $this->admin->refresh();
        $this->assertEquals('Admin Utama Diperbarui', $this->admin->nama_petugas);
        $this->assertEquals('admin.baru@banksekolah.id', $this->admin->email);

        // 2. Format email salah harus ditolak
        $response = $this->actingAs($this->admin)->post('/admin/profil/update', [
            'nama_petugas' => 'Admin Utama',
            'email' => 'bukan-email-valid',
        ]);
        $response->assertSessionHasErrors(['email']);

        // 3. Update profil nasabah via mobile API
        $nasabah = Nasabah::create([
            'no_rekening' => '26-RPL-099',
            'nis' => '1099',
            'nama_siswa' => 'Siswa Mobile',
            'kelas' => 'X RPL 1',
            'jurusan' => 'RPL',
            'username' => 'siswamobile',
            'password' => Hash::make('secret'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 50000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ]);

        $token = $nasabah->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/nasabah/update-profil', [
                'email' => 'siswa@sekolah.id',
                'no_hp' => '081234567899',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'siswa@sekolah.id',
                    'no_hp' => '081234567899',
                ],
            ]);

        $nasabah->refresh();
        $this->assertEquals('siswa@sekolah.id', $nasabah->email);
    }
}
