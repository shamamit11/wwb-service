<?php

namespace App\Modules\Homepage\Repositories;

use App\Models\Homepage;
use App\Modules\Homepage\Data\UpdateHomepageData;

interface HomepageRepository
{
    public function getSingleton(): Homepage;

    public function update(Homepage $homepage, UpdateHomepageData $data): Homepage;
}
