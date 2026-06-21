<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\Post;
use App\Modules\Posts\Data\CreatePostCommandData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:190', 'unique:posts,slug'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'full_article_html' => ['required', 'string'],
            'full_article_delta' => ['nullable', 'array'],
            'faq' => ['nullable', 'array'],
            'faq.*.question' => ['required_with:faq', 'string'],
            'faq.*.answer' => ['required_with:faq', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'status' => ['required', 'string', Rule::in(Post::STATUSES)],
            'visibility' => ['required', 'string', Rule::in(Post::VISIBILITIES)],
            'published_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function toData(int $userId): CreatePostCommandData
    {
        /** @var array{title:string,slug?:string|null,short_description?:string|null,description?:string|null,full_article_html:string,full_article_delta?:array<int|string,mixed>|null,faq?:array<int,array{question:string,answer:string}>|null,category_id:int,featured_media_id?:int|null,status:string,visibility:string,published_at?:string|null,meta?:array<string,mixed>|null,tag_ids?:array<int,int>} $validated */
        $validated = $this->validated();

        return new CreatePostCommandData(
            authorUserId: $userId,
            categoryId: $validated['category_id'],
            featuredMediaId: $validated['featured_media_id'] ?? null,
            title: $validated['title'],
            slug: $validated['slug'] ?? '',
            shortDescription: $validated['short_description'] ?? null,
            description: $validated['description'] ?? null,
            fullArticleHtml: $validated['full_article_html'],
            fullArticleDelta: is_array($validated['full_article_delta'] ?? null) ? $validated['full_article_delta'] : null,
            faq: $validated['faq'] ?? [],
            status: $validated['status'],
            visibility: $validated['visibility'],
            publishedAt: $validated['published_at'] ?? null,
            meta: $validated['meta'] ?? null,
            tagIds: $validated['tag_ids'] ?? [],
        );
    }
}
