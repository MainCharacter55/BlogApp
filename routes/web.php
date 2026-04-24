<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CommentController;
use App\Http\Controllers\Web\ReactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'index'])->name('home');
Route::get('/recent', [BlogController::class, 'recent'])->name('posts.recent');
Route::get('/popular', [BlogController::class, 'popular'])->name('posts.popular');
Route::get('/posts/{post}', [BlogController::class, 'show'])->name('posts.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');

    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::get('/register/verify', [AuthController::class, 'createRegisterVerify'])->name('register.verify.form');
    Route::post('/register/request', [AuthController::class, 'requestRegistrationToken'])
        ->name('register.request')
        ->middleware('throttle:auth-registration-request');
    Route::post('/register/verify', [AuthController::class, 'verifyRegistration'])
        ->name('register.verify')
        ->middleware('throttle:auth-registration-verify');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
        ->name('posts.comments.store')
        ->middleware('throttle:post-comments');
    Route::patch('/posts/{post}/comments/{comment}', [CommentController::class, 'update'])
        ->name('posts.comments.update');
    Route::delete('/posts/{post}/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('posts.comments.destroy');
    Route::post('/posts/{post}/reaction', [ReactionController::class, 'togglePostReaction'])
        ->name('posts.reaction.toggle')
        ->middleware('throttle:post-reactions');
    Route::post('/posts/{post}/comments/{comment}/reaction', [ReactionController::class, 'toggleCommentReaction'])
        ->name('posts.comments.reaction.toggle')
        ->middleware('throttle:comment-reactions');
});
