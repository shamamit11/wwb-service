<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('template_id')
                ->constrained('templates')
                ->cascadeOnDelete();
            $table->char('block_key', 26)->unique();
            $table->string('block_type', 50);
            $table->unsignedInteger('sort_order');
            $table->string('label', 160)->nullable();
            $table->longText('default_markdown')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['template_id', 'sort_order']);
            $table->index(['template_id', 'block_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_blocks');
    }
};
