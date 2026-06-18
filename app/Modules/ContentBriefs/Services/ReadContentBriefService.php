<?php

namespace App\Modules\ContentBriefs\Services;

use App\Models\ContentBrief;
use App\Modules\ContentBriefs\Repositories\ContentBriefRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ReadContentBriefService
{
    public function __construct(
        private readonly ContentBriefRepository $briefs,
    ) {}

    public function handle(int $id): ContentBrief
    {
        return $this->briefs->findById($id) ?? throw (new ModelNotFoundException)->setModel(ContentBrief::class, [$id]);
    }
}
