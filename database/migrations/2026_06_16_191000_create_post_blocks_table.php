<?php

use App\Enums\ContentBlockType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->char('block_key', 26)->unique();
            $table->enum('block_type', ContentBlockType::values());
            $table->unsignedInteger('sort_order');
            $table->longText('content_markdown')->nullable();
            $table->longText('content_html_cache')->nullable();
            $table->longText('plain_text_cache')->nullable();
            $table->json('settings')->nullable();
            $table->foreignId('source_template_block_id')
                ->nullable()
                ->constrained('template_blocks')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['post_id', 'sort_order']);
            $table->index(['post_id', 'block_type']);
            $table->index('source_template_block_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_blocks');
    }
};
