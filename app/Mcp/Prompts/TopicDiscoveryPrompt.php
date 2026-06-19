<?php

namespace App\Mcp\Prompts;

use App\Models\ContentTopic;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('topic-discovery')]
#[Description('Reusable prompt for discovering grounded topic ideas without publishing content.')]
class TopicDiscoveryPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'cluster' => ['required', 'string', 'in:'.implode(',', ContentTopic::CLUSTERS)],
            'audience' => ['sometimes', 'nullable', 'string', 'max:255'],
            'count' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ]);

        $text = implode("\n", array_filter([
            'Discover topic ideas for the '.$validated['cluster'].' cluster.',
            isset($validated['audience']) && $validated['audience'] !== '' ? 'Target audience: '.$validated['audience'].'.' : null,
            'Retrieve grounding context with `searchKnowledgeBase` before proposing ideas.',
            'Use `listContentTopics` to avoid duplicating existing suggested or approved topics.',
            'Before saving anything, call `createTopicSuggestion` and respect duplicate detection.',
            'Return only suggested topics. Do not approve, publish, or schedule content.',
        ]));

        return Response::text($text)->asAssistant();
    }

    public function arguments(): array
    {
        return [
            new Argument('cluster', 'One of the supported content clusters.', true),
            new Argument('audience', 'Optional audience or persona for the topic set.'),
            new Argument('count', 'Optional number of ideas to explore, up to 10.'),
        ];
    }
}
