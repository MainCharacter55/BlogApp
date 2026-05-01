<?php
// app/Http/Controllers/Api/CommentController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiCommentRequest;
use App\Http\Requests\UpdateApiCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * 投稿コメントの API エンドポイントを提供するコントローラー。
 */
class CommentController extends Controller
{
    /**
     * 指定した投稿のコメント一覧を新着順で取得する。
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
    public function update(UpdateApiCommentRequest $request, Post $post, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment->update($request->validated());

        return (new CommentResource($comment))->response();
    }

    /**
     * 指定したコメントを削除する。
     */
    public function destroy(Post $post, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(null, 204);
    }
}
