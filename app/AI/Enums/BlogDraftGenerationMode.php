<?php

namespace App\AI\Enums;

enum BlogDraftGenerationMode: string
{
    case Tutorial = 'tutorial';
    case Comparison = 'comparison';
    case OpinionatedAnalysis = 'opinionated_analysis';
    case Checklist = 'checklist';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $mode): string => $mode->value,
            self::cases(),
        );
    }

    public function promptKey(): string
    {
        return 'blog_writer_'.$this->value;
    }

    public function guidance(): string
    {
        return match ($this) {
            self::Tutorial => 'Frame the draft as a step-by-step tutorial with clear progression, practical examples, and implementation-oriented guidance.',
            self::Comparison => 'Frame the draft as a balanced comparison that evaluates options, trade-offs, strengths, and weaknesses with clear decision criteria.',
            self::OpinionatedAnalysis => 'Frame the draft as an opinionated analysis with a clear editorial stance, defensible arguments, and nuanced supporting evidence.',
            self::Checklist => 'Frame the draft as a checklist-driven article with concise, actionable sections, scannable structure, and explicit completion criteria.',
        };
    }
}
