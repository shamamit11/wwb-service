<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_entries', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('updated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 190)->unique();
            $table->enum('entry_type', ['note', 'research', 'experience', 'architecture', 'code', 'reference', 'idea']);
            $table->enum('status', ['draft', 'active', 'archived']);
            $table->text('summary')->nullable();
            $table->longText('content_markdown');
            $table->string('source_url', 500)->nullable();
            $table->foreignId('featured_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['entry_type', 'status']);
            $table->index('featured_media_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_entries');
    }
};
