<?php

namespace App\Modules\Ai\Services;

use App\Models\Category;

class ResolveCategoryDiscoveryBriefService
{
    public function forCategory(Category $category): ?string
    {
        return match ($category->slug) {
            'ai-tools' => $this->aiToolsBrief(),
            default => null,
        };
    }

    private function aiToolsBrief(): string
    {
        return implode(' ', [
            'Focus on practical, tool-specific, commercially relevant AI content.',
            'Assume readers are evaluating, comparing, selecting, or using real AI tools.',
            'Favor named tools, tool categories, comparisons, reviews, alternatives, best-tools-for-X, and pricing, features, workflow, or integration angles.',
            'Prioritize commercial investigation, comparison, alternatives, review, best-for, and workflow evaluation.',
            'Relevant examples include Claude, Codex, Gemini, Kiro, image generation tools, voice generation tools, coding assistants, research tools, writing tools, agent-building tools, and automation tools.',
            'Avoid generic AI thought leadership, broad future-of-AI topics, abstract AI ethics, non-tool AI theory, prompt engineering without a product angle, vague hype topics, and obvious ad copy.',
            'Topics should still be useful even if later sponsor-supported.',
            'Prefer specific tools plus specific use cases plus commercial or editorial usefulness over generic AI topics.',
        ]);
    }
}
