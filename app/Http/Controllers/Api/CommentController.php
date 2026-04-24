<?php
// app/Http/Controllers/Api/CommentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * 指定した記事のコメント一覧を新着順で取得する。
     */
    public function index(Post $post): AnonymousResourceCollection
    {
        $comments = $post->comments()
            ->latest()
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    /**
     * 指定した記事にコメントを投稿する。
     */
    public function store(StoreApiCommentRequest $request, Post $post): JsonResponse
    {
        $validated = $request->validated();

        if (! empty($validated['parent_id'])) {
            $parentComment = Comment::query()
                ->whereKey($validated['parent_id'])
                ->where('post_id', $post->id)
                ->first();

            if (! $parentComment) {
                return response()->json([
                    'message' => __('comments.reply_parent_invalid'),
                ], 422);
            }
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 指定したコメントを更新する。
     */
    public function update(UpdateCommentRequest $request, Post $post, Comment $comment): JsonResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $this->authorize('update', $comment);

        $comment->update($request->validated());

        return (new CommentResource($comment))->response();
    }

    /**
     * 指定したコメントを削除する。
     */
    public function destroy(Post $post, Comment $comment): JsonResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(null, 204);
    }
}
