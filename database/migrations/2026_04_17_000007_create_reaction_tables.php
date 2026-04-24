<?php
// database/migrations/2026_04_17_000007_create_reaction_tables.php

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
        if (! Schema::hasTable('post_reactions')) {
            Schema::create('post_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('reaction', 32);
                $table->timestamps();

                $table->unique(['post_id', 'user_id']);
                $table->index('reaction');
            });
        }

        if (! Schema::hasTable('comment_reactions')) {
            Schema::create('comment_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('reaction', 32);
                $table->timestamps();

                $table->unique(['comment_id', 'user_id']);
                $table->index('reaction');
            });
        }

        if (Schema::hasTable('post_likes')) {
            DB::statement(<<<'SQL'
                INSERT INTO post_reactions (post_id, user_id, reaction, created_at, updated_at)
                SELECT pl.post_id, pl.user_id, 'like', pl.created_at, pl.updated_at
                FROM post_likes pl
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM post_reactions pr
                    WHERE pr.post_id = pl.post_id
                      AND pr.user_id = pl.user_id
                )
            SQL);
        }

        if (Schema::hasTable('comment_likes')) {
            DB::statement(<<<'SQL'
                INSERT INTO comment_reactions (comment_id, user_id, reaction, created_at, updated_at)
                SELECT cl.comment_id, cl.user_id, 'like', cl.created_at, cl.updated_at
                FROM comment_likes cl
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM comment_reactions cr
                    WHERE cr.comment_id = cl.comment_id
                      AND cr.user_id = cl.user_id
                )
            SQL);
        }
    }

    /**
     * マイグレーションをロールバックする。
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
        Schema::dropIfExists('post_reactions');
    }
};