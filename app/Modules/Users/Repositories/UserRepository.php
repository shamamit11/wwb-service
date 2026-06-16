<?php

namespace App\Modules\Users\Repositories;

use App\Models\User;
use App\Modules\Users\Data\CreateUserData;

interface UserRepository
{
    public function create(CreateUserData $data): User;

    public function findByEmail(string $email): ?User;
}
