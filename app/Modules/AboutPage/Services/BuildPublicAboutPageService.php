<?php

namespace App\Modules\AboutPage\Services;

use App\Models\AboutPage;
use App\Modules\AboutPage\Repositories\AboutPageRepository;

class BuildPublicAboutPageService
{
    public function __construct(
        private readonly AboutPageRepository $aboutPages,
    ) {}

    public function handle(): AboutPage
    {
        return $this->aboutPages->getSingleton();
    }
}
