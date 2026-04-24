<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_post_without_user_id_and_cannot_impersonate(): void
    {
        $post = Post::factory()->create();
        $authenticatedUser = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($authenticatedUser);

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'これは認可済みユーザーによる投稿コメントです。',
            'user_id' => $otherUser->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.user_id', $authenticatedUser->id);
    }

    public function test_authenticated_user_can_reply_to_an_existing_comment(): void
    {
        $post = Post::factory()->create();
        $authenticatedUser = User::factory()->create();
        $parentComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $authenticatedUser->id,
        ]);

        Sanctum::actingAs($authenticatedUser);

        $response = $this->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'これは親コメントへの返信です。',
            'parent_id' => $parentComment->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.parent_id', $parentComment->id);
    }

    public function test_non_owner_non_admin_cannot_update_or_delete_comment(): void
    {
        $post = Post::factory()->create();
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($attacker);

        $updateResponse = $this->patchJson("/api/posts/{$post->id}/comments/{$comment->id}", [
            'content' => 'これは不正更新を試みるテキストです。',
        ]);

        $deleteResponse = $this->deleteJson("/api/posts/{$post->id}/comments/{$comment->id}");

        $updateResponse->assertForbidden();
        $deleteResponse->assertForbidden();
    }

    public function test_admin_can_update_and_delete_other_users_comment(): void
    {
        $post = Post::factory()->create();
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($admin);

        $updateResponse = $this->patchJson("/api/posts/{$post->id}/comments/{$comment->id}", [
            'content' => 'これは管理者による代理更新コメントです。',
        ]);

        $deleteResponse = $this->deleteJson("/api/posts/{$post->id}/comments/{$comment->id}");

        $updateResponse->assertOk();
        $updateResponse->assertJsonPath('data.user_id', $owner->id);
        $deleteResponse->assertNoContent();
    }
}
