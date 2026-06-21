<?php

namespace App\Modules\News\Repositories;

use App\Models\NewsSource;
use App\Modules\News\Data\CreateNewsSourceData;

class EloquentNewsSourceRepository implements NewsSourceRepository
{
    public function upsert(CreateNewsSourceData $data): NewsSource
    {
        return NewsSource::query()->updateOrCreate(
            ['slug' => $data->slug],
            [
                'name' => $data->name,
                'kind' => $data->kind,
                'base_url' => $data->baseUrl,
                'trust_score' => $data->trustScore,
                'is_active' => $data->isActive,
                'metadata' => $data->metadata,
            ],
        );
    }

    public function findById(int $id): ?NewsSource
    {
        return NewsSource::query()->find($id);
    }
}
