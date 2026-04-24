<?php
// database/migrations/2026_04_17_000005_rename_articles_to_posts_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * マイグレーションを実行する。
     */
    public function up(): void
    {
        if (Schema::hasTable('articles') && ! Schema::hasTable('posts')) {
            Schema::rename('articles', 'posts');
        }

        if (Schema::hasTable('comments') && Schema::hasColumn('comments', 'article_id') && ! Schema::hasColumn('comments', 'post_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropForeign(['article_id']);
            });

            DB::statement('ALTER TABLE comments CHANGE article_id post_id BIGINT UNSIGNED NOT NULL');

            Schema::table('comments', function (Blueprint $table) {
                $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('article_likes') && ! Schema::hasTable('post_likes')) {
            Schema::rename('article_likes', 'post_likes');
        }

        if (Schema::hasTable('post_likes') && Schema::hasColumn('post_likes', 'article_id') && ! Schema::hasColumn('post_likes', 'post_id')) {
            Schema::table('post_likes', function (Blueprint $table) {
                $table->dropForeign('article_likes_article_id_foreign');
                $table->dropUnique('article_likes_article_id_user_id_unique');
            });

            DB::statement('ALTER TABLE post_likes CHANGE article_id post_id BIGINT UNSIGNED NOT NULL');

            Schema::table('post_likes', function (Blueprint $table) {
                $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
                $table->unique(['post_id', 'user_id']);
            });
        }
    }

    /**
     * マイグレーションをロールバックする。
     */
    public function down(): void
    {
        if (Schema::hasTable('post_likes') && Schema::hasColumn('post_likes', 'post_id') && ! Schema::hasColumn('post_likes', 'article_id')) {
            Schema::table('post_likes', function (Blueprint $table) {
                $table->dropForeign(['post_id']);
                $table->dropUnique(['post_id', 'user_id']);
            });

            DB::statement('ALTER TABLE post_likes CHANGE post_id article_id BIGINT UNSIGNED NOT NULL');

            Schema::table('post_likes', function (Blueprint $table) {
                $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
                $table->unique(['article_id', 'user_id']);
            });
        }

        if (Schema::hasTable('comments') && Schema::hasColumn('comments', 'post_id') && ! Schema::hasColumn('comments', 'article_id')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropForeign(['post_id']);
            });

            DB::statement('ALTER TABLE comments CHANGE post_id article_id BIGINT UNSIGNED NOT NULL');

            Schema::table('comments', function (Blueprint $table) {
                $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('posts') && ! Schema::hasTable('articles')) {
            Schema::rename('posts', 'articles');
        }
    }
};