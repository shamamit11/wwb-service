<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->foreignId('author_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();
            $table->foreignId('featured_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 190)->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->longText('full_article_markdown')->nullable();
            $table->longText('full_article_html')->nullable();
            $table->json('faq')->nullable();
            $table->enum('status', Post::STATUSES);
            $table->enum('visibility', Post::VISIBILITIES)->default(Post::VISIBILITY_PUBLIC);
            $table->timestamp('published_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('author_user_id');
            $table->index('category_id');
            $table->index('featured_media_id');
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
