<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalAccessTokenSeeder extends Seeder
{
    use SeederSupport;

    public const TOKEN_NAME = 'seeded-admin-token';

    public const PLAIN_TEXT_TOKEN = 'seeded-admin-access-token';

    public function run(): void
    {
        $admin = $this->adminUser();

        if ($admin === null) {
            $this->warn('Skipping personal access token seeding because no user record exists.');

            return;
        }

        DB::table('personal_access_tokens')->updateOrInsert(
            [
                'tokenable_type' => User::class,
                'tokenable_id' => $admin->id,
                'name' => self::TOKEN_NAME,
            ],
            [
                'token' => hash('sha256', self::PLAIN_TEXT_TOKEN),
                'abilities' => json_encode(['admin:access'], JSON_THROW_ON_ERROR),
                'last_used_at' => null,
                'expires_at' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
