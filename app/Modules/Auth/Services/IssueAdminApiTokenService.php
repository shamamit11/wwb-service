<?php

namespace App\Modules\Auth\Services;

use App\Models\User;
use App\Modules\Auth\Data\AdminLoginData;
use App\Modules\Users\Repositories\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class IssueAdminApiTokenService
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    /**
     * @return array{token:string,token_type:string,abilities:list<string>,user:User}
     */
    public function handle(AdminLoginData $data): array
    {
        $user = $this->users->findByEmail($data->email);

        if (! $user || ! Hash::check($data->password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (! $user->is_admin) {
            throw new AccessDeniedHttpException('Admin access is required.');
        }

        $abilities = ['admin:access'];
        $token = $user->createToken($data->deviceName, $abilities);

        return [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'abilities' => $abilities,
            'user' => $user,
        ];
    }
}
