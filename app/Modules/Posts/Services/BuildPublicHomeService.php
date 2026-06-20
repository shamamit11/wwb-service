<?php

namespace App\Modules\Posts\Services;

use App\Models\Homepage;
use App\Modules\Homepage\Repositories\HomepageRepository;

class BuildPublicHomeService
{
    public function __construct(
        private readonly HomepageRepository $homepages,
    ) {}

    public function handle(): Homepage
    {
        return $this->homepages->getSingleton();
    }
}
