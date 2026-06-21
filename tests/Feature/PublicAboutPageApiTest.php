<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAboutPageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_about_returns_structured_about_page_payload(): void
    {
        AboutPage::query()->create([
            'singleton_key' => AboutPage::SINGLETON_KEY,
            'hero' => [
                'eyebrow' => 'Our story',
                'title' => 'Navigating the digital frontier together.',
                'description' => 'Technology should be accessible, insightful, and growth-oriented.',
                'media_url' => 'https://cdn.widewebblog.test/about/hero.png',
                'media_alt' => 'About page office photograph',
            ],
            'mission_section' => [
                'title' => 'Our Mission: Fueling Digital Growth',
                'description' => 'We provide the context needed to thrive in an era of rapid AI evolution.',
                'quote' => 'The future is about how we leverage technology to amplify human potential.',
            ],
            'stats_section' => [
                'items' => [
                    ['label' => 'Articles Published', 'value' => '500+'],
                    ['label' => 'Monthly Readers', 'value' => '120K'],
                ],
            ],
            'values_section' => [
                'title' => 'The Values We Live By',
                'items' => [
                    ['icon' => 'clarity', 'title' => 'Authentic Clarity', 'description' => 'We prioritize honesty.'],
                ],
            ],
            'team_section' => [
                'title' => 'Meet the Minds',
                'description' => 'Our multidisciplinary team combines decades of expertise.',
                'primary_cta_label' => 'Join the Team',
                'primary_cta_url' => 'https://widewebblog.test/careers',
                'members' => [
                    ['name' => 'Sarah Jenkins', 'role' => 'Head of AI Research', 'image_url' => 'https://cdn.widewebblog.test/about/sarah.png', 'image_alt' => 'Sarah portrait'],
                ],
            ],
            'seo' => [
                'meta_title' => 'About Wide Web Blog',
                'meta_description' => 'Learn more about our mission, values, and team.',
            ],
            'updated_by_user_id' => null,
        ]);

        $this->getJson('/api/v1/public/about')
            ->assertOk()
            ->assertJsonPath('data.hero.title', 'Navigating the digital frontier together.')
            ->assertJsonPath('data.mission_section.title', 'Our Mission: Fueling Digital Growth')
            ->assertJsonPath('data.stats_section.items.1.value', '120K')
            ->assertJsonPath('data.values_section.items.0.icon', 'clarity')
            ->assertJsonPath('data.team_section.members.0.name', 'Sarah Jenkins')
            ->assertJsonPath('data.seo.meta_title', 'About Wide Web Blog')
            ->assertJsonMissingPath('data.updated_by')
            ->assertJsonMissingPath('data.updated_at');
    }

    public function test_public_about_bootstraps_default_about_page_shape(): void
    {
        $this->getJson('/api/v1/public/about')
            ->assertOk()
            ->assertJsonPath('data.hero.title', null)
            ->assertJsonPath('data.mission_section.quote', null)
            ->assertJsonPath('data.stats_section.items', [])
            ->assertJsonPath('data.values_section.items', [])
            ->assertJsonPath('data.team_section.members', [])
            ->assertJsonPath('data.seo.meta_title', null);

        $this->assertDatabaseHas('about_pages', [
            'singleton_key' => AboutPage::SINGLETON_KEY,
        ]);
    }
}
