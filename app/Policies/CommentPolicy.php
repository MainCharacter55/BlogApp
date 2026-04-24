<?php
// app/Policies/CommentPolicy.php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * コメント更新の可否を判定する。
     */
    public function update(User $user, Comment $comment): bool
    {
        return $this->isOwnerOrAdmin($user, $comment);
    }

    /**
     * コメント削除の可否を判定する。
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $this->isOwnerOrAdmin($user, $comment);
    }

    /**
     * オーナーまたは管理者かを判定する。
     */
    private function isOwnerOrAdmin(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id || $user->isAdmin();
    }
}
