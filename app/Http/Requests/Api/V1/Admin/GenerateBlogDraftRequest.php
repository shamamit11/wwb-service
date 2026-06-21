<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\AI\Enums\BlogDraftGenerationMode;
use App\Models\Post;
use App\Modules\Ai\Data\QueueBlogDraftGenerationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateBlogDraftRequest extends FormRequest
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
            'author_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'featured_media_id' => ['sometimes', 'nullable', 'integer', 'exists:media,id'],
            'visibility' => ['sometimes', 'string', Rule::in(Post::VISIBILITIES)],
            'generation_mode' => ['sometimes', 'nullable', 'string', Rule::in(BlogDraftGenerationMode::values())],
        ];
    }

    public function toData(): QueueBlogDraftGenerationData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return new QueueBlogDraftGenerationData(
            authorUserId: isset($validated['author_user_id']) ? (int) $validated['author_user_id'] : null,
            categoryId: (int) $validated['category_id'],
            featuredMediaId: isset($validated['featured_media_id']) ? (int) $validated['featured_media_id'] : null,
            visibility: isset($validated['visibility']) ? (string) $validated['visibility'] : Post::VISIBILITY_PUBLIC,
            generationMode: isset($validated['generation_mode']) ? (string) $validated['generation_mode'] : null,
        );
    }
}
