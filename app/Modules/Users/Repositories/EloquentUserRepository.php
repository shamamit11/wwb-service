<?php

namespace App\Modules\Users\Repositories;

use App\Models\User;
use App\Modules\Users\Data\CreateUserData;

class EloquentUserRepository implements UserRepository
{
    public function create(CreateUserData $data): User
    {
        return User::query()->create($data->toArray());
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', strtolower($email))
            ->first();
    }
}
