<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->unsignedBigInteger('generated_by_ai_job_id')->nullable();
            $table->enum('storage_provider', ['r2']);
            $table->string('bucket_name', 120);
            $table->string('object_key', 512);
            $table->string('original_filename', 255);
            $table->string('mime_type', 120);
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('file_size_bytes');
            $table->char('checksum_sha256', 64)->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->text('caption')->nullable();
            $table->enum('source_type', ['uploaded', 'ai_generated', 'stock']);
            $table->string('source_url', 500)->nullable();
            $table->string('attribution_text', 255)->nullable();
            $table->enum('status', ['pending', 'ready', 'failed', 'archived']);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['storage_provider', 'bucket_name', 'object_key']);
            $table->index('uploaded_by_user_id');
            $table->index('generated_by_ai_job_id');
            $table->index(['status', 'created_at']);
            $table->index('checksum_sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
