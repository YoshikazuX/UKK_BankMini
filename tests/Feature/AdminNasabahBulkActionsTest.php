<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Nasabah;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminNasabahBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customerService;

    private User $teller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'nama_petugas' => 'Administrator Utama',
            'username' => 'admin_bulk',
            'role' => 'admin',
        ]);

        $this->customerService = User::factory()->create([
            'nama_petugas' => 'CS Sekolah',
            'username' => 'cs_bulk',
            'role' => 'customer_service',
        ]);

        $this->teller = User::factory()->create([
            'nama_petugas' => 'Teller Kasir',
            'username' => 'teller_bulk',
            'role' => 'teller',
        ]);
    }

    private function createNasabah(array $overrides = []): Nasabah
    {
        $nis = $overrides['nis'] ?? (string) random_int(10000, 99999);

        return Nasabah::create(array_merge([
            'no_rekening' => '26-RPL-'.substr($nis, -3),
            'nis' => $nis,
            'nama_siswa' => 'Nasabah '.$nis,
            'kelas' => 'XI RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => $nis,
            'password' => Hash::make('123456'),
            'pin_keamanan' => Hash::make('123456'),
            'saldo' => 25000,
            'status' => 'aktif',
            'kategori' => 'siswa',
        ], $overrides));
    }

    public function test_hanya_admin_yang_melihat_tombol_dan_bisa_mengakses_aksi_massal_nasabah(): void
    {
        $this->createNasabah([
            'no_rekening' => '26-RPL-001',
            'nis' => '20001',
            'status' => 'aktif',
        ]);

        $resAdmin = $this->actingAs($this->admin)->get('/admin/data-nasabah');
        $resAdmin->assertOk();
        $resAdmin->assertSee('Nonaktifkan Semua Nasabah');
        $resAdmin->assertSee('Hapus Semua Nasabah');

        $resCs = $this->actingAs($this->customerService)->get('/admin/data-nasabah');
        $resCs->assertOk();
        $resCs->assertDontSee('Nonaktifkan Semua Nasabah');
        $resCs->assertDontSee('Hapus Semua Nasabah');

        foreach ([$this->customerService, $this->teller] as $nonAdmin) {
            $this->actingAs($nonAdmin)
                ->post('/admin/nasabah/nonaktifkan-semua')
                ->assertForbidden();

            $this->actingAs($nonAdmin)
                ->post('/admin/nasabah/hapus-semua', ['konfirmasi' => 'HAPUS SEMUA'])
                ->assertForbidden();
        }
    }

    public function test_admin_dapat_menonaktifkan_semua_nasabah_aktif_dan_mencatat_audit_log(): void
    {
        $aktif1 = $this->createNasabah([
            'no_rekening' => '26-RPL-011',
            'nis' => '21001',
            'status' => 'aktif',
            'saldo' => 100000,
        ]);

        $aktif2 = $this->createNasabah([
            'no_rekening' => '26-RPL-012',
            'nis' => '21002',
            'status' => 'aktif',
            'saldo' => 50000,
        ]);

        $alumni = $this->createNasabah([
            'no_rekening' => '24-RPL-013',
            'nis' => '21003',
            'status' => 'alumni',
            'kategori' => 'alumni',
            'saldo' => 0,
        ]);

        $res = $this->actingAs($this->admin)->post('/admin/nasabah/nonaktifkan-semua', [
            'alasan' => 'Penutupan tahun ajaran',
        ]);

        $res->assertRedirect('/admin/data-nasabah');
        $res->assertSessionHas('success');

        $this->assertDatabaseHas('nasabah', ['id' => $aktif1->id, 'status' => 'nonaktif', 'saldo' => 100000]);
        $this->assertDatabaseHas('nasabah', ['id' => $aktif2->id, 'status' => 'nonaktif', 'saldo' => 50000]);
        $this->assertDatabaseHas('nasabah', ['id' => $alumni->id, 'status' => 'alumni']);

        $audit = AuditLog::where('jenis_aktivitas', 'NONAKTIFKAN_SEMUA_NASABAH')->first();
        $this->assertNotNull($audit);
        $this->assertSame('Nasabah', $audit->modul);
        $this->assertSame('SEMUA', $audit->objek_id);
        $this->assertSame(2, $audit->new_values['jumlah_dinonaktifkan'] ?? null);
    }

    public function test_nonaktifkan_semua_ditolak_jika_tidak_ada_nasabah_aktif(): void
    {
        $this->createNasabah([
            'no_rekening' => '26-RPL-021',
            'nis' => '22001',
            'status' => 'nonaktif',
        ]);

        $res = $this->actingAs($this->admin)
            ->from('/admin/data-nasabah')
            ->post('/admin/nasabah/nonaktifkan-semua');

        $res->assertRedirect('/admin/data-nasabah');
        $res->assertSessionHas('error');
    }

    public function test_hapus_semua_nasabah_memerlukan_konfirmasi_dan_memblokir_jika_masih_ada_aktif(): void
    {
        $aktif = $this->createNasabah([
            'no_rekening' => '26-RPL-031',
            'nis' => '23001',
            'status' => 'aktif',
        ]);

        $nonaktif = $this->createNasabah([
            'no_rekening' => '26-RPL-032',
            'nis' => '23002',
            'status' => 'nonaktif',
        ]);

        $resSalahKonfirmasi = $this->actingAs($this->admin)
            ->from('/admin/data-nasabah')
            ->post('/admin/nasabah/hapus-semua', [
                'konfirmasi' => 'HAPUS',
            ]);

        $resSalahKonfirmasi->assertRedirect('/admin/data-nasabah');
        $resSalahKonfirmasi->assertSessionHasErrors('konfirmasi');

        $resMasihAktif = $this->actingAs($this->admin)
            ->from('/admin/data-nasabah')
            ->post('/admin/nasabah/hapus-semua', [
                'konfirmasi' => 'HAPUS SEMUA',
            ]);

        $resMasihAktif->assertRedirect('/admin/data-nasabah');
        $resMasihAktif->assertSessionHas('error');

        $this->assertNotSoftDeleted('nasabah', ['id' => $aktif->id]);
        $this->assertNotSoftDeleted('nasabah', ['id' => $nonaktif->id]);
    }

    public function test_admin_dapat_menghapus_semua_nasabah_setelah_dinonaktifkan_dan_riwayat_transaksi_tetap_utuh(): void
    {
        $nonaktif = $this->createNasabah([
            'no_rekening' => '26-RPL-041',
            'nis' => '24001',
            'nama_siswa' => 'Siswa Nonaktif Arsip',
            'status' => 'nonaktif',
            'saldo' => 50000,
        ]);

        $alumni = $this->createNasabah([
            'no_rekening' => '24-RPL-042',
            'nis' => '24002',
            'nama_siswa' => 'Alumni Arsip',
            'status' => 'alumni',
            'kategori' => 'alumni',
            'saldo' => 0,
        ]);

        $trx = Transaksi::create([
            'nasabah_id' => $nonaktif->id,
            'petugas_id' => $this->teller->id,
            'jenis_transaksi' => 'setor',
            'kode_sandi' => '101',
            'nominal' => 50000,
            'saldo_akhir' => 50000,
            'status' => 'sukses',
        ]);

        $res = $this->actingAs($this->admin)->post('/admin/nasabah/hapus-semua', [
            'konfirmasi' => 'HAPUS SEMUA',
            'alasan' => 'Pembersihan data massal',
        ]);

        $res->assertRedirect('/admin/data-nasabah');
        $res->assertSessionHas('success');

        $this->assertSoftDeleted('nasabah', ['id' => $nonaktif->id]);
        $this->assertSoftDeleted('nasabah', ['id' => $alumni->id]);

        $trxCheck = Transaksi::with('nasabah')->find($trx->id);
        $this->assertNotNull($trxCheck);
        $this->assertNotNull($trxCheck->nasabah);
        $this->assertSame('Siswa Nonaktif Arsip', $trxCheck->nasabah->nama_siswa);

        $audit = AuditLog::where('jenis_aktivitas', 'HAPUS_SEMUA_NASABAH')->first();
        $this->assertNotNull($audit);
        $this->assertSame('Nasabah', $audit->modul);
        $this->assertSame('SEMUA', $audit->objek_id);
        $this->assertSame(2, $audit->old_values['jumlah_terhapus'] ?? null);
    }
}

