<?php
// app/Http/Controllers/Web/CommentController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreWebCommentRequest;
use App\Http\Requests\Web\UpdateWebCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    /**
     * コメントを投稿する。
     */
    public function store(StoreWebCommentRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();

        if (! empty($validated['parent_id'])) {
            $parentComment = Comment::query()
                ->whereKey($validated['parent_id'])
                ->where('post_id', $post->id)
                ->first();

            if (! $parentComment) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('comments.reply_parent_invalid')],
                ]);
            }
        }

        Comment::query()->create([
            'post_id' => $post->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $query = [];

        if ($request->filled('comment_sort')) {
            $query['comment_sort'] = $request->input('comment_sort');
        }

        $redirectUrl = route('posts.show', array_merge(['post' => $post], $query));

        if (! empty($validated['parent_id'])) {
            $redirectUrl .= '#comment-' . $validated['parent_id'];
        }

        return redirect($redirectUrl)->with('status', 'コメントを投稿しました。');
    }

    /**
     * コメントを更新する。
     */
    public function update(UpdateWebCommentRequest $request, Post $post, Comment $comment): RedirectResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $this->authorize('update', $comment);

        $comment->update($request->validated());

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'コメントを更新しました。');
    }

    /**
     * コメントを削除する。
     */
    public function destroy(Post $post, Comment $comment): RedirectResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()
            ->route('posts.show', $post)
            ->with('status', 'コメントを削除しました。');
    }
}
