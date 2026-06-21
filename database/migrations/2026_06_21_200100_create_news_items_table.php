<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_items', function (Blueprint $table): void {
            $table->id();
            $table->string('external_id', 255)->nullable();
            $table->string('provider', 60);
            $table->foreignId('source_id')
                ->nullable()
                ->constrained('news_sources')
                ->nullOnDelete();
            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();
            $table->string('publisher_name', 190)->nullable();
            $table->string('title', 500);
            $table->string('normalized_title', 500);
            $table->string('url', 1500);
            $table->string('canonical_url', 1500)->nullable();
            $table->text('description')->nullable();
            $table->string('author', 190)->nullable();
            $table->string('language', 12)->nullable();
            $table->string('country', 12)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('discovered_at')->nullable();
            $table->string('status', 32);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'external_id']);
            $table->index(['provider', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index('normalized_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_items');
    }
};
