<?php

namespace App\Modules\Media\Repositories;

use App\Models\Media;
use App\Modules\Media\Data\CreateMediaData;
use App\Modules\Media\Data\MediaFiltersData;
use App\Modules\Media\Data\UpdateMediaMetadataData;
use Illuminate\Database\Eloquent\Collection;

class EloquentMediaRepository implements MediaRepository
{
    public function create(CreateMediaData $data): Media
    {
        return Media::query()->create([
            'ulid' => $data->ulid,
            'uploaded_by_user_id' => $data->uploadedByUserId,
            'generated_by_ai_job_id' => $data->generatedByAiJobId,
            'storage_provider' => $data->storageProvider,
            'bucket_name' => $data->bucketName,
            'object_key' => $data->objectKey,
            'original_filename' => $data->originalFilename,
            'mime_type' => $data->mimeType,
            'extension' => $data->extension,
            'file_size_bytes' => $data->fileSizeBytes,
            'checksum_sha256' => $data->checksumSha256,
            'width' => $data->width,
            'height' => $data->height,
            'alt_text' => $data->altText,
            'caption' => $data->caption,
            'source_type' => $data->sourceType,
            'source_url' => $data->sourceUrl,
            'attribution_text' => $data->attributionText,
            'status' => $data->status,
            'metadata' => $data->metadata,
        ]);
    }

    public function updateMetadata(Media $media, UpdateMediaMetadataData $data): Media
    {
        $media->update([
            'alt_text' => $data->altText,
            'caption' => $data->caption,
            'source_type' => $data->sourceType,
            'source_url' => $data->sourceUrl,
            'attribution_text' => $data->attributionText,
            'metadata' => $data->metadata,
        ]);

        return $media->refresh();
    }

    public function findById(int $id): ?Media
    {
        return Media::query()->find($id);
    }

    public function findByUlid(string $ulid): ?Media
    {
        return Media::query()
            ->where('ulid', $ulid)
            ->first();
    }

    /**
     * @return Collection<int, Media>
     */
    public function getLatest(): Collection
    {
        return Media::query()
            ->latest()
            ->get();
    }

    /**
     * @return Collection<int, Media>
     */
    public function search(MediaFiltersData $filters): Collection
    {
        return Media::query()
            ->when($filters->search, function ($query, string $search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('original_filename', 'like', "%{$search}%")
                        ->orWhere('alt_text', 'like', "%{$search}%")
                        ->orWhere('caption', 'like', "%{$search}%")
                        ->orWhere('mime_type', 'like', "%{$search}%");
                });
            })
            ->when($filters->sourceType, fn ($query, string $sourceType) => $query->where('source_type', $sourceType))
            ->when($filters->mimeType, fn ($query, string $mimeType) => $query->where('mime_type', $mimeType))
            ->when($filters->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters->isImage !== null, function ($query) use ($filters): void {
                if ($filters->isImage) {
                    $query->whereNotNull('width')->whereNotNull('height');
                } else {
                    $query->where(function ($innerQuery): void {
                        $innerQuery->whereNull('width')->orWhereNull('height');
                    });
                }
            })
            ->latest()
            ->get();
    }

    public function markArchived(Media $media): Media
    {
        $media->update([
            'status' => 'archived',
        ]);

        $media->delete();

        return $media->refresh();
    }
}
