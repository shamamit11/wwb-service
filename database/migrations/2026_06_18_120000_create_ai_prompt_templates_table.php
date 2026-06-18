<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_prompt_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 160);
            $table->string('key', 180)->unique();
            $table->string('type', 60);
            $table->text('description')->nullable();
            $table->string('status', 32);
            $table->unsignedBigInteger('active_version_id')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('ai_prompt_template_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prompt_template_id')
                ->constrained('ai_prompt_templates')
                ->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->longText('system_prompt');
            $table->longText('user_prompt');
            $table->json('output_schema')->nullable();
            $table->json('variables')->nullable();
            $table->string('status', 32);
            $table->timestamps();

            $table->unique(['prompt_template_id', 'version']);
            $table->index(['prompt_template_id', 'status']);
        });

        Schema::table('ai_prompt_templates', function (Blueprint $table): void {
            $table->foreign('active_version_id')
                ->references('id')
                ->on('ai_prompt_template_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ai_prompt_templates', function (Blueprint $table): void {
            $table->dropForeign(['active_version_id']);
        });

        Schema::dropIfExists('ai_prompt_template_versions');
        Schema::dropIfExists('ai_prompt_templates');
    }
};
