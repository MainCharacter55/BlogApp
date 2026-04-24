<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    public function test_registration_request_endpoint_is_throttled_after_limit(): void
    {
        $email = 'rate-register@example.com';

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $response = $this->postJson('/api/auth/register/request', [
                'email' => $email,
            ]);

            $response->assertStatus(202);
        }

        $blockedResponse = $this->postJson('/api/auth/register/request', [
            'email' => $email,
        ]);

        $blockedResponse->assertStatus(429);
    }

    public function test_api_comment_write_endpoint_is_throttled_after_limit(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        Sanctum::actingAs($user);

        for ($attempt = 0; $attempt < 8; $attempt++) {
            $response = $this->postJson("/api/posts/{$post->id}/comments", [
                'content' => 'スロットル確認用コメントです。'.$attempt,
            ]);

            $response->assertStatus(201);
        }

        $blockedResponse = $this->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'スロットル上限超過を確認するコメントです。',
        ]);

        $blockedResponse->assertStatus(429);
    }

    public function test_api_comment_mutate_endpoint_is_throttled_after_limit(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $response = $this->patchJson("/api/posts/{$post->id}/comments/{$comment->id}", [
                'content' => 'ミューテーション制限テスト用コメントです。'.$attempt,
            ]);

            $response->assertStatus(200);
        }

        $blockedResponse = $this->patchJson("/api/posts/{$post->id}/comments/{$comment->id}", [
            'content' => 'ミューテーション上限超過を確認するコメントです。',
        ]);

        $blockedResponse->assertStatus(429);
    }
}
