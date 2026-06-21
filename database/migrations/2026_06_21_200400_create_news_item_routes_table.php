<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_item_routes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_item_id')
                ->constrained('news_items')
                ->cascadeOnDelete();
            $table->string('route', 40);
            $table->foreignId('knowledge_base_entry_id')
                ->nullable()
                ->constrained('knowledge_base_entries')
                ->nullOnDelete();
            $table->foreignId('content_topic_id')
                ->nullable()
                ->constrained('content_topics')
                ->nullOnDelete();
            $table->foreignId('post_id')
                ->nullable()
                ->constrained('posts')
                ->nullOnDelete();
            $table->timestamp('routed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['news_item_id', 'created_at']);
            $table->index(['route', 'routed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_item_routes');
    }
};
