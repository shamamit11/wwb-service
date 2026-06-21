<?php

namespace App\Modules\ContactPage\Repositories;

use App\Models\ContactPage;
use App\Modules\ContactPage\Data\UpdateContactPageData;

interface ContactPageRepository
{
    public function getSingleton(): ContactPage;

    public function update(ContactPage $contactPage, UpdateContactPageData $data): ContactPage;
}
