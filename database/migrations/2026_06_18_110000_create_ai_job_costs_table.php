<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_job_costs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ai_job_id')
                ->constrained('ai_jobs')
                ->cascadeOnDelete();
            $table->foreignId('ai_generation_step_id')
                ->nullable()
                ->constrained('ai_generation_steps')
                ->nullOnDelete();
            $table->string('provider', 120)->nullable();
            $table->string('model', 160)->nullable();
            $table->unsignedBigInteger('input_tokens')->default(0);
            $table->unsignedBigInteger('output_tokens')->default(0);
            $table->unsignedBigInteger('total_tokens')->default(0);
            $table->decimal('estimated_cost', 12, 8)->nullable();
            $table->decimal('actual_cost', 12, 8)->nullable();
            $table->string('currency', 8)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('ai_job_id');
            $table->index('ai_generation_step_id');
            $table->index(['provider', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_job_costs');
    }
};
