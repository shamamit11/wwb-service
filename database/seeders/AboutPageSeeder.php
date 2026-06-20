<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        AboutPage::query()->updateOrCreate(
            ['singleton_key' => AboutPage::SINGLETON_KEY],
            [
                ...AboutPage::defaultPayload(),
                'singleton_key' => AboutPage::SINGLETON_KEY,
                'hero' => [
                    'eyebrow' => 'Our Story',
                    'title' => 'Navigating the digital frontier together.',
                    'description' => 'Wide Web Blog was founded on the belief that technology should be accessible, insightful, and growth-oriented.',
                    'media_url' => null,
                    'media_alt' => null,
                ],
                'mission_section' => [
                    'title' => 'Our Mission: Fueling Digital Growth',
                    'description' => 'We bridge the gap between complex engineering concepts and strategic business outcomes.',
                    'quote' => 'The future is not just about code, it is about how we leverage it to amplify human potential.',
                ],
                'stats_section' => [
                    'items' => [
                        ['label' => 'Articles Published', 'value' => '500+'],
                        ['label' => 'Monthly Readers', 'value' => '120K'],
                        ['label' => 'Global Experts', 'value' => '12'],
                        ['label' => 'Independent', 'value' => '100%'],
                    ],
                ],
                'values_section' => [
                    'title' => 'The Values We Live By',
                    'items' => [
                        [
                            'icon' => 'clarity',
                            'title' => 'Authentic Clarity',
                            'description' => 'We prioritize honesty and transparency in every piece of content.',
                        ],
                        [
                            'icon' => 'growth',
                            'title' => 'Growth Mindset',
                            'description' => 'Continuous learning is at our core as we explore emerging technologies.',
                        ],
                        [
                            'icon' => 'community',
                            'title' => 'Community Focused',
                            'description' => 'Technology is human. We build platforms that foster dialogue and collaboration.',
                        ],
                    ],
                ],
                'team_section' => [
                    'title' => 'Meet the Minds',
                    'description' => 'Our multidisciplinary team combines decades of expertise in software engineering, digital marketing, and thoughtful journalism.',
                    'primary_cta_label' => 'Join the Team',
                    'primary_cta_url' => null,
                    'members' => [
                        ['name' => 'Alexander Chen', 'role' => 'Editor-in-Chief', 'image_url' => null, 'image_alt' => null],
                        ['name' => 'Sarah Jenkins', 'role' => 'Head of AI Research', 'image_url' => null, 'image_alt' => null],
                        ['name' => 'Marcus Thorne', 'role' => 'Lead Developer', 'image_url' => null, 'image_alt' => null],
                        ['name' => 'Elena Rodriguez', 'role' => 'Strategy Director', 'image_url' => null, 'image_alt' => null],
                    ],
                ],
                'seo' => [
                    'meta_title' => 'About Wide Web Blog',
                    'meta_description' => 'Learn more about Wide Web Blog, our mission, values, and editorial team.',
                ],
                'updated_by_user_id' => $admin?->id,
            ],
        );
    }
}
