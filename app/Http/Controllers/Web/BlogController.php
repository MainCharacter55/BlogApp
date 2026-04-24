<?php
// app/Http/Controllers/Web/BlogController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    /**
     * ブログのトップページを表示する。
     */
    public function index(): Response
    {
        return response()->view('home');
    }

    /**
     * 新着記事一覧ページを表示する。
     */
    public function recent(): Response
    {
        $postReactionScoreExpression = $this->postReactionScoreExpression();

        $posts = Post::query()
            ->select('posts.*')
            ->selectRaw('(SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.deleted_at IS NULL) as comments_count')
            ->selectRaw('(SELECT COUNT(*) FROM post_reactions WHERE post_reactions.post_id = posts.id) as reactions_count')
            ->selectRaw("COALESCE({$postReactionScoreExpression}, 0) as reaction_score")
            ->latest()
            ->paginate(8)
            ->withQueryString();

        $postIds = $posts->pluck('id')->all();
        $postReactionSummaries = [];

        if ($postIds !== []) {
            $rawPostReactionRows = DB::table('post_reactions')
                ->whereIn('post_id', $postIds)
                ->select('post_id', 'reaction', DB::raw('COUNT(*) as total'))
                ->groupBy('post_id', 'reaction')
                ->get();

            foreach ($rawPostReactionRows as $row) {
                $postId = (int) $row->post_id;

                if (! isset($postReactionSummaries[$postId])) {
                    $postReactionSummaries[$postId] = [];
                }

                $postReactionSummaries[$postId][$row->reaction] = (int) $row->total;
            }
        }

        $currentUserPostReactions = [];

        if (auth()->check() && $postIds !== []) {
            $currentUserPostReactions = auth()->user()
                ->postReactions()
                ->whereIn('post_id', $postIds)
                ->pluck('reaction', 'post_id')
                ->toArray();
        }

        return response()->view('posts.recent', [
            'posts' => $posts,
            'postReactionSummaries' => $postReactionSummaries,
            'currentUserPostReactions' => $currentUserPostReactions,
            'reactionOptions' => config('reactions.options', []),
        ]);
    }

    /**
     * 人気記事一覧ページを表示する。
     */
    public function popular(): Response
    {
        $postReactionScoreExpression = $this->postReactionScoreExpression();

        $posts = Post::query()
            ->select('posts.*')
            ->selectRaw('(SELECT COUNT(*) FROM post_reactions WHERE post_reactions.post_id = posts.id) as reactions_count')
            ->selectRaw('(SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.deleted_at IS NULL) as comments_count')
            ->selectRaw("COALESCE({$postReactionScoreExpression}, 0) as reaction_score")
            ->selectRaw("(COALESCE({$postReactionScoreExpression}, 0) * 1.5) + ((SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.deleted_at IS NULL) * 2) + (views_count * 0.2) as popularity_score")
            ->orderByRaw("(COALESCE({$postReactionScoreExpression}, 0) * 1.5) + ((SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id AND comments.deleted_at IS NULL) * 2) + (views_count * 0.2) DESC")
            ->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString();

        $postIds = $posts->pluck('id')->all();
        $postReactionSummaries = [];

        if ($postIds !== []) {
            $rawPostReactionRows = DB::table('post_reactions')
                ->whereIn('post_id', $postIds)
                ->select('post_id', 'reaction', DB::raw('COUNT(*) as total'))
                ->groupBy('post_id', 'reaction')
                ->get();

            foreach ($rawPostReactionRows as $row) {
                $postId = (int) $row->post_id;

                if (! isset($postReactionSummaries[$postId])) {
                    $postReactionSummaries[$postId] = [];
                }

                $postReactionSummaries[$postId][$row->reaction] = (int) $row->total;
            }
        }

        $currentUserPostReactions = [];

        if (auth()->check() && $postIds !== []) {
            $currentUserPostReactions = auth()->user()
                ->postReactions()
                ->whereIn('post_id', $postIds)
                ->pluck('reaction', 'post_id')
                ->toArray();
        }

        return response()->view('posts.popular', [
            'posts' => $posts,
            'postReactionSummaries' => $postReactionSummaries,
            'currentUserPostReactions' => $currentUserPostReactions,
            'reactionOptions' => config('reactions.options', []),
        ]);
    }

    /**
     * 記事詳細ページを表示する。
     */
    public function show(Post $post): Response
    {
        $viewerKey = auth()->id() ? 'user:' . auth()->id() : 'ip:' . (request()->ip() ?? 'unknown');
        $cacheKey = 'post:view:' . $post->id . ':' . $viewerKey;
        $commentSort = request()->string('comment_sort')->toString() === 'popular' ? 'popular' : 'new';

        if (Cache::add($cacheKey, true, now()->addMinutes(30))) {
            $post->increment('views_count');
        }

        $post->refresh();

        $post->loadCount(['comments']);

        $postReactionsSummary = DB::table('post_reactions')
            ->where('post_id', $post->id)
            ->select('reaction', DB::raw('COUNT(*) as total'))
            ->groupBy('reaction')
            ->pluck('total', 'reaction')
            ->all();

        $postReactionsCount = array_sum($postReactionsSummary);
        $currentUserPostReaction = null;

        if (auth()->check()) {
            $currentUserPostReaction = DB::table('post_reactions')
                ->where('post_id', $post->id)
                ->where('user_id', auth()->id())
                ->value('reaction');
        }

        $commentReactionScoreExpression = $this->commentReactionScoreExpression();

        $comments = Comment::query()
            ->where('post_id', $post->id)
            ->with('user:id,name')
            ->select('comments.*')
            ->selectRaw('(SELECT COUNT(*) FROM comments as child_comments WHERE child_comments.parent_id = comments.id AND child_comments.deleted_at IS NULL) as replies_count')
            ->selectRaw('(SELECT COUNT(*) FROM comment_reactions WHERE comment_reactions.comment_id = comments.id) as reactions_count')
            ->selectRaw("COALESCE({$commentReactionScoreExpression}, 0) as reaction_score")
            ->when($commentSort === 'popular', function ($query): void {
                $query
                    ->orderByRaw('((reaction_score * 1.5) + (replies_count * 2)) DESC')
                    ->orderByDesc('created_at');
            }, function ($query): void {
                $query->orderByDesc('created_at');
            })
            ->get();

        $commentsByParentId = $comments->groupBy(function (Comment $comment): int {
            return $comment->parent_id ?? 0;
        });

        $commentReactionSummaries = [];
        $commentIds = $comments->pluck('id')->all();

        if ($commentIds !== []) {
            $rawCommentReactionRows = DB::table('comment_reactions')
                ->whereIn('comment_id', $commentIds)
                ->select('comment_id', 'reaction', DB::raw('COUNT(*) as total'))
                ->groupBy('comment_id', 'reaction')
                ->get();

            foreach ($rawCommentReactionRows as $row) {
                $commentId = (int) $row->comment_id;

                if (! isset($commentReactionSummaries[$commentId])) {
                    $commentReactionSummaries[$commentId] = [];
                }

                $commentReactionSummaries[$commentId][$row->reaction] = (int) $row->total;
            }
        }

        $currentUserCommentReactions = [];

        if (auth()->check()) {
            $user = auth()->user();

            if ($commentIds !== []) {
                $currentUserCommentReactions = $user->commentReactions()
                    ->whereIn('comment_id', $commentIds)
                    ->pluck('reaction', 'comment_id')
                    ->toArray();
            }
        }

        return response()->view('posts.show', [
            'post' => $post,
            'commentsByParentId' => $commentsByParentId,
            'postReactionsSummary' => $postReactionsSummary,
            'postReactionsCount' => $postReactionsCount,
            'currentUserPostReaction' => $currentUserPostReaction,
            'reactionOptions' => config('reactions.options', []),
            'commentReactionSummaries' => $commentReactionSummaries,
            'currentUserCommentReactions' => $currentUserCommentReactions,
            'commentSort' => $commentSort,
        ]);
    }

    /**
     * 投稿リアクションスコアを算出する副問い合わせを返す。
     */
    private function postReactionScoreExpression(): string
    {
        return '(SELECT SUM(' . $this->reactionWeightCaseExpression('post_reactions.reaction') . ') FROM post_reactions WHERE post_reactions.post_id = posts.id)';
    }

    /**
     * コメントリアクションスコアを算出する副問い合わせを返す。
     */
    private function commentReactionScoreExpression(): string
    {
        return '(SELECT SUM(' . $this->reactionWeightCaseExpression('comment_reactions.reaction') . ') FROM comment_reactions WHERE comment_reactions.comment_id = comments.id)';
    }

    /**
     * リアクション種別を重みへ変換する CASE 式を返す。
     */
    private function reactionWeightCaseExpression(string $reactionColumn): string
    {
        $options = config('reactions.options', []);
        $cases = [];

        foreach ($options as $reaction => $meta) {
            $weight = (int) ($meta['weight'] ?? 0);
            $cases[] = "WHEN '{$reaction}' THEN {$weight}";
        }

        return 'CASE ' . $reactionColumn . ' ' . implode(' ', $cases) . ' ELSE 0 END';
    }
}
