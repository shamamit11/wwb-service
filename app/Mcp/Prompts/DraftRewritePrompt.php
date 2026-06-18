<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('draft-rewrite')]
#[Description('Reusable prompt for queueing a draft rewrite or targeted draft regeneration workflow.')]
class DraftRewritePrompt extends Prompt
{
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'post_id' => ['required', 'string'],
            'scope' => ['required', 'string'],
        ]);

        $text = implode("\n", [
            'Prepare a rewrite workflow for draft post #'.$validated['post_id'].'.',
            'Use `searchKnowledgeBase` if grounded editorial references are needed before rewriting.',
            'Queue the rewrite with `rewritePostDraft`, using scope `'.$validated['scope'].'`.',
            'Provide `target_block_ids` for section or paragraph rewrites when you want targeted regeneration.',
            'Track execution with `getAiJobStatus` until the rewrite workflow finishes.',
            'Do not publish, schedule, or expose the draft as live content.',
        ]);

        return Response::text($text)->asAssistant();
    }

    public function arguments(): array
    {
        return [
            new Argument('post_id', 'Draft post numeric ID or ULID to rewrite.', true),
            new Argument('scope', 'Rewrite scope: full_draft, section, or paragraph.', true),
        ];
    }
}
