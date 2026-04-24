<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * 初期データを投入する。
     */
    public function run(): void
    {
        // コメント投稿時に参照できるユーザーを先に作成する。
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 管理者としてコメントを編集・削除できるユーザーを用意する。
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 各投稿にコメントを紐づけて作成する。
        Post::factory(10)->hasComments(5)->create();
    }
}
