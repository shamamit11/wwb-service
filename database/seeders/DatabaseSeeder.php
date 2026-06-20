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
            PageSeeder::class,
            HomepageSeeder::class,
            AboutPageSeeder::class,
            ContactPageSeeder::class,
            KnowledgeBaseEntrySeeder::class,
            SeoMetadataSeeder::class,
            AiPromptTemplateSeeder::class,
            ContentTopicSeeder::class,
            ContentBriefSeeder::class,
            NewsletterListSeeder::class,
            NewsletterSubscriberSeeder::class,
            NewsletterCampaignSeeder::class,
            PersonalAccessTokenSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
