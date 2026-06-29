<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Post;
use App\Modules\Posts\Data\PostFiltersData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(Post::STATUSES)],
            'visibility' => ['nullable', 'string', Rule::in(Post::VISIBILITIES)],
            'category_slug' => ['nullable', 'string', 'max:160'],
            'is_featured' => ['nullable', 'boolean'],
            'author_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'is_ai_generated' => ['nullable', 'boolean'],
            'source_content_topic_id' => ['nullable', 'integer', 'exists:content_topics,id'],
            'generated_by_ai_job_id' => ['nullable', 'integer', 'exists:ai_jobs,id'],
            'needs_originality_review' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', Rule::in(['title', '-title', 'created_at', '-created_at', 'updated_at', '-updated_at', 'published_at', '-published_at'])],
        ];
    }

    public function toData(): PostFiltersData
    {
        /** @var array{search?:string|null,status?:string|null,visibility?:string|null,category_slug?:string|null,is_featured?:bool|null,author_user_id?:int|null,is_ai_generated?:bool|null,source_content_topic_id?:int|null,generated_by_ai_job_id?:int|null,needs_originality_review?:bool|null,sort?:string|null} $validated */
        $validated = $this->validated();

        return new PostFiltersData(
            search: $validated['search'] ?? null,
            status: $validated['status'] ?? null,
            visibility: $validated['visibility'] ?? null,
            categorySlug: $validated['category_slug'] ?? null,
            isFeatured: $validated['is_featured'] ?? null,
            authorUserId: $validated['author_user_id'] ?? null,
            isAiGenerated: $validated['is_ai_generated'] ?? null,
            sourceContentTopicId: $validated['source_content_topic_id'] ?? null,
            generatedByAiJobId: $validated['generated_by_ai_job_id'] ?? null,
            needsOriginalityReview: $validated['needs_originality_review'] ?? null,
            sort: $validated['sort'] ?? '-updated_at',
        );
    }
}
