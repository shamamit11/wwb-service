<?php

namespace App\AI\DTO;

final readonly class PostMetadataSuggestionInput extends AgentInput
{
    /**
     * @param  list<string>  $secondaryKeywords
     * @param  list<string>  $existingTags
     * @param  list<string>  $knowledgeBaseContext
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $postId,
        public string $postTitle,
        public string $postSlug,
        public ?string $postExcerpt,
        public string $postStatus,
        public ?string $primaryKeyword,
        public array $secondaryKeywords,
        public ?string $existingFocusKeyword,
        public ?string $existingMetaTitle,
        public ?string $existingMetaDescription,
        public string $existingMarkdownBody,
        public array $existingTags = [],
        public array $knowledgeBaseContext = [],
        public ?string $instructions = null,
        ?string $provider = null,
        ?string $model = null,
        ?int $timeoutSeconds = null,
        ?int $retryTimes = null,
        ?int $retrySleepMilliseconds = null,
        array $metadata = [],
    ) {
        parent::__construct(
            provider: $provider,
            model: $model,
            timeoutSeconds: $timeoutSeconds,
            retryTimes: $retryTimes,
            retrySleepMilliseconds: $retrySleepMilliseconds,
            metadata: $metadata,
        );
    }
}
