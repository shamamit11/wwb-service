<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('blog-draft')]
#[Description('Reusable prompt for drafting a blog post from an approved topic without publishing it.')]
class BlogDraftPrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'content_topic_id' => ['required', 'integer', 'min:1'],
            'category_id' => ['required', 'integer', 'min:1'],
            'generation_mode' => ['sometimes', 'nullable', 'string'],
        ]);

        $modeLine = is_string($validated['generation_mode'] ?? null) && $validated['generation_mode'] !== ''
            ? 'Prefer the `'.$validated['generation_mode'].'` generation mode when queuing the draft.'
            : 'Use the default editorial generation mode unless a stronger article format is required.';

        $text = implode("\n", [
            'Prepare a draft workflow for approved topic #'.$validated['content_topic_id'].'.',
            'Use `searchKnowledgeBase` for editorial references that should shape the draft.',
            $modeLine,
            'Queue the draft with `generateBlogDraft`, providing category #'.$validated['category_id'].'.',
            'Track execution with `getAiJobStatus` until the draft workflow finishes.',
            'Do not publish, schedule, or expose the draft as live content.',
        ]);

        return Response::text($text)->asAssistant();
    }

    public function arguments(): array
    {
        return [
            new Argument('content_topic_id', 'Approved topic ID to draft from.', true),
            new Argument('category_id', 'Category ID for the queued draft.', true),
            new Argument('generation_mode', 'Optional editorial mode such as tutorial, comparison, opinionated_analysis, or checklist.', false),
        ];
    }
}
