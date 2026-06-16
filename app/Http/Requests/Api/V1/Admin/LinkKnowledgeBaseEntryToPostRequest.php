<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\KnowledgeBase\Data\LinkKnowledgeBaseEntryToPostData;
use Illuminate\Foundation\Http\FormRequest;

class LinkKnowledgeBaseEntryToPostRequest extends FormRequest
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
            'post_id' => ['required', 'integer', 'exists:posts,id'],
        ];
    }

    public function toData(): LinkKnowledgeBaseEntryToPostData
    {
        /** @var array{post_id:int} $validated */
        $validated = $this->validated();

        return new LinkKnowledgeBaseEntryToPostData(
            postId: $validated['post_id'],
        );
    }
}
