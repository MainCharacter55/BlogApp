<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_endpoint_is_throttled_after_limit(): void
    {
        $email = 'throttle-user@example.com';
        User::factory()->create([
            'email' => $email,
            'password' => 'CorrectPass!123',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => $email,
                'password' => 'WrongPass!123',
            ]);

            $response->assertStatus(422);
        }

        $blockedResponse = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'WrongPass!123',
        ]);

        $blockedResponse->assertStatus(429);
    }
}
