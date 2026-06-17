<?php

namespace Database\Seeders;

use App\Models\Post;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostTagSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $post = Post::query()->where('slug', 'how-ai-agent-memory-works')->first();
        $tagIds = $this->firstTagIds();

        if ($post === null) {
            $this->warn('Skipping post tag seeding because the base post is missing.');

            return;
        }

        if ($tagIds === []) {
            $this->warn('Skipping post tag seeding because tags are excluded and no tag records exist.');

            return;
        }

        foreach ($tagIds as $tagId) {
            DB::table('post_tags')->updateOrInsert(
                [
                    'post_id' => $post->id,
                    'tag_id' => $tagId,
                ],
                [
                    'created_at' => now(),
                ],
            );
        }
    }
}
