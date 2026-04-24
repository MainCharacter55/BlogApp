<?php
// app/Models/User.php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入を許可する属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * シリアライズ時に非表示にする属性。
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 属性のキャスト定義を返す。
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * 管理者ユーザーかを判定する。
     */
    public function isAdmin(): bool
    {
        return (bool) ($this->getAttribute('is_admin') ?? false);
    }

    /**
     * ユーザーが投稿したコメント一覧。
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * ユーザーが投稿へ付与したリアクション一覧。
     */
    public function postReactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    /**
     * ユーザーがコメントへ付与したリアクション一覧。
     */
    public function commentReactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }
}
