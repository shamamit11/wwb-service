<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_topics', function (Blueprint $table): void {
            $table->json('discovery_metadata')->nullable()->after('score_breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('content_topics', function (Blueprint $table): void {
            $table->dropColumn('discovery_metadata');
        });
    }
};
