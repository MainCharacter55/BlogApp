<?php
// app/Models/Post.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * 一括代入を許可する属性。
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'content',
        'views_count',
    ];

    /**
     * 投稿に紐づくコメント一覧。
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 投稿に紐づくリアクション一覧。
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }
}