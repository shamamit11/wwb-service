<?php

namespace App\AI\Context;

use App\Models\KnowledgeBaseEntry;
use App\Modules\KnowledgeBase\Data\KnowledgeContextQueryData;
use Illuminate\Support\Collection;

class KnowledgeContextFormatter
{
    /**
     * @param  Collection<int, KnowledgeBaseEntry>  $entries
     * @return list<string>
     */
    public function format(Collection $entries, KnowledgeContextQueryData $query): array
    {
        $maxEntries = max(1, min(20, $query->maxEntries));
        $maxEntryCharacters = max(40, min(1200, $query->maxEntryCharacters));
        $maxTotalCharacters = max(40, min(12000, $query->maxTotalCharacters));

        $lines = [];
        $usedCharacters = 0;

        foreach ($entries as $entry) {
            if (count($lines) >= $maxEntries) {
                break;
            }

            $line = $this->formatEntry($entry, $maxEntryCharacters);

            if ($line === '') {
                continue;
            }

            $remainingCharacters = $maxTotalCharacters - $usedCharacters;

            if ($remainingCharacters < 40) {
                break;
            }

            if (mb_strlen($line) > $remainingCharacters) {
                $line = $this->truncate($line, $remainingCharacters);
            }

            if ($line === '') {
                break;
            }

            $lines[] = $line;
            $usedCharacters += mb_strlen($line);
        }

        return $lines;
    }

    private function formatEntry(KnowledgeBaseEntry $entry, int $maxCharacters): string
    {
        $prefix = '['.$entry->entry_type.'] '.$entry->title;
        $body = $this->collapseWhitespace($entry->summary ?: $entry->content_markdown);

        if ($body === '') {
            return '';
        }

        $sourceSuffix = '';

        if (is_string($entry->source_url) && $entry->source_url !== '') {
            $sourceSuffix = ' Source: '.$entry->source_url;
        }

        $base = $prefix.': ';
        $availableBodyCharacters = $maxCharacters - mb_strlen($base) - mb_strlen($sourceSuffix);

        if ($sourceSuffix !== '' && $availableBodyCharacters > 0) {
            return $base.$this->truncate($body, $availableBodyCharacters).$sourceSuffix;
        }

        return $this->truncate($base.$body.$sourceSuffix, $maxCharacters);
    }

    private function collapseWhitespace(?string $value): string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return '';
        }

        return (string) preg_replace('/\s+/u', ' ', $normalized);
    }

    private function truncate(string $value, int $limit): string
    {
        $normalized = trim($value);

        if ($normalized === '' || $limit <= 0) {
            return '';
        }

        if (mb_strlen($normalized) <= $limit) {
            return $normalized;
        }

        if ($limit <= 3) {
            return mb_substr($normalized, 0, $limit);
        }

        return rtrim(mb_substr($normalized, 0, $limit - 3)).'...';
    }
}
