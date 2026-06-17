<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepages', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('singleton_key', 32)->unique();
            $table->json('hero');
            $table->json('featured_editorial');
            $table->json('guide_section');
            $table->json('topic_section');
            $table->json('promo_section');
            $table->json('newsletter_section');
            $table->json('seo');
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepages');
    }
};
