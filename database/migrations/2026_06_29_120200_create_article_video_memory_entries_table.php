<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_video_memory_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->foreignId('article_video_id')
                ->nullable()
                ->constrained('article_videos')
                ->nullOnDelete();
            $table->string('hook', 500);
            $table->string('core_angle', 255)->nullable();
            $table->text('voiceover_text');
            $table->timestamps();

            $table->index('post_id');
            $table->index('article_video_id');
            $table->index(['post_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_video_memory_entries');
    }
};
