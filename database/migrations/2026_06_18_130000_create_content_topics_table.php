<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_topics', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 190)->unique();
            $table->string('cluster', 60);
            $table->string('primary_keyword', 255)->nullable();
            $table->json('secondary_keywords')->nullable();
            $table->string('search_intent', 120)->nullable();
            $table->decimal('priority_score', 5, 2)->nullable();
            $table->text('difficulty_note')->nullable();
            $table->string('source', 120);
            $table->string('status', 32);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['cluster', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('primary_keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_topics');
    }
};
