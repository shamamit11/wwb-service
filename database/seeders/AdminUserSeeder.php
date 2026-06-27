<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public const NAME = 'Admin User';

    public const EMAIL = 'admin@example.com';

    public const PASSWORD = 'admin12345';

    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => self::NAME,
                'password' => self::PASSWORD,
                'is_admin' => true,
            ],
        );
    }
}
