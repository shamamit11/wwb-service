<?php

namespace App\Modules\Media\Services;

use App\Models\Media;
use App\Modules\Media\Data\MediaUsageData;
use App\Modules\Media\Data\MediaUsageReferenceData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MediaUsageService
{
    public function usageFor(Media $media): MediaUsageData
    {
        $references = [];

        $featuredPostCount = $this->countIfTableAndColumnExist('posts', 'featured_media_id', $media->id);
        if ($featuredPostCount > 0) {
            $references[] = new MediaUsageReferenceData('featured_post', 'Featured post usage', $featuredPostCount);
        }

        $seoImageCount = $this->countIfTableAndColumnExist('seo_metadata', 'og_image_media_id', $media->id);
        if ($seoImageCount > 0) {
            $references[] = new MediaUsageReferenceData('seo_image', 'SEO image usage', $seoImageCount);
        }

        $knowledgeBaseCount = $this->countIfTableAndColumnExist('knowledge_base_entries', 'featured_media_id', $media->id);
        if ($knowledgeBaseCount > 0) {
            $references[] = new MediaUsageReferenceData('knowledge_base', 'Knowledge base usage', $knowledgeBaseCount);
        }

        return new MediaUsageData(
            usageCount: array_sum(array_map(static fn (MediaUsageReferenceData $reference): int => $reference->count, $references)),
            references: $references,
        );
    }

    /**
     * @param  iterable<Media>  $mediaItems
     * @return array<int, MediaUsageData>
     */
    public function usageMap(iterable $mediaItems): array
    {
        $usageMap = [];

        foreach ($mediaItems as $media) {
            $usageMap[$media->id] = $this->usageFor($media);
        }

        return $usageMap;
    }

    private function countIfTableAndColumnExist(string $table, string $column, int $mediaId): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)
            ->where($column, $mediaId)
            ->count();
    }
}
