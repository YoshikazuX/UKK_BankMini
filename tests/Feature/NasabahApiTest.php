<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NasabahApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_nasabah_login_success(): void
    {
        $response = $this->postJson('/api/nasabah/login', [
            'username' => 'Handa',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'token_type',
                'data' => [
                    'id_nasabah',
                    'username',
                    'nama_siswa',
                    'no_rekening',
                ],
            ])
            ->assertJsonMissingPath('data.saldo');
    }

    public function test_nasabah_logout(): void
    {
        $loginRes = $this->postJson('/api/nasabah/login', [
            'username' => 'Handa',
            'password' => 'password123',
        ]);

        $token = $loginRes->json('token');

        $logoutRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/nasabah/logout');

        $logoutRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout Berhasil',
            ]);
    }

    public function test_nasabah_login_failed_with_wrong_password(): void
    {
        $response = $this->postJson('/api/nasabah/login', [
            'username' => 'Handa',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Username atau password salah!',
            ]);
    }

    public function test_cek_saldo(): void
    {
        $response = $this->getJson('/api/nasabah/saldo?no_rekening=202601001');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'no_rekening' => '202601001',
                    'nama_siswa' => 'Dewi Handayani Nur Halimah',
                ],
            ]);
    }

    public function test_verify_pin(): void
    {
        $response = $this->postJson('/api/nasabah/verify-pin', [
            'no_rekening' => '202601001',
            'pin' => '240808',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Otorisasi PIN Berhasil!',
            ]);
    }

    public function test_admin_tambah_nasabah_dan_login(): void
    {
        $userAdmin = User::first();

        // 1. Simpan nasabah baru
        $response = $this->actingAs($userAdmin)->post('/admin/tambah-nasabah', [
            'nis' => '17799',
            'nama_siswa' => 'Budi Santoso',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'RPL',
            'no_rekening' => '10017799',
            'username' => 'budisantoso',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/data-nasabah');

        // 2. Pastikan data tersimpan langsung di tabel nasabah
        $this->assertDatabaseHas('nasabah', [
            'nis' => '17799',
            'username' => 'budisantoso',
        ]);

        // 3. Tes login API menggunakan akun yang baru dibuat
        $loginRes = $this->postJson('/api/nasabah/login', [
            'username' => 'budisantoso',
            'password' => 'secret123',
        ]);

        $loginRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'username' => 'budisantoso',
                    'nama_siswa' => 'Budi Santoso',
                    'no_rekening' => '10017799',
                ],
            ]);
    }

    public function test_admin_update_nasabah(): void
    {
        $userAdmin = User::first();
        $nasabah = DB::table('nasabah')->first();

        $response = $this->actingAs($userAdmin)->post('/admin/edit-nasabah/'.$nasabah->id, [
            'nis' => $nasabah->nis,
            'nama_siswa' => 'Dewi Handayani Update',
            'kelas' => $nasabah->kelas,
            'jurusan' => $nasabah->jurusan,
            'username' => 'dewibaru',
        ]);

        $response->assertRedirect('/admin/data-nasabah');

        $this->assertDatabaseHas('nasabah', [
            'id' => $nasabah->id,
            'nama_siswa' => 'Dewi Handayani Update',
            'username' => 'dewibaru',
        ]);
    }

    public function test_admin_hapus_nasabah(): void
    {
        $userAdmin = User::first();
        $nasabah = DB::table('nasabah')->first();

        $response = $this->actingAs($userAdmin)->get('/admin/hapus-nasabah/'.$nasabah->id);
        $response->assertRedirect('/admin/data-nasabah');

        $this->assertDatabaseMissing('nasabah', [
            'id' => $nasabah->id,
        ]);
    }

    public function test_cek_rekening_tujuan(): void
    {
        $response = $this->getJson('/api/nasabah/cek-rekening?no_rekening=202601001');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'no_rekening' => '202601001',
                    'nama_siswa' => 'Dewi Handayani Nur Halimah',
                ],
            ]);
    }

    public function test_transfer_saldo_dinonaktifkan(): void
    {
        $response = $this->postJson('/api/nasabah/transfer', [
            'no_rekening_pengirim' => '202601001',
            'no_rekening_tujuan' => '202601002',
            'nominal' => 15000,
            'pin' => '240808',
        ]);

        // Fitur transfer antar siswa telah dihapus sehingga route tidak tersedia (404)
        $response->assertStatus(404);
    }

    public function test_ganti_password(): void
    {
        $response = $this->postJson('/api/nasabah/ganti-password', [
            'no_rekening' => '202601001',
            'password_lama' => 'password123',
            'password_baru' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password login berhasil diperbarui!',
            ]);

        // Login dengan password baru
        $loginRes = $this->postJson('/api/nasabah/login', [
            'username' => 'Handa',
            'password' => 'newpassword123',
        ]);

        $loginRes->assertStatus(200);
    }

    public function test_ganti_pin(): void
    {
        $response = $this->postJson('/api/nasabah/ganti-pin', [
            'no_rekening' => '202601001',
            'pin_lama' => '240808',
            'pin_baru' => '654321',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'PIN transaksi 6-digit berhasil diperbarui!',
            ]);

        // Verifikasi PIN baru
        $verifyRes = $this->postJson('/api/nasabah/verify-pin', [
            'no_rekening' => '202601001',
            'pin' => '654321',
        ]);

        $verifyRes->assertStatus(200);
    }

    public function test_ringkasan_keuangan(): void
    {
        $response = $this->getJson('/api/nasabah/ringkasan?no_rekening=202601001');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'no_rekening' => '202601001',
                    'saldo' => 50000,
                ],
            ]);
    }
}
