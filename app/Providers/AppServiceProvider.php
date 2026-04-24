<?php

namespace App\Providers;

use App\Models\Comment;
use App\Policies\CommentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Comment::class, CommentPolicy::class);

        RateLimiter::for('auth-login', function (Request $request): Limit {
            $email = (string) $request->input('email');

            return Limit::perMinute(5)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('auth-registration-request', function (Request $request): Limit {
            $email = (string) $request->input('email');

            return Limit::perMinute(3)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('auth-registration-verify', function (Request $request): Limit {
            $registrationData = $request->hasSession()
                ? $request->session()->get('web_registration', [])
                : [];
            $email = (string) ($request->input('email') ?: ($registrationData['email'] ?? ''));

            return Limit::perMinute(5)->by($request->ip().'|'.$email);
        });

        RateLimiter::for('post-comments', function (Request $request): Limit {
            $userKey = $request->user()?->id ? 'user:'.$request->user()->id : 'ip:'.$request->ip();

            return Limit::perMinute(8)->by($userKey);
        });

        RateLimiter::for('post-reactions', function (Request $request): Limit {
            $userKey = $request->user()?->id ? 'user:'.$request->user()->id : 'ip:'.$request->ip();

            return Limit::perMinute(20)->by($userKey);
        });

        RateLimiter::for('comment-reactions', function (Request $request): Limit {
            $userKey = $request->user()?->id ? 'user:'.$request->user()->id : 'ip:'.$request->ip();

            return Limit::perMinute(20)->by($userKey);
        });

        RateLimiter::for('api-comments-write', function (Request $request): Limit {
            $userKey = $request->user()?->id ? 'user:'.$request->user()->id : 'ip:'.$request->ip();

            return Limit::perMinute(8)->by($userKey);
        });

        RateLimiter::for('api-posts-write', function (Request $request): Limit {
            $userKey = $request->user()?->id ? 'user:'.$request->user()->id : 'ip:'.$request->ip();

            return Limit::perMinute(5)->by($userKey);
        });

        RateLimiter::for('api-comments-mutate', function (Request $request): Limit {
            $userKey = $request->user()?->id ? 'user:'.$request->user()->id : 'ip:'.$request->ip();

            return Limit::perMinute(20)->by($userKey);
        });
    }
}
