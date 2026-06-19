<?php

namespace App\AI\DTO;

final readonly class PostRewriteInput extends AgentInput
{
    /**
     * @param  list<array<string, mixed>>  $existingContentBlocks
     * @param  list<int>  $targetBlockIds
     * @param  list<array<string, mixed>>  $targetBlocks
     * @param  list<string>  $knowledgeBaseContext
     * @param  list<array<string, mixed>>  $briefOutline
     * @param  list<string>  $briefHeadings
     * @param  list<array<string, mixed>>  $faqSuggestions
     * @param  list<string>  $secondaryKeywords
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $postId,
        public int $contentBriefId,
        public int $contentTopicId,
        public string $scope,
        public string $postTitle,
        public string $postSlug,
        public ?string $postExcerpt,
        public ?string $primaryKeyword,
        public array $secondaryKeywords,
        public ?string $searchIntent,
        public ?string $instructions,
        public string $existingMarkdownBody,
        public array $existingContentBlocks = [],
        public array $targetBlockIds = [],
        public array $targetBlocks = [],
        public array $knowledgeBaseContext = [],
        public array $briefOutline = [],
        public array $briefHeadings = [],
        public array $faqSuggestions = [],
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
