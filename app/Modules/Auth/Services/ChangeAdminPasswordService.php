<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Data\ChangePasswordData;

class ChangeAdminPasswordService
{
    public function handle(User $user, ChangePasswordData $data): User
    {
        $user->forceFill([
            'password' => $data->password,
        ])->save();

        return $user->refresh();
    }
}
