<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('seo-review')]
#[Description('Reusable prompt for reviewing a topic, brief, or draft against grounded SEO references.')]
class SeoReviewPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'focus_keyword' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        $text = implode("\n", array_filter([
            'Run an SEO review for: '.$validated['subject'].'.',
            isset($validated['focus_keyword']) && $validated['focus_keyword'] !== '' ? 'Focus keyword: '.$validated['focus_keyword'].'.' : null,
            'Use `searchKnowledgeBase` to gather editorial rules, internal standards, and source material before making recommendations.',
            'Use `listContentTopics` to inspect overlap with nearby approved or suggested topics when cannibalization is a concern.',
            'Keep the review advisory. Do not publish or mutate post state from this workflow.',
        ]));

        return Response::text($text)->asAssistant();
    }

    public function arguments(): array
    {
        return [
            new Argument('subject', 'The draft, brief, or topic being reviewed.', true),
            new Argument('focus_keyword', 'Optional primary SEO keyword to review against.'),
        ];
    }
}
