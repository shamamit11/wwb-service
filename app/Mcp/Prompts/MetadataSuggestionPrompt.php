<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('metadata-suggestion')]
#[Description('Reusable prompt for queueing review-only title, excerpt, and SEO metadata suggestions for a post draft.')]
class MetadataSuggestionPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'post_id' => ['required', 'string'],
        ]);

        $text = implode("\n", [
            'Prepare a metadata suggestion workflow for post #'.$validated['post_id'].'.',
            'Use `searchKnowledgeBase` if editorial rules or grounded references should shape the metadata suggestions.',
            'Queue the suggestion run with `suggestPostMetadata`.',
            'Track execution with `getAiJobStatus` until the suggestion workflow finishes.',
            'Treat the output as review-only. Do not publish or auto-apply metadata changes.',
        ]);

        return Response::text($text)->asAssistant();
    }

    public function arguments(): array
    {
        return [
            new Argument('post_id', 'Draft or post numeric ID or ULID to analyze.', true),
        ];
    }
}
