<?php

namespace App\Modules\Auth\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class AdminLoginData extends DataTransferObject
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName,
    ) {}
}
