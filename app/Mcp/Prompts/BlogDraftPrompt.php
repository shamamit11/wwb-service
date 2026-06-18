<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('blog-draft')]
#[Description('Reusable prompt for drafting a blog post from an approved brief without publishing it.')]
class BlogDraftPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'content_brief_id' => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'integer', 'min:1'],
        ]);

        $text = implode("\n", [
            'Prepare a draft workflow for content brief #'.$validated['content_brief_id'].'.',
            'Use `searchKnowledgeBase` for editorial references that should shape the draft.',
            'Queue the draft with `generateBlogDraft`, providing category #'.$validated['category_id'].'.',
            'Track execution with `getAiJobStatus` until the draft workflow finishes.',
            'Do not publish, schedule, or expose the draft as live content.',
        ]);

        return Response::text($text)->asAssistant();
    }

    public function arguments(): array
    {
        return [
            new Argument('content_brief_id', 'Approved brief ID to draft from.', true),
            new Argument('category_id', 'Category ID for the queued draft.', true),
        ];
    }
}
