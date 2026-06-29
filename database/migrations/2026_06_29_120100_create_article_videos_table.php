<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_videos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->foreignId('article_video_recommendation_id')
                ->nullable()
                ->constrained('article_video_recommendations')
                ->nullOnDelete();
            $table->enum('status', ['draft', 'approved', 'rendering', 'rendered', 'failed', 'rejected'])
                ->default('draft');
            $table->enum('render_mode', ['text_only'])->default('text_only');
            $table->string('hook', 500)->nullable();
            $table->text('voiceover_text')->nullable();
            $table->json('script_json')->nullable();
            $table->json('captions_json')->nullable();
            $table->string('voiceover_path', 512)->nullable();
            $table->string('captions_path', 512)->nullable();
            $table->string('thumbnail_path', 512)->nullable();
            $table->string('video_path', 512)->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rendered_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('post_id');
            $table->index('article_video_recommendation_id');
            $table->index(['post_id', 'status']);
            $table->index(['status', 'approved_at']);
            $table->index(['status', 'rendered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_videos');
    }
};
