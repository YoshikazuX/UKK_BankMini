<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Nasabah;
use App\Models\SavingGoal;
use App\Models\Transaksi;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NasabahMobileFiturLengkapTest extends TestCase
{
    use RefreshDatabase;

    private Nasabah $nasabah;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->nasabah = Nasabah::where('username', 'Handa')->firstOrFail();
        $this->token = $this->nasabah->createToken('test-token')->plainTextToken;
    }

    public function test_emergency_freeze_locks_account_and_records_audit_log(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/nasabah/emergency-freeze');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'no_rekening' => $this->nasabah->no_rekening,
                    'status' => 'nonaktif',
                ],
            ]);

        $this->nasabah->refresh();
        $this->assertEquals('nonaktif', $this->nasabah->status);

        // Audit log dicatat
        $this->assertDatabaseHas('audit_logs', [
            'jenis_aktivitas' => 'EMERGENCY_FREEZE_MANDIRI',
            'modul' => 'Nasabah',
            'objek_id' => (string) $this->nasabah->id,
        ]);

        // Coba freeze lagi saat sudah nonaktif -> harus ditolak (400)
        $secondResponse = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/nasabah/emergency-freeze');

        $secondResponse->assertStatus(400);
    }

    public function test_saving_goals_crud_and_calculation(): void
    {
        // 1. Create Goal
        $createRes = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/nasabah/goals', [
                'nama' => 'Laptop SMK',
                'target_amount' => 5000000,
                'current_amount' => 1000000,
                'icon' => 'laptop',
                'deadline' => '2026-12-31',
            ]);

        $createRes->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama' => 'Laptop SMK',
                    'target_amount' => 5000000,
                    'current_amount' => 1000000,
                    'progress_percent' => 20.0,
                    'remaining' => 4000000,
                ],
            ]);

        $goalId = $createRes->json('data.id');

        // 2. Index Goals
        $indexRes = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/nasabah/goals');

        $indexRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
            ]);

        // 3. Update Goal
        $updateRes = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/nasabah/goals/'.$goalId, [
                'nama' => 'Laptop Asus ROG',
                'current_amount' => 5000000,
            ]);

        $updateRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama' => 'Laptop Asus ROG',
                    'is_completed' => true,
                    'progress_percent' => 100.0,
                    'remaining' => 0.0,
                ],
            ]);

        // 4. Delete Goal
        $delRes = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/nasabah/goals/'.$goalId);

        $delRes->assertStatus(200);
        $this->assertDatabaseMissing('saving_goals', ['id' => $goalId]);
    }

    public function test_badges_evaluation_dynamically(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/nasabah/badges');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'nama',
                        'deskripsi',
                        'icon',
                        'tier',
                        'is_unlocked',
                    ],
                ],
            ]);
    }

    public function test_session_logs_retrieval(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/nasabah/sessions');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'device_name',
                        'device_type',
                        'ip_address',
                        'last_access',
                        'is_current_session',
                    ],
                ],
            ]);
    }

    public function test_notifications_retrieval_and_mark_read(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/notifikasi');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Mark all as read
        $markRes = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/notifikasi/read-all');

        $markRes->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_ringkasan_returns_6_months_trend(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/nasabah/ringkasan');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'no_rekening',
                    'nama_siswa',
                    'saldo',
                    'total_masuk',
                    'total_keluar',
                    'monthly_trend_6_months',
                    'average_monthly_deposit',
                ],
            ]);

        $trend = $response->json('data.monthly_trend_6_months');
        $this->assertCount(6, $trend);
    }
}

