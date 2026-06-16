<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Modules\KnowledgeBase\Data\LinkKnowledgeBaseEntryToTopicData;
use Illuminate\Foundation\Http\FormRequest;

class LinkKnowledgeBaseEntryToTopicRequest extends FormRequest
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
            'topic_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toData(): LinkKnowledgeBaseEntryToTopicData
    {
        /** @var array{topic_id:int} $validated */
        $validated = $this->validated();

        return new LinkKnowledgeBaseEntryToTopicData(
            topicId: $validated['topic_id'],
        );
    }
}
