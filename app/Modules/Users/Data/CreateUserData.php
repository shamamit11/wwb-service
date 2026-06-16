<?php

namespace App\Modules\Users\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class CreateUserData extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}
}
