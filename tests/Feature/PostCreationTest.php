<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PostCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_post_via_api(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/posts', [
            'title' => 'API から作成した記事',
            'content' => 'これは API で作成された新しい投稿の本文です。',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.title', 'API から作成した記事');
        $this->assertDatabaseHas('posts', [
            'title' => 'API から作成した記事',
        ]);
    }

    public function test_non_admin_cannot_create_post_via_api(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/posts', [
            'title' => '権限外の投稿',
            'content' => 'この投稿は一般ユーザーでは作成できません。',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('posts', [
            'title' => '権限外の投稿',
        ]);
    }
}