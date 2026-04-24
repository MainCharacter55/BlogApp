<?php

namespace Tests\Feature;

use App\Models\PendingRegistration;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Str::createRandomStringsNormally();

        parent::tearDown();
    }

    public function test_guest_can_only_view_comments(): void
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertOk();
        $response->assertJsonStructure(['data']);
    }

    public function test_guest_cannot_post_comments(): void
    {
        $post = Post::factory()->create();

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'ログインが必要なコメントです。',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment([
            'message' => '認証が必要です。ログインしてから再度お試しください。',
        ]);
    }

    public function test_user_can_register_login_and_post_comment(): void
    {
        Mail::fake();
        Str::createRandomStringsUsing(fn () => 'fixed-registration-token-123456');

        $post = Post::factory()->create();
        $email = 'new-user@example.com';

        $requestTokenResponse = $this->postJson('/api/auth/register/request', [
            'email' => $email,
        ]);

        $requestTokenResponse->assertAccepted();

        PendingRegistration::query()->where('email', $email)->firstOrFail();

        $verifyResponse = $this->postJson('/api/auth/register/verify', [
            'email' => $email,
            'token' => 'fixed-registration-token-123456',
            'name' => 'Test User',
            'password' => 'StrongPass!123',
        ]);

        $verifyResponse->assertCreated();
        $verifyResponse->assertJsonStructure([
            'message',
            'data' => [
                'user',
                'token',
                'token_type',
            ],
        ]);

        Str::createRandomStringsNormally();

        $user = User::query()->where('email', $email)->firstOrFail();
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'StrongPass!123',
        ]);

        $loginResponse->assertOk();

        $token = $loginResponse->json('data.token');

        $commentResponse = $this->withToken($token)->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'これはログイン後に投稿したコメントです。',
        ]);

        $commentResponse->assertCreated();
        $commentResponse->assertJsonPath('data.user_id', $user->id);
    }
}
