<?php

namespace App\Modules\AboutPage\Repositories;

use App\Models\AboutPage;
use App\Modules\AboutPage\Data\UpdateAboutPageData;

interface AboutPageRepository
{
    public function getSingleton(): AboutPage;

    public function update(AboutPage $aboutPage, UpdateAboutPageData $data): AboutPage;
}
