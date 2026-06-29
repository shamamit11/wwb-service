<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_video_recommendations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->enum('priority', ['high', 'medium', 'low']);
            $table->enum('recommended_format', ['explainer', 'checklist', 'opinion', 'news_pulse']);
            $table->text('reason');
            $table->string('suggested_hook', 500);
            $table->text('risk_note')->nullable();
            $table->enum('status', ['pending', 'selected', 'skipped', 'rejected'])->default('pending');
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->index('post_id');
            $table->index(['post_id', 'status']);
            $table->index(['status', 'evaluated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_video_recommendations');
    }
};
