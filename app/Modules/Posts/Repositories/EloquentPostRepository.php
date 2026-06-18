<?php

namespace App\Modules\Posts\Repositories;

use App\Models\Post;
use App\Modules\Posts\Data\CreatePostData;
use App\Modules\Posts\Data\PostFiltersData;
use App\Modules\Posts\Data\PostStateTransitionData;
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

    public function transition(Post $post, PostStateTransitionData $data): Post
    {
        $post->update([
            'status' => $data->status,
            'published_at' => $data->publishedAt,
            'scheduled_for' => $data->scheduledFor,
        ]);

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

    public function findBySourceContentBriefId(int $contentBriefId): ?Post
    {
        return Post::query()
            ->with($this->relations())
            ->where('meta->source_content_brief_id', $contentBriefId)
            ->first();
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
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->first();
    }

    public function existsBySlug(string $slug, ?int $ignoreId = null): bool
    {
        return Post::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists();
    }

    public function existsPotentialDuplicate(string $title, ?string $slug = null): bool
    {
        $normalizedTitle = mb_strtolower(trim($title));
        $normalizedSlug = is_string($slug) && $slug !== '' ? trim($slug) : null;

        return Post::query()
            ->where(function ($query) use ($normalizedTitle, $normalizedSlug): void {
                $query->whereRaw('LOWER(title) = ?', [$normalizedTitle]);

                if ($normalizedSlug !== null) {
                    $query->orWhere('slug', $normalizedSlug);
                }
            })
            ->exists();
    }

    /**
     * @return Collection<int, Post>
     */
    public function getAdminOrdered(): Collection
    {
        return $this->searchAdmin(new PostFiltersData);
    }

    /**
     * @return Collection<int, Post>
     */
    public function searchAdmin(PostFiltersData $filters): Collection
    {
        [$sortColumn, $descending] = $this->normalizeSort($filters->sort);

        return Post::query()
            ->with($this->relations())
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%");
                });
            })
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->visibility, fn ($query, string $visibility) => $query->where('visibility', $visibility))
            ->when($filters->categorySlug, function ($query, string $categorySlug): void {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug));
            })
            ->when($filters->isFeatured !== null, fn ($query) => $query->where('is_featured', $filters->isFeatured))
            ->when($filters->authorUserId, fn ($query, int $authorUserId) => $query->where('author_user_id', $authorUserId))
            ->when($filters->isAiGenerated !== null, function ($query) use ($filters): void {
                if ($filters->isAiGenerated) {
                    $query->where(function ($innerQuery): void {
                        $innerQuery
                            ->whereNotNull('meta->source_content_brief_id')
                            ->orWhereNotNull('meta->source_content_topic_id')
                            ->orWhereNotNull('meta->ai_job_id')
                            ->orWhereNotNull('meta->generated_by');
                    });

                    return;
                }

                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->whereNull('meta->source_content_brief_id')
                        ->whereNull('meta->source_content_topic_id')
                        ->whereNull('meta->ai_job_id')
                        ->whereNull('meta->generated_by');
                });
            })
            ->when($filters->sourceContentBriefId, fn ($query, int $sourceContentBriefId) => $query->where('meta->source_content_brief_id', $sourceContentBriefId))
            ->when($filters->sourceContentTopicId, fn ($query, int $sourceContentTopicId) => $query->where('meta->source_content_topic_id', $sourceContentTopicId))
            ->when($filters->generatedByAiJobId, fn ($query, int $generatedByAiJobId) => $query->where('meta->ai_job_id', $generatedByAiJobId))
            ->orderBy($sortColumn, $descending ? 'desc' : 'asc')
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
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($categorySlug, function ($query, string $categorySlug): void {
                $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $categorySlug)->where('is_active', true));
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
     * @return array{0: string, 1: bool}
     */
    private function normalizeSort(string $sort): array
    {
        $descending = str_starts_with($sort, '-');
        $field = ltrim($sort, '-');
        $allowed = ['created_at', 'updated_at', 'published_at', 'title'];

        if (! in_array($field, $allowed, true)) {
            return ['updated_at', true];
        }

        return [$field, $descending];
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return ['author', 'category', 'template', 'featuredMedia', 'tags', 'blocks.sourceTemplateBlock', 'seo'];
    }
}
