<?php

namespace App\AI\DTO;

final readonly class TopicDiscoveryResult extends AgentOutput
{
    /**
     * @param  list<TopicSuggestionData>  $topics
     */
    public function __construct(
        public array $topics,
    ) {}
}
