<?php

namespace Tests\Feature\Auth;

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\OtpToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ─── Page Rendering ──────────────────────────────────────────────

    public function test_forgot_password_page_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_verify_otp_page_redirects_when_no_phone_in_session(): void
    {
        $response = $this->get('/verify-otp');

        $response->assertRedirect('/forgot-password');
    }

    public function test_reset_password_page_redirects_when_not_verified(): void
    {
        $response = $this->get('/reset-password');

        $response->assertRedirect('/forgot-password');
    }

    // ─── Send OTP ────────────────────────────────────────────────────

    public function test_send_otp_rejects_nonexistent_phone(): void
    {
        $response = $this->post('/forgot-password', [
            'phone' => '0999999999',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_send_otp_creates_token_and_dispatches_job(): void
    {
        Bus::fake([SendWhatsAppMessageJob::class]);

        $user = User::factory()->create(['phone' => '0912345678']);

        $response = $this->post('/forgot-password', [
            'phone' => '0912345678',
        ]);

        $response->assertRedirect('/verify-otp');
        $response->assertSessionHas('otp_phone', '0912345678');

        $this->assertDatabaseHas('otp_tokens', [
            'phone' => '0912345678',
        ]);

        Bus::assertDispatched(SendWhatsAppMessageJob::class, function ($job) {
            return $job->phone === '2' . '0912345678'
                && str_contains($job->message, '🔐');
        });
    }

    public function test_send_otp_is_rate_limited(): void
    {
        Bus::fake([SendWhatsAppMessageJob::class]);

        User::factory()->create(['phone' => '0912345678']);

        $this->post('/forgot-password', ['phone' => '0912345678']);
        $response = $this->post('/forgot-password', ['phone' => '0912345678']);

        $response->assertSessionHas('error');
    }

    // ─── Verify OTP ──────────────────────────────────────────────────

    public function test_verify_otp_rejects_wrong_code(): void
    {
        $user = User::factory()->create(['phone' => '0912345678']);
        $token = OtpToken::create([
            'phone' => '0912345678',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->withSession(['otp_phone' => '0912345678'])
            ->post('/verify-otp', [
                'phone' => '0912345678',
                'otp' => '999999',
            ]);

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('otp_tokens', [
            'id' => $token->id,
            'attempts' => 1,
        ]);
    }

    public function test_verify_otp_rejects_expired_code(): void
    {
        User::factory()->create(['phone' => '0912345678']);
        OtpToken::create([
            'phone' => '0912345678',
            'otp' => '123456',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->withSession(['otp_phone' => '0912345678'])
            ->post('/verify-otp', [
                'phone' => '0912345678',
                'otp' => '123456',
            ]);

        $response->assertSessionHas('error');
    }

    public function test_verify_otp_fails_after_max_attempts(): void
    {
        User::factory()->create(['phone' => '0912345678']);
        OtpToken::create([
            'phone' => '0912345678',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
            'attempts' => 5,
        ]);

        $response = $this->withSession(['otp_phone' => '0912345678'])
            ->post('/verify-otp', [
                'phone' => '0912345678',
                'otp' => '123456',
            ]);

        $response->assertSessionHas('error');
    }

    public function test_verify_otp_succeeds_with_valid_code(): void
    {
        User::factory()->create(['phone' => '0912345678']);
        OtpToken::create([
            'phone' => '0912345678',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->withSession(['otp_phone' => '0912345678'])
            ->post('/verify-otp', [
                'phone' => '0912345678',
                'otp' => '123456',
            ]);

        $response->assertRedirect('/reset-password');
        $response->assertSessionHas('otp_verified', true);
        $response->assertSessionHas('reset_phone', '0912345678');
        $response->assertSessionMissing('otp_phone');

        $this->assertDatabaseMissing('otp_tokens', [
            'phone' => '0912345678',
        ]);
    }

    // ─── Reset Password ──────────────────────────────────────────────

    public function test_reset_password_fails_without_verification_session(): void
    {
        $response = $this->post('/reset-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_reset_password_updates_password(): void
    {
        $user = User::factory()->create([
            'phone' => '0912345678',
            'password' => 'oldpassword',
        ]);

        $response = $this->withSession([
            'otp_verified' => true,
            'reset_phone' => '0912345678',
        ])->post('/reset-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_reset_password_requires_password_confirmation(): void
    {
        User::factory()->create(['phone' => '0912345678']);

        $response = $this->withSession([
            'otp_verified' => true,
            'reset_phone' => '0912345678',
        ])->post('/reset-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'different',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_reset_password_cleans_up_tokens(): void
    {
        User::factory()->create(['phone' => '0912345678']);
        OtpToken::create([
            'phone' => '0912345678',
            'otp' => '123456',
            'expires_at' => now()->addMinutes(10),
        ]);
        OtpToken::create([
            'phone' => '0912345678',
            'otp' => '654321',
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->withSession([
            'otp_verified' => true,
            'reset_phone' => '0912345678',
        ])->post('/reset-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $this->assertDatabaseMissing('otp_tokens', [
            'phone' => '0912345678',
        ]);
    }

    // ─── Full Flow ───────────────────────────────────────────────────

    public function test_complete_forgot_password_flow(): void
    {
        Bus::fake([SendWhatsAppMessageJob::class]);

        $user = User::factory()->create([
            'phone' => '0912345678',
            'password' => 'oldpassword',
        ]);

        // Step 1: Request OTP
        $this->post('/forgot-password', ['phone' => '0912345678'])
            ->assertRedirect('/verify-otp');

        Bus::assertDispatched(SendWhatsAppMessageJob::class);

        // Get the stored OTP from DB
        $otpToken = OtpToken::where('phone', '0912345678')->first();
        $this->assertNotNull($otpToken);

        // Step 2: Verify OTP
        $this->withSession(['otp_phone' => '0912345678'])
            ->post('/verify-otp', [
                'phone' => '0912345678',
                'otp' => $otpToken->otp,
            ])
            ->assertRedirect('/reset-password');

        // Step 3: Reset password
        $this->withSession([
            'otp_verified' => true,
            'reset_phone' => '0912345678',
        ])->post('/reset-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertRedirect('/login');

        // Assert can login with new password
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));

        // Assert tokens cleaned up
        $this->assertDatabaseMissing('otp_tokens', [
            'phone' => '0912345678',
        ]);
    }
}
