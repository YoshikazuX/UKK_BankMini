<?php

namespace Tests\Feature;

use App\Models\Nasabah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NasabahActivationAndLoginTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'nama_petugas' => 'Admin Test',
            'username' => 'admin_test',
            'role' => 'admin',
            'status' => 'aktif',
        ]);
    }

    public function test_inactive_nasabah_cannot_login_and_sees_notification(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '24-RPL-001',
            'nis' => '19001',
            'nama_siswa' => 'Siswa Nonaktif',
            'kelas' => 'X RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '19001',
            'password' => 'someoldpassword',
            'pin_keamanan' => '123456',
            'status' => 'nonaktif',
        ]);

        // 1. Web Login attempt
        $webResponse = $this->post('/login', [
            'username' => '19001',
            'password' => 'someoldpassword',
        ]);

        $webResponse->assertSessionHas('error', 'Minta admin untuk mengaktifkan akun');
        $this->assertGuest();

        // 2. API Nasabah Login attempt
        $apiNasabahResponse = $this->postJson('/api/nasabah/login', [
            'username' => '19001',
            'password' => 'someoldpassword',
        ]);

        $apiNasabahResponse->assertStatus(403);
        $apiNasabahResponse->assertJson([
            'success' => false,
            'message' => 'Minta admin untuk mengaktifkan akun',
        ]);

        // 3. API Universal Login attempt
        $apiUniversalResponse = $this->postJson('/api/auth/login', [
            'username' => '19001',
            'password' => 'someoldpassword',
        ]);

        $apiUniversalResponse->assertStatus(403);
        $apiUniversalResponse->assertJson([
            'success' => false,
            'message' => 'Minta admin untuk mengaktifkan akun',
        ]);
    }

    public function test_activating_nasabah_generates_default_password_and_allows_login(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '24-RPL-002',
            'nis' => '19002',
            'nama_siswa' => 'Siswa Baru',
            'kelas' => 'X RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '19002',
            'password' => 'temporarypassword',
            'pin_keamanan' => '123456',
            'status' => 'nonaktif',
        ]);

        // Pastikan password lama bukan "password"
        $this->assertFalse(Hash::check('password', $nasabah->password));

        // Admin mengaktifkan akun via ubah-status
        $activateResponse = $this->actingAs($this->admin)->post("/admin/nasabah/ubah-status/{$nasabah->id}", [
            'status' => 'aktif',
        ]);

        $activateResponse->assertSessionHas('success');

        // Refresh model dan cek status serta password
        $nasabah->refresh();
        $this->assertEquals('aktif', $nasabah->status);
        $this->assertTrue(Hash::check('password', $nasabah->password), 'Password otomatis di-generate menjadi "password"');

        // Sekarang nasabah dapat login dengan password default "password"
        // 1. API Login
        $loginApiResponse = $this->postJson('/api/nasabah/login', [
            'username' => '19002',
            'password' => 'password',
        ]);

        $loginApiResponse->assertStatus(200);
        $loginApiResponse->assertJson([
            'success' => true,
            'message' => 'Login Berhasil',
        ]);

        // 2. Web Login
        $loginWebResponse = $this->post('/login', [
            'username' => '19002',
            'password' => 'password',
        ]);

        $loginWebResponse->assertRedirect('/nasabah/dashboard');
    }

    public function test_updating_nasabah_to_active_via_nasabah_controller_generates_password(): void
    {
        $nasabah = Nasabah::create([
            'no_rekening' => '24-RPL-003',
            'nis' => '19003',
            'nama_siswa' => 'Siswa Tiga',
            'kelas' => 'X RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'username' => '19003',
            'password' => 'oldpass123',
            'pin_keamanan' => '123456',
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/edit-nasabah/{$nasabah->id}", [
            'nama_siswa' => 'Siswa Tiga Update',
            'kelas' => 'X RPL 1',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'status' => 'aktif',
        ]);

        $response->assertRedirect('/admin/data-nasabah');

        $nasabah->refresh();
        $this->assertEquals('aktif', $nasabah->status);
        $this->assertTrue(Hash::check('password', $nasabah->password));
    }
}
