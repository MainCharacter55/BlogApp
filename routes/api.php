<?php
// routes/api.php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\CommentController;
use Illuminate\Support\Facades\Route;

Route::get('/posts', [PostController::class, 'index']);

Route::post('/posts', [PostController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:api-posts-write']);

Route::scopeBindings()->group(function (): void {
    Route::get('/posts/{post}/comments', [CommentController::class, 'index'])
        ->whereNumber('post');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
            ->middleware('throttle:api-comments-write')
            ->whereNumber('post');

        Route::patch('/posts/{post}/comments/{comment}', [CommentController::class, 'update'])
            ->middleware('throttle:api-comments-mutate')
            ->whereNumber('post')
            ->whereNumber('comment');

        Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])
            ->middleware('throttle:api-comments-mutate')
            ->whereNumber('post')
            ->whereNumber('comment');
    });
});

Route::prefix('auth')->group(function (): void {
    Route::post('/register/request', [AuthController::class, 'requestRegistrationToken'])
        ->middleware('throttle:auth-registration-request');
    Route::post('/register/verify', [AuthController::class, 'verifyRegistration'])
        ->middleware('throttle:auth-registration-verify');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:auth-login');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
