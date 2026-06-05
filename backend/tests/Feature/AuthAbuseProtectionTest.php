<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthAbuseProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_rate_limited_by_ip(): void
    {
        for ($index = 1; $index <= 3; $index++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
                ->postJson('/api/register', $this->registrationPayload([
                'email' => "new-user-{$index}@example.com",
            ]))->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->postJson('/api/register', $this->registrationPayload([
            'email' => 'new-user-4@example.com',
        ]))
            ->assertStatus(429)
            ->assertJsonPath('message', 'Too many registration attempts. Please wait before trying again.');
    }

    public function test_registration_normalizes_email_to_lowercase(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
            ->postJson('/api/register', $this->registrationPayload([
            'email' => 'MixedCase@Example.COM',
        ]))->assertOk();

        $this->assertDatabaseHas('users', [
            'email' => 'mixedcase@example.com',
        ]);
    }

    public function test_resend_otp_is_rate_limited_by_email(): void
    {
        User::factory()->unverified()->create([
            'email' => 'pending@example.com',
        ]);

        for ($index = 1; $index <= 3; $index++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
                ->postJson('/api/resend-otp', [
                'email' => 'pending@example.com',
            ])->assertOk();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
            ->postJson('/api/resend-otp', [
            'email' => 'pending@example.com',
        ])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Too many OTP requests. Please wait before requesting another code.');
    }

    public function test_unverified_users_cannot_access_protected_api_routes(): void
    {
        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertStatus(403)
            ->assertJsonPath('code', 'email_not_verified');
    }

    private function registrationPayload(array $overrides = []): array
    {
        return [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            ...$overrides,
        ];
    }
}
