<?php

namespace App\Infrastructure\News;

use App\Infrastructure\News\Contracts\NewsDiscoveryClient;
use App\Infrastructure\News\Data\DiscoveredNewsArticleData;
use App\Models\Category;
use Illuminate\Support\Facades\Http;

class CurrentsNewsClient implements NewsDiscoveryClient
{
    /**
     * @return list<DiscoveredNewsArticleData>
     */
    public function search(Category $category, string $query, int $limit): array
    {
        $apiKey = (string) config('services.currents.api_key');

        if ($apiKey === '') {
            return [];
        }

        $response = Http::baseUrl((string) config('services.currents.base_url'))
            ->timeout((int) config('services.currents.timeout', 15))
            ->withHeaders(['Authorization' => $apiKey])
            ->get('/search', [
                'keywords' => $query,
                'language' => config('news.discovery.language', 'en'),
                'country' => config('news.discovery.country', 'us'),
                'limit' => max(1, $limit),
            ])
            ->throw()
            ->json();

        $articles = is_array($response['news'] ?? null) ? $response['news'] : [];

        return array_values(array_filter(array_map(
            fn (mixed $article): ?DiscoveredNewsArticleData => $this->mapArticle($article),
            array_slice($articles, 0, max(1, $limit)),
        )));
    }

    private function mapArticle(mixed $article): ?DiscoveredNewsArticleData
    {
        if (! is_array($article)) {
            return null;
        }

        $title = $article['title'] ?? null;
        $url = $article['url'] ?? null;

        if (! is_string($title) || trim($title) === '' || ! is_string($url) || trim($url) === '') {
            return null;
        }

        $sourceUrl = is_string($article['source_url'] ?? null) ? $article['source_url'] : null;
        $domain = is_string($sourceUrl) ? parse_url($sourceUrl, PHP_URL_HOST) : parse_url($url, PHP_URL_HOST);

        return new DiscoveredNewsArticleData(
            externalId: is_string($article['id'] ?? null) ? $article['id'] : null,
            provider: 'currents',
            publisherName: is_string($article['author'] ?? null) ? $article['author'] : (is_string($article['source'] ?? null) ? $article['source'] : null),
            publisherDomain: is_string($domain) ? $domain : null,
            title: trim($title),
            url: trim($url),
            canonicalUrl: is_string($article['url'] ?? null) ? trim($article['url']) : null,
            description: is_string($article['description'] ?? null) ? trim($article['description']) : null,
            author: is_string($article['author'] ?? null) ? trim($article['author']) : null,
            language: is_string($article['language'] ?? null) ? $article['language'] : null,
            country: is_string($article['country'] ?? null) ? $article['country'] : null,
            publishedAt: is_string($article['published'] ?? null) ? $article['published'] : null,
            metadata: $article,
        );
    }
}
