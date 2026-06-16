<?php

namespace App\Modules\Media\Repositories;

use App\Models\Media;
use App\Modules\Media\Data\CreateMediaData;

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

    public function markArchived(Media $media): Media
    {
        $media->update([
            'status' => 'archived',
        ]);

        $media->delete();

        return $media->refresh();
    }
}
