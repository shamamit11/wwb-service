<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('content-brief')]
#[Description('Reusable prompt for generating a brief from an approved topic with grounded references.')]
class ContentBriefPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'content_topic_id' => ['required', 'integer', 'min:1'],
        ]);

        $text = implode("\n", [
            'Generate a content brief for approved topic #'.$validated['content_topic_id'].'.',
            'Use `searchKnowledgeBase` to gather editorial references and domain constraints first.',
            'Review adjacent topics with `listContentTopics` if you need nearby editorial context.',
            'Call `generateContentBrief` once the topic is clearly scoped.',
            'The brief must remain a draft or reusable artifact. Do not publish any post content.',
        ]);

        return Response::text($text)->asAssistant();
    }

    public function arguments(): array
    {
        return [
            new Argument('content_topic_id', 'Approved topic ID to expand into a content brief.', true),
        ];
    }
}
