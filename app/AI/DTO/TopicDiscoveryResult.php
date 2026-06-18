<?php

namespace App\AI\DTO;

final readonly class TopicDiscoveryResult extends AgentOutput
{
    /**
     * @param  list<array<string, mixed>>  $topics
     */
    public function __construct(
        public array $topics,
    ) {}
}
