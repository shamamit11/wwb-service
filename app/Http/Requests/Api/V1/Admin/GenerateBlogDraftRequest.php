<?php

namespace App\Http\Requests\Api\V1\Admin;

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
            'template_id' => ['sometimes', 'nullable', 'integer', 'exists:templates,id'],
            'featured_media_id' => ['sometimes', 'nullable', 'integer', 'exists:media,id'],
            'visibility' => ['sometimes', 'string', Rule::in(Post::VISIBILITIES)],
            'prompt_template_key' => ['sometimes', 'nullable', 'string', 'max:190'],
        ];
    }

    public function toData(): QueueBlogDraftGenerationData
    {
        /** @var array<string, mixed> $validated */
        $validated = $this->validated();

        return new QueueBlogDraftGenerationData(
            authorUserId: isset($validated['author_user_id']) ? (int) $validated['author_user_id'] : null,
            categoryId: (int) $validated['category_id'],
            templateId: isset($validated['template_id']) ? (int) $validated['template_id'] : null,
            featuredMediaId: isset($validated['featured_media_id']) ? (int) $validated['featured_media_id'] : null,
            visibility: isset($validated['visibility']) ? (string) $validated['visibility'] : Post::VISIBILITY_PUBLIC,
            promptTemplateKey: $validated['prompt_template_key'] ?? null,
        );
    }
}
