<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            MediaSeeder::class,
            TemplateSeeder::class,
            TemplateBlockSeeder::class,
            PostSeeder::class,
            PostTagSeeder::class,
            PostBlockSeeder::class,
            KnowledgeBaseEntrySeeder::class,
            SeoMetadataSeeder::class,
            PersonalAccessTokenSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
