<?php

namespace App\AI\Support;

use Throwable;

trait DecodesJsonResponse
{
    /**
     * @return array<string, mixed>|null
     */
    private function decodeJson(string $rawContent): ?array
    {
        $candidates = array_values(array_unique(array_filter([
            trim($rawContent),
            $this->stripMarkdownCodeFence($rawContent),
            $this->extractBalancedJsonObject($rawContent),
        ], static fn (?string $candidate): bool => is_string($candidate) && $candidate !== '')));

        foreach ($candidates as $candidate) {
            try {
                $decoded = json_decode($candidate, true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable) {
                continue;
            }

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function stripMarkdownCodeFence(string $rawContent): ?string
    {
        $trimmed = trim($rawContent);

        if (! preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $trimmed, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function extractBalancedJsonObject(string $rawContent): ?string
    {
        $start = strpos($rawContent, '{');

        if ($start === false) {
            return null;
        }

        $depth = 0;
        $inString = false;
        $isEscaped = false;
        $length = strlen($rawContent);

        for ($index = $start; $index < $length; $index++) {
            $char = $rawContent[$index];

            if ($inString) {
                if ($isEscaped) {
                    $isEscaped = false;

                    continue;
                }

                if ($char === '\\') {
                    $isEscaped = true;

                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;

                continue;
            }

            if ($char === '{') {
                $depth++;

                continue;
            }

            if ($char !== '}') {
                continue;
            }

            $depth--;

            if ($depth === 0) {
                return trim(substr($rawContent, $start, $index - $start + 1));
            }
        }

        return null;
    }
}
