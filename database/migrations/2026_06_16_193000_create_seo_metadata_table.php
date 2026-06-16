<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table): void {
            $table->id();
            $table->string('seoable_type', 120);
            $table->unsignedBigInteger('seoable_id');
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('og_title', 255)->nullable();
            $table->string('og_description', 320)->nullable();
            $table->foreignId('og_image_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
            $table->string('schema_type', 120)->nullable();
            $table->json('schema_payload')->nullable();
            $table->string('focus_keyword', 190)->nullable();
            $table->timestamps();

            $table->unique(['seoable_type', 'seoable_id']);
            $table->index('og_image_media_id');
            $table->index('focus_keyword');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
