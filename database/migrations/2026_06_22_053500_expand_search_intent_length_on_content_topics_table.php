<?php

use App\Models\ContentTopic;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_topics', function (Blueprint $table): void {
            $table->string('search_intent', ContentTopic::SEARCH_INTENT_MAX_LENGTH)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('content_topics', function (Blueprint $table): void {
            $table->string('search_intent', 120)->nullable()->change();
        });
    }
};
