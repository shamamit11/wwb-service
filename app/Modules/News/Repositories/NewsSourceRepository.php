<?php

namespace App\Modules\News\Repositories;

use App\Models\NewsSource;
use App\Modules\News\Data\CreateNewsSourceData;

interface NewsSourceRepository
{
    public function upsert(CreateNewsSourceData $data): NewsSource;

    public function findById(int $id): ?NewsSource;
}
