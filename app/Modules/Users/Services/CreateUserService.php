<?php

namespace App\Modules\Users\Services;

use App\Models\User;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Repositories\UserRepository;

class CreateUserService
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function handle(CreateUserData $data): User
    {
        return $this->users->create($data);
    }
}
