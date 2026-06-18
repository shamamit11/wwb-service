<?php

use App\Models\ContentBrief;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_briefs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_topic_id')
                ->unique()
                ->constrained('content_topics')
                ->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('slug', 190)->unique();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('primary_keyword', 255)->nullable();
            $table->json('secondary_keywords')->nullable();
            $table->string('search_intent', 120)->nullable();
            $table->json('outline')->nullable();
            $table->json('headings')->nullable();
            $table->json('faq_suggestions')->nullable();
            $table->json('internal_link_suggestions')->nullable();
            $table->json('image_suggestions')->nullable();
            $table->enum('status', ContentBrief::STATUSES);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['status', 'approved_at']);
            $table->index('primary_keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_briefs');
    }
};
