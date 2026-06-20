<?php

namespace App\Modules\ContactPage\Services;

use App\Models\ContactPage;
use App\Modules\ContactPage\Repositories\ContactPageRepository;

class BuildPublicContactPageService
{
    public function __construct(
        private readonly ContactPageRepository $contactPages,
    ) {}

    public function handle(): ContactPage
    {
        return $this->contactPages->getSingleton();
    }
}
