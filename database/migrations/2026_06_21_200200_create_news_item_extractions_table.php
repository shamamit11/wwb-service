<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_item_extractions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_item_id')
                ->constrained('news_items')
                ->cascadeOnDelete();
            $table->string('extractor', 60);
            $table->longText('content_markdown')->nullable();
            $table->longText('content_text')->nullable();
            $table->text('excerpt')->nullable();
            $table->json('facts_json')->nullable();
            $table->json('entities_json')->nullable();
            $table->json('claims_json')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['news_item_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_item_extractions');
    }
};
