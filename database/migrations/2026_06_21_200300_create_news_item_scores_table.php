<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_item_scores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_item_id')
                ->constrained('news_items')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('relevance_score')->default(0);
            $table->unsignedTinyInteger('freshness_score')->default(0);
            $table->unsignedTinyInteger('credibility_score')->default(0);
            $table->unsignedTinyInteger('pillar_fit_score')->default(0);
            $table->unsignedTinyInteger('evergreen_potential_score')->default(0);
            $table->unsignedTinyInteger('novelty_score')->default(0);
            $table->unsignedTinyInteger('business_value_score')->default(0);
            $table->unsignedTinyInteger('total_score')->default(0);
            $table->string('decision', 40);
            $table->text('reasoning')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            $table->index(['news_item_id', 'created_at']);
            $table->index(['decision', 'total_score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_item_scores');
    }
};
