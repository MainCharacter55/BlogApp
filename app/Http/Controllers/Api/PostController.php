<?php
// app/Http/Controllers/Api/PostController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiPostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * 投稿の参照・作成 API を提供するコントローラー。
 */
class PostController extends Controller
{
    /**
     * 投稿一覧を取得する。
     */
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->latest()
            ->paginate(20);

        return PostResource::collection($posts);
    }

    /**
     * 新しい投稿を作成する。
     */
    public function store(StoreApiPostRequest $request): JsonResponse
    {
        $post = Post::query()->create($request->validated());

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }
}
