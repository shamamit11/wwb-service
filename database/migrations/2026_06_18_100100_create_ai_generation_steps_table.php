<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generation_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ai_job_id')
                ->constrained('ai_jobs')
                ->cascadeOnDelete();
            $table->string('agent_name', 120);
            $table->string('status', 32);
            $table->json('input_payload')->nullable();
            $table->json('output_payload')->nullable();
            $table->json('usage_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['ai_job_id', 'status']);
            $table->index(['agent_name', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_steps');
    }
};
