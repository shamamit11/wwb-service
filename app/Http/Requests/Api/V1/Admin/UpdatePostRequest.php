<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\ContentBlockType;
use App\Http\Requests\Api\V1\Admin\Concerns\InteractsWithPostData;
use App\Models\Post;
use App\Modules\Posts\Data\UpdatePostCommandData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    use InteractsWithPostData;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Post $post */
        $post = $this->route('post');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('posts', 'slug')->ignore($post->id)],
            'excerpt' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'template_id' => ['nullable', 'integer', 'exists:templates,id'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['required', 'string', Rule::in(Post::STATUSES)],
            'visibility' => ['required', 'string', Rule::in(Post::VISIBILITIES)],
            'published_at' => ['nullable', 'date'],
            'scheduled_for' => ['nullable', 'date'],
            'content_version' => ['nullable', 'integer', 'min:1'],
            'reading_time_minutes' => ['nullable', 'integer', 'min:0'],
            'word_count' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'meta' => ['nullable', 'array'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'blocks' => ['required', 'array', 'min:1'],
            'blocks.*.block_type' => ['required', 'string', Rule::in(ContentBlockType::values())],
            'blocks.*.sort_order' => ['required', 'integer', 'min:1', 'distinct'],
            'blocks.*.content' => ['required', 'array'],
            'blocks.*.source_template_block_id' => ['nullable', 'integer', 'exists:template_blocks,id'],
        ];
    }

    public function toData(int $userId): UpdatePostCommandData
    {
        /** @var array{title:string,slug?:string|null,excerpt?:string|null,category_id:int,template_id?:int|null,featured_media_id?:int|null,status:string,visibility:string,published_at?:string|null,scheduled_for?:string|null,content_version?:int|null,reading_time_minutes?:int|null,word_count?:int|null,is_featured?:bool,meta?:array<string,mixed>|null,tag_ids?:array<int,int>,blocks:array<int,array{block_type:string,sort_order:int,content:array<string,mixed>,source_template_block_id?:int|null}>} $validated */
        $validated = $this->validated();

        return new UpdatePostCommandData(
            authorUserId: $userId,
            categoryId: $validated['category_id'],
            templateId: $validated['template_id'] ?? null,
            featuredMediaId: $validated['featured_media_id'] ?? null,
            title: $validated['title'],
            slug: $validated['slug'] ?? '',
            excerpt: $validated['excerpt'] ?? null,
            status: $validated['status'],
            visibility: $validated['visibility'],
            publishedAt: $validated['published_at'] ?? null,
            scheduledFor: $validated['scheduled_for'] ?? null,
            contentVersion: $validated['content_version'] ?? 1,
            readingTimeMinutes: $validated['reading_time_minutes'] ?? null,
            wordCount: $validated['word_count'] ?? null,
            isFeatured: $validated['is_featured'] ?? false,
            meta: $validated['meta'] ?? null,
            tagIds: $validated['tag_ids'] ?? [],
            blocks: $this->mapBlocks($validated['blocks']),
        );
    }
}
