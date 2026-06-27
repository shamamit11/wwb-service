<?php

namespace App\Modules\Posts\Services;

use App\Models\Media;
use App\Models\Post;
use App\Modules\Media\Services\Contracts\MediaReader;

class SyncPostInlineMediaService
{
    public function __construct(
        private readonly MediaReader $mediaReader,
    ) {}

    public function handle(Post $post): void
    {
        $mediaIds = array_values(array_unique(array_merge(
            $this->extractMediaIdsFromHtml((string) ($post->full_article_html ?? '')),
            $this->extractMediaIdsFromDelta($post->full_article_delta),
            $this->resolveMediaIdsFromUrls(array_merge(
                $this->extractImageUrlsFromHtml((string) ($post->full_article_html ?? '')),
                $this->extractImageUrlsFromDelta($post->full_article_delta),
            )),
        )));

        $post->inlineMedia()->sync($mediaIds);
    }

    /**
     * @return list<int>
     */
    private function extractMediaIdsFromHtml(string $html): array
    {
        if ($html === '') {
            return [];
        }

        preg_match_all('/data-media-id=["\'](\d+)["\']/i', $html, $matches);

        return array_values(array_map(
            static fn (string $id): int => (int) $id,
            $matches[1] ?? [],
        ));
    }

    /**
     * @param  array<int|string, mixed>|null  $delta
     * @return list<int>
     */
    private function extractMediaIdsFromDelta(?array $delta): array
    {
        if (! is_array($delta)) {
            return [];
        }

        $ids = [];

        foreach (($delta['ops'] ?? []) as $op) {
            if (! is_array($op)) {
                continue;
            }

            $attributes = $op['attributes'] ?? null;

            if (is_array($attributes) && isset($attributes['mediaId']) && is_numeric($attributes['mediaId'])) {
                $ids[] = (int) $attributes['mediaId'];
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<string>
     */
    private function extractImageUrlsFromHtml(string $html): array
    {
        if ($html === '') {
            return [];
        }

        preg_match_all('/<img\b[^>]*\bsrc=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

        return array_values(array_filter(
            $matches[1] ?? [],
            static fn (mixed $url): bool => is_string($url) && trim($url) !== '',
        ));
    }

    /**
     * @param  array<int|string, mixed>|null  $delta
     * @return list<string>
     */
    private function extractImageUrlsFromDelta(?array $delta): array
    {
        if (! is_array($delta)) {
            return [];
        }

        $urls = [];

        foreach (($delta['ops'] ?? []) as $op) {
            if (! is_array($op)) {
                continue;
            }

            $insert = $op['insert'] ?? null;

            if (is_array($insert) && is_string($insert['image'] ?? null) && trim($insert['image']) !== '') {
                $urls[] = trim($insert['image']);
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param  list<string>  $urls
     * @return list<int>
     */
    private function resolveMediaIdsFromUrls(array $urls): array
    {
        if ($urls === []) {
            return [];
        }

        $normalizedUrls = array_values(array_unique(array_map(
            fn (string $url): string => $this->normalizeUrl($url),
            $urls,
        )));

        $ids = [];

        foreach (Media::query()->get() as $media) {
            $mediaUrl = $this->normalizeUrl($this->mediaReader->url($media));

            if (in_array($mediaUrl, $normalizedUrls, true)) {
                $ids[] = (int) $media->id;

                continue;
            }

            foreach ($normalizedUrls as $url) {
                if (str_ends_with(parse_url($url, PHP_URL_PATH) ?: $url, '/'.ltrim((string) $media->object_key, '/'))) {
                    $ids[] = (int) $media->id;
                    break;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private function normalizeUrl(string $url): string
    {
        return rtrim(trim($url), '/');
    }
}
