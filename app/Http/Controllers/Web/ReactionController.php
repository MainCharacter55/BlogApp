<?php
// app/Http/Controllers/Web/ReactionController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SetReactionRequest;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class ReactionController extends Controller
{
    /**
     * 投稿へのリアクションをトグルする。
     */
    public function togglePostReaction(SetReactionRequest $request, Post $post): RedirectResponse|JsonResponse
    {
        $user = request()->user();

        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $validated = $request->validated();

        $currentReaction = PostReaction::query()
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($currentReaction && $currentReaction->reaction === $validated['reaction']) {
            $currentReaction->delete();
            $message = '投稿のリアクションを解除しました。';
        } else {
            PostReaction::query()->updateOrCreate(
                [
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                ],
                [
                    'reaction' => $validated['reaction'],
                ]
            );
            $message = '投稿にリアクションしました。';
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('status', $message);
    }

    /**
     * コメントへのリアクションをトグルする。
     */
    public function toggleCommentReaction(SetReactionRequest $request, Post $post, Comment $comment): RedirectResponse|JsonResponse
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $user = request()->user();

        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('login');
        }

        $validated = $request->validated();

        $currentReaction = CommentReaction::query()
            ->where('comment_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($currentReaction && $currentReaction->reaction === $validated['reaction']) {
            $currentReaction->delete();
            $message = 'コメントのリアクションを解除しました。';
        } else {
            CommentReaction::query()->updateOrCreate(
                [
                    'comment_id' => $comment->id,
                    'user_id' => $user->id,
                ],
                [
                    'reaction' => $validated['reaction'],
                ]
            );
            $message = 'コメントにリアクションしました。';
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('status', $message);
    }
}
