<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 120);
            $table->string('status', 32);
            $table->string('entity_type', 120)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('provider', 120)->nullable();
            $table->string('model', 160)->nullable();
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->json('usage_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedSmallInteger('attempts')->default(1);
            $table->foreignId('retry_of_ai_job_id')
                ->nullable()
                ->constrained('ai_jobs')
                ->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_jobs');
    }
};
