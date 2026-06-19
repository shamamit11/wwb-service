<?php

namespace App\Modules\Auth\Data;

use App\Modules\Shared\Data\DataTransferObject;

final readonly class ChangePasswordData extends DataTransferObject
{
    public function __construct(
        public string $currentPassword,
        public string $password,
    ) {}
}
