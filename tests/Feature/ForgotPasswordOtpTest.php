<?php

namespace Tests\Feature;

use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_otp_without_truncation_error(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'username' => 'petugas_test',
            'nama_petugas' => 'Petugas Test',
            'email' => 'petugas@example.com',
            'role' => 'teller',
        ]);

        $response = $this->postJson(route('password.otp'), [
            'email' => 'petugas@example.com',
            'username' => 'petugas_test',
            'metode' => 'email',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('password_reset_requests', [
            'username' => 'petugas_test',
            'metode' => 'email',
            'status' => 'pending',
        ]);

        $ticket = PasswordResetRequest::where('username', 'petugas_test')->first();
        $this->assertNotNull($ticket);
        $this->assertGreaterThan(10, strlen((string) $ticket->otp_code));
    }

    public function test_incorrect_otp_code_is_rejected_and_cannot_proceed(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'username' => 'petugas_salah_otp',
            'email' => 'salah_otp@example.com',
        ]);

        // 1. Request OTP
        $this->postJson(route('password.otp'), [
            'email' => 'salah_otp@example.com',
            'metode' => 'email',
        ])->assertStatus(200);

        // 2. Submit wrong OTP code
        $wrongResponse = $this->postJson(route('password.verify-otp'), [
            'otp_code' => '999999',
        ]);

        $wrongResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertFalse(session('pwd_reset_otp_verified', false));

        // 3. Submit correct OTP code
        $correctOtp = session('pwd_reset_otp');
        $correctResponse = $this->postJson(route('password.verify-otp'), [
            'otp_code' => $correctOtp,
        ]);

        $correctResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertTrue(session('pwd_reset_otp_verified'));
    }

    public function test_user_can_verify_otp_and_reset_password(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'username' => 'petugas_reset',
            'password' => Hash::make('oldpassword123'),
            'email' => 'reset@example.com',
        ]);

        // Request OTP
        $this->postJson(route('password.otp'), [
            'email' => 'reset@example.com',
            'metode' => 'email',
        ])->assertStatus(200);

        $plainOtp = session('pwd_reset_otp');
        $this->assertNotEmpty($plainOtp);

        // Verify and reset
        $response = $this->postJson(route('password.reset'), [
            'otp_code' => $plainOtp,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));

        $ticket = PasswordResetRequest::where('username', 'petugas_reset')->first();
        $this->assertEquals('completed', $ticket->status);
    }
}
