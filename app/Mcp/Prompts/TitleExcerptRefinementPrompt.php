<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('title-excerpt-refinement')]
#[Description('Reusable prompt for queueing review-only title, excerpt, and headline variation suggestions for a post draft.')]
class TitleExcerptRefinementPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'post_id' => ['required', 'string'],
        ]);

        $text = implode("\n", [
            'Prepare a title and excerpt refinement workflow for post #'.$validated['post_id'].'.',
            'Use `searchKnowledgeBase` if brand, editorial, or grounded reference notes should shape the refinement suggestions.',
            'Queue the suggestion run with `refinePostTitleExcerpt`.',
            'Track execution with `getAiJobStatus` until the refinement workflow finishes.',
            'Treat the output as review-only. Do not publish or auto-apply title or excerpt changes.',
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
