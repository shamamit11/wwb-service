<?php

namespace App\Modules\Posts\Repositories;

use App\Models\Post;
use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Data\UpdatePostData;
use Illuminate\Database\Eloquent\Collection;

class EloquentPostRepository implements PostRepository
{
    public function create(CreatePostData $data): Post
    {
        $post = Post::query()->create([
            'author_user_id' => $data->authorUserId,
            'category_id' => $data->categoryId,
            'template_id' => $data->templateId,
            'featured_media_id' => $data->featuredMediaId,
            'title' => $data->title,
            'slug' => $data->slug,
            'excerpt' => $data->excerpt,
            'status' => $data->status,
            'visibility' => $data->visibility,
            'published_at' => $data->publishedAt,
            'scheduled_for' => $data->scheduledFor,
            'content_version' => $data->contentVersion,
            'reading_time_minutes' => $data->readingTimeMinutes,
            'word_count' => $data->wordCount,
            'is_featured' => $data->isFeatured,
            'meta' => $data->meta,
        ]);

        $post->tags()->sync(array_values(array_unique($data->tagIds)));

        return $this->refreshWithRelations($post);
    }

    public function update(Post $post, UpdatePostData $data): Post
    {
        $post->update([
            'author_user_id' => $data->authorUserId,
            'category_id' => $data->categoryId,
            'template_id' => $data->templateId,
            'featured_media_id' => $data->featuredMediaId,
            'title' => $data->title,
            'slug' => $data->slug,
            'excerpt' => $data->excerpt,
            'status' => $data->status,
            'visibility' => $data->visibility,
            'published_at' => $data->publishedAt,
            'scheduled_for' => $data->scheduledFor,
            'content_version' => $data->contentVersion,
            'reading_time_minutes' => $data->readingTimeMinutes,
            'word_count' => $data->wordCount,
            'is_featured' => $data->isFeatured,
            'meta' => $data->meta,
        ]);

        $post->tags()->sync(array_values(array_unique($data->tagIds)));

        return $this->refreshWithRelations($post);
    }

    public function delete(Post $post): void
    {
        $post->tags()->detach();
        $post->delete();
    }

    public function findById(int $id): ?Post
    {
        return Post::query()
            ->with($this->relations())
            ->find($id);
    }

    public function findBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with($this->relations())
            ->where('slug', $slug)
            ->first();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with($this->relations())
            ->where('slug', $slug)
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return Post::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    /**
     * @return Collection<int, Post>
     */
    public function getAdminOrdered(): Collection
    {
        return Post::query()
            ->with($this->relations())
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPublishedOrdered(?string $categorySlug = null, bool $featuredOnly = false): Collection
    {
        return Post::query()
            ->with($this->relations())
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->whereNotNull('published_at')
            ->when($categorySlug, function ($query, string $categorySlug): void {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug));
            })
            ->when($featuredOnly, fn ($query) => $query->where('is_featured', true))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();
    }

    private function refreshWithRelations(Post $post): Post
    {
        return $post->refresh()->load($this->relations());
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks.sourceTemplateBlock'];
    }
}
