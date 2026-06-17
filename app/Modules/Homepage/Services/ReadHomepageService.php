<?php

namespace App\Modules\Homepage\Services;

use App\Models\Homepage;
use App\Modules\Homepage\Repositories\HomepageRepository;

class ReadHomepageService
{
    public function __construct(
        private readonly HomepageRepository $homepages,
    ) {}

    public function handle(): Homepage
    {
        return $this->homepages->getSingleton();
    }
}
