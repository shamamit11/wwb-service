<?php

namespace Database\Seeders;

use App\Models\Post;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityLogSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();
        $post = Post::query()->where('slug', 'how-ai-agent-memory-works')->first();

        if ($admin === null) {
            $this->warn('Skipping activity log seeding because no user record exists.');

            return;
        }

        if ($post === null) {
            $this->warn('Skipping activity log seeding because the base post is missing.');

            return;
        }

        DB::table('activity_log')
            ->where('log_name', 'seed')
            ->where('description', 'post.seeded')
            ->where('subject_type', Post::class)
            ->where('subject_id', $post->id)
            ->delete();

        DB::table('activity_log')->insert([
            'log_name' => 'seed',
            'description' => 'post.seeded',
            'subject_type' => Post::class,
            'subject_id' => $post->id,
            'event' => 'seeded',
            'causer_type' => get_class($admin),
            'causer_id' => $admin->id,
            'attribute_changes' => json_encode([
                'attributes' => ['status' => $post->status],
            ], JSON_THROW_ON_ERROR),
            'properties' => json_encode([
                'seeded' => true,
                'source' => 'database-seeder',
            ], JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
