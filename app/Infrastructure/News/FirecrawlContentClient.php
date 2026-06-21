<?php

namespace App\Infrastructure\News;

use App\Infrastructure\News\Contracts\NewsContentExtractionClient;
use App\Infrastructure\News\Data\ExtractedNewsContentData;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FirecrawlContentClient implements NewsContentExtractionClient
{
    public function extract(string $url): ExtractedNewsContentData
    {
        $apiKey = (string) config('services.firecrawl.api_key');

        if ($apiKey === '') {
            return new ExtractedNewsContentData(
                contentMarkdown: null,
                contentText: null,
                excerpt: null,
                facts: null,
                entities: null,
                claims: null,
                metadata: ['skipped' => 'missing_api_key'],
            );
        }

        $response = Http::baseUrl((string) config('services.firecrawl.base_url'))
            ->timeout((int) config('services.firecrawl.timeout', 30))
            ->withToken($apiKey)
            ->post('/scrape', [
                'url' => $url,
                'formats' => ['markdown'],
            ])
            ->throw()
            ->json();

        $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        $markdown = is_string($data['markdown'] ?? null) ? trim($data['markdown']) : null;
        $text = $markdown !== null ? trim(Str::of($markdown)->replaceMatches('/[#>*`\\-]+/', ' ')->replaceMatches('/\\s+/', ' ')->toString()) : null;
        $excerpt = $text !== null ? Str::limit($text, 320) : null;

        return new ExtractedNewsContentData(
            contentMarkdown: $markdown,
            contentText: $text,
            excerpt: $excerpt,
            facts: [
                'url' => $url,
                'title' => is_string($data['metadata']['title'] ?? null) ? $data['metadata']['title'] : null,
                'published_at' => is_string($data['metadata']['publishedTime'] ?? null) ? $data['metadata']['publishedTime'] : null,
            ],
            entities: [
                'site_name' => is_string($data['metadata']['siteName'] ?? null) ? $data['metadata']['siteName'] : null,
                'domain' => parse_url($url, PHP_URL_HOST),
            ],
            claims: $this->extractClaims($markdown),
            metadata: $data,
        );
    }

    /**
     * @return array<int, string>|null
     */
    private function extractClaims(?string $markdown): ?array
    {
        if ($markdown === null || trim($markdown) === '') {
            return null;
        }

        $segments = preg_split('/(?<=[.!?])\s+/', trim($markdown)) ?: [];
        $claims = array_values(array_filter(array_map(
            static fn (string $segment): ?string => ($normalized = trim(strip_tags($segment))) !== '' ? Str::limit($normalized, 220, '') : null,
            array_slice($segments, 0, 5),
        )));

        return $claims === [] ? null : $claims;
    }
}
