@extends('layouts.app')

@section('content')
    @php
        $sortedPostReactions = collect($postReactionsSummary)
            ->map(fn ($total, $reaction) => [
                'key' => $reaction,
                'total' => (int) $total,
                'meta' => $reactionOptions[$reaction] ?? ['emoji' => '🙂', 'label' => ucfirst((string) $reaction)],
            ])
            ->sortByDesc('total')
            ->values();

        $topPostReactions = $sortedPostReactions->take(3);
        if ($topPostReactions->isEmpty()) {
            $topPostReactions = collect([
                [
                    'key' => 'like',
                    'total' => 0,
                    'meta' => $reactionOptions['like'] ?? ['emoji' => '👍', 'label' => 'Like'],
                ],
                [
                    'key' => 'dislike',
                    'total' => 0,
                    'meta' => $reactionOptions['dislike'] ?? ['emoji' => '👎', 'label' => 'Dislike'],
                ],
            ]);
        }

        $postReactionMenuItems = collect($reactionOptions)
            ->map(function ($meta, $reactionKey) use ($postReactionsSummary) {
                return [
                    'key' => $reactionKey,
                    'meta' => $meta,
                    'total' => (int) ($postReactionsSummary[$reactionKey] ?? 0),
                ];
            })
            ->sortByDesc('total')
            ->values();
        $topPostReactionKeys = $topPostReactions->pluck('key')->all();
        $otherPostReactionMenuItems = $postReactionMenuItems
            ->filter(fn ($item) => ! in_array($item['key'], $topPostReactionKeys, true))
            ->values();
        $otherPostReactionsCount = (int) $otherPostReactionMenuItems->sum('total');
    @endphp

    <article class="mx-auto w-full max-w-4xl space-y-6">
        <section class="rounded-[2rem] border border-white/10 bg-slate-900/80 p-8 shadow-2xl shadow-cyan-950/20">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm uppercase tracking-[0.28em] text-cyan-300">Post {{ $post->id }}</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white">{{ $post->title }}</h1>
                </div>
                <div class="rounded-full border border-white/10 px-3 py-1 text-xs text-slate-300">{{ $post->views_count }} views</div>
            </div>

            <p class="mt-4 text-sm text-slate-500">{{ $post->created_at?->format('Y/m/d H:i') }}</p>

            <div class="prose prose-invert mt-6 max-w-none prose-p:text-slate-300 prose-headings:text-white">
                <p>{{ $post->content }}</p>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-white/10 pt-4 text-sm text-slate-300">
                @auth
                    @foreach ($topPostReactions as $item)
                        <form method="POST" action="{{ route('posts.reaction.toggle', $post) }}">
                            @csrf
                            <input type="hidden" name="reaction" value="{{ $item['key'] }}">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-full border px-3 py-1 transition {{ $currentUserPostReaction === $item['key'] ? 'border-cyan-400/40 bg-cyan-400/20 text-cyan-100' : 'border-white/10 bg-white/5 text-slate-200 hover:bg-white/10' }}">
                                <span>{{ $item['meta']['emoji'] }}</span>
                                <span class="text-xs text-slate-300">{{ $item['total'] }}</span>
                            </button>
                        </form>
                    @endforeach

                    <div class="relative" data-reaction-menu-wrapper>
                        <button
                            type="button"
                            class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10"
                            data-reaction-menu-button
                            aria-expanded="false"
                            aria-controls="post-reaction-menu"
                        >
                            Others{{ $otherPostReactionsCount > 0 ? ' +' . $otherPostReactionsCount : '' }} ▾
                        </button>

                        <div id="post-reaction-menu" class="absolute right-0 z-30 mt-2 hidden w-72 overflow-hidden rounded-2xl border border-white/10 bg-slate-950 shadow-2xl shadow-black/40" data-reaction-menu>
                            <div class="border-b border-white/10 px-4 py-3 text-xs uppercase tracking-[0.22em] text-slate-400">Other Reactions</div>
                            <div class="max-h-80 overflow-y-auto p-2">
                                @forelse ($otherPostReactionMenuItems as $item)
                                    <form method="POST" action="{{ route('posts.reaction.toggle', $post) }}">
                                        @csrf
                                        <input type="hidden" name="reaction" value="{{ $item['key'] }}">
                                        <button type="submit" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm transition {{ $currentUserPostReaction === $item['key'] ? 'bg-cyan-400/15 text-cyan-100' : 'text-slate-200 hover:bg-white/10' }}">
                                            <span>{{ $item['meta']['emoji'] }} {{ $item['meta']['label'] }}</span>
                                            <span class="text-xs text-slate-400">{{ $item['total'] }}</span>
                                        </button>
                                    </form>
                                @empty
                                    <div class="px-3 py-2 text-xs text-slate-500">No other reactions.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @else
                    @foreach ($topPostReactions as $item)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-200">
                            <span>{{ $item['meta']['emoji'] }}</span>
                            <span class="text-xs text-slate-300">{{ $item['total'] }}</span>
                        </span>
                    @endforeach
                    <a href="{{ route('login') }}" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-slate-200 transition hover:bg-white/10">Others ▾</a>
                @endauth

                <a href="#comment-form" class="rounded-full border border-white/10 bg-white/5 px-3 py-1 transition hover:bg-white/10">{{ $post->comments_count }} comments</a>
            </div>
        </section>

        <section id="comment-form" class="scroll-mt-32 rounded-[1.75rem] border border-white/10 bg-white/5 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-semibold text-white">コメント投稿</h2>
                    <p class="mt-1 text-sm text-slate-400">新しいコメントは上部、返信は各コメントの Reply から送れます。</p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm">
                    <a href="{{ route('posts.show', ['post' => $post->id, 'comment_sort' => 'new']) }}#comments" class="rounded-full border px-3 py-1.5 transition {{ $commentSort === 'new' ? 'border-cyan-400/40 bg-cyan-400/20 text-cyan-100' : 'border-white/10 text-slate-200 hover:bg-white/10' }}">
                        New
                    </a>
                    <a href="{{ route('posts.show', ['post' => $post->id, 'comment_sort' => 'popular']) }}#comments" class="rounded-full border px-3 py-1.5 transition {{ $commentSort === 'popular' ? 'border-cyan-400/40 bg-cyan-400/20 text-cyan-100' : 'border-white/10 text-slate-200 hover:bg-white/10' }}">
                        Popular
                    </a>
                </div>
            </div>

            @auth
                <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                    <input type="hidden" name="comment_sort" value="{{ $commentSort }}">
                    <div>
                        <label for="content" class="mb-2 block text-sm text-slate-300">コメント内容</label>
                        <textarea id="content" name="content" rows="5" class="w-full rounded-2xl border border-white/10 bg-slate-950/80 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none" placeholder="10文字以上100文字以内で入力してください">{{ old('content') }}</textarea>
                        @error('content')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>
                    <input type="hidden" name="parent_id" value="">
                    <button class="inline-flex items-center rounded-full bg-cyan-400 px-5 py-3 font-medium text-slate-950 transition hover:bg-cyan-300">投稿する</button>
                </form>
            @else
                <p class="mt-4 text-sm leading-6 text-slate-300">
                    コメント投稿にはログインが必要です。<a href="{{ route('login') }}" class="text-cyan-300 underline decoration-cyan-300/40 underline-offset-4">ログイン</a> または <a href="{{ route('register') }}" class="text-cyan-300 underline decoration-cyan-300/40 underline-offset-4">会員登録</a> を行ってください。
                </p>
            @endauth
        </section>

        <section id="comments" class="rounded-[1.75rem] border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-semibold text-white">コメント</h2>
            <div class="mt-4 space-y-4">
                @forelse ($commentsByParentId->get(0, collect()) as $comment)
                    @include('posts.partials.comment', [
                        'comment' => $comment,
                        'post' => $post,
                        'commentsByParentId' => $commentsByParentId,
                        'commentReactionSummaries' => $commentReactionSummaries,
                        'currentUserCommentReactions' => $currentUserCommentReactions,
                        'reactionOptions' => $reactionOptions,
                        'commentSort' => $commentSort,
                        'depth' => 0,
                    ])
                @empty
                    <div class="rounded-2xl border border-dashed border-white/15 bg-slate-950/50 p-4 text-sm text-slate-400">
                        まだコメントがありません。
                    </div>
                @endforelse
            </div>
        </section>
    </article>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const menuWrappers = Array.from(document.querySelectorAll('[data-comment-menu-wrapper]'));
            const reactionMenuWrappers = Array.from(document.querySelectorAll('[data-reaction-menu-wrapper]'));
            const toggleReplyButtons = Array.from(document.querySelectorAll('[data-toggle-replies]'));
            const allRepliesContainers = Array.from(document.querySelectorAll('[data-replies-container]'));
            const allReplyFormContainers = Array.from(document.querySelectorAll('[data-reply-form-container]'));
            const reactionForms = Array.from(document.querySelectorAll('form[action*="/reaction"]'));

            const closeAllMenus = () => {
                menuWrappers.forEach((wrapper) => {
                    const button = wrapper.querySelector('[data-comment-menu-button]');
                    const menu = wrapper.querySelector('[data-comment-menu]');

                    if (menu) {
                        menu.classList.add('hidden');
                    }

                    if (button) {
                        button.setAttribute('aria-expanded', 'false');
                    }
                });

                reactionMenuWrappers.forEach((wrapper) => {
                    const button = wrapper.querySelector('[data-reaction-menu-button]');
                    const menu = wrapper.querySelector('[data-reaction-menu]');

                    if (menu) {
                        menu.classList.add('hidden');
                    }

                    if (button) {
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            };

            toggleReplyButtons.forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();

                    const commentId = button.dataset.commentId;
                    const repliesContainer = document.querySelector(`[data-replies-container="${commentId}"]`);
                    const replyFormContainer = document.querySelector(`[data-reply-form-container="${commentId}"]`);
                    const replyTextarea = document.querySelector(`[data-reply-textarea="${commentId}"]`);

                    const isOpen =
                        (repliesContainer && !repliesContainer.classList.contains('hidden')) ||
                        (replyFormContainer && !replyFormContainer.classList.contains('hidden'));

                    const shouldOpen = !isOpen;

                    allRepliesContainers.forEach((container) => {
                        container.classList.add('hidden');
                    });

                    allReplyFormContainers.forEach((container) => {
                        container.classList.add('hidden');
                    });

                    if (repliesContainer) {
                        repliesContainer.classList.toggle('hidden', !shouldOpen);
                    }

                    if (replyFormContainer) {
                        replyFormContainer.classList.toggle('hidden', !shouldOpen);
                    }

                    if (shouldOpen && replyTextarea) {
                        replyTextarea.focus();
                    }
                });
            });

            reactionForms.forEach((form) => {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    
                    const formData = new FormData(form);
                    const action = form.getAttribute('action');
                    
                    fetch(action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    })
                    .then(async (response) => {
                        if (response.status === 401) {
                            window.location.href = '{{ route('login') }}';
                            return { success: false };
                        }

                        const contentType = response.headers.get('content-type') ?? '';

                        if (contentType.includes('application/json')) {
                            return response.json();
                        }

                        if (response.redirected) {
                            window.location.href = response.url;
                            return { success: false };
                        }

                        return { success: response.ok };
                    })
                    .then(data => {
                        if (data?.success) {
                            window.location.reload();
                        }
                    })
                    .catch(() => {
                        window.location.reload();
                    });
                });
            });

            menuWrappers.forEach((wrapper) => {
                const button = wrapper.querySelector('[data-comment-menu-button]');
                const menu = wrapper.querySelector('[data-comment-menu]');

                if (!button || !menu) {
                    return;
                }

                button.addEventListener('click', (event) => {
                    event.stopPropagation();

                    const isOpen = !menu.classList.contains('hidden');

                    closeAllMenus();

                    if (!isOpen) {
                        menu.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });

                menu.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
            });

            reactionMenuWrappers.forEach((wrapper) => {
                const button = wrapper.querySelector('[data-reaction-menu-button]');
                const menu = wrapper.querySelector('[data-reaction-menu]');

                if (!button || !menu) {
                    return;
                }

                button.addEventListener('click', (event) => {
                    event.stopPropagation();

                    const isOpen = !menu.classList.contains('hidden');

                    closeAllMenus();

                    if (!isOpen) {
                        menu.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });

                menu.addEventListener('click', (event) => {
                    event.stopPropagation();
                });
            });

            document.addEventListener('click', () => {
                closeAllMenus();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAllMenus();
                }
            });
        });
    </script>
@endsection