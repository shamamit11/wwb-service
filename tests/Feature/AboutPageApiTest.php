<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_admin_routes_require_admin_access(): void
    {
        $this->getJson('/api/v1/admin/about-page')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');

        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/about-page')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN');
    }

    public function test_admin_can_fetch_bootstrapped_about_page_defaults(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/about-page')
            ->assertOk()
            ->assertJsonPath('data.hero.title', null)
            ->assertJsonPath('data.mission_section.quote', null)
            ->assertJsonPath('data.stats_section.items', [])
            ->assertJsonPath('data.values_section.items', [])
            ->assertJsonPath('data.team_section.members', [])
            ->assertJsonPath('data.seo.meta_title', null)
            ->assertJsonPath('data.updated_by', null);

        $this->assertDatabaseCount('about_pages', 1);
        $this->assertDatabaseHas('about_pages', [
            'singleton_key' => AboutPage::SINGLETON_KEY,
            'updated_by_user_id' => null,
        ]);
    }

    public function test_admin_can_update_about_page_and_preserve_array_ordering(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $payload = [
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
                    ['label' => 'Global Experts', 'value' => '12'],
                ],
            ],
            'values_section' => [
                'title' => 'The Values We Live By',
                'items' => [
                    ['icon' => 'community', 'title' => 'Community Focused', 'description' => 'Technology is human.'],
                    ['icon' => 'clarity', 'title' => 'Authentic Clarity', 'description' => 'We prioritize honesty.'],
                    ['icon' => 'growth', 'title' => 'Growth Mindset', 'description' => 'Continuous learning is at our core.'],
                ],
            ],
            'team_section' => [
                'title' => 'Meet the Minds',
                'description' => 'Our multidisciplinary team combines decades of expertise.',
                'primary_cta_label' => 'Join the Team',
                'primary_cta_url' => 'https://widewebblog.test/careers',
                'members' => [
                    ['name' => 'Marcus Thorne', 'role' => 'Lead Developer', 'image_url' => 'https://cdn.widewebblog.test/about/marcus.png', 'image_alt' => 'Marcus portrait'],
                    ['name' => 'Sarah Jenkins', 'role' => 'Head of AI Research', 'image_url' => 'https://cdn.widewebblog.test/about/sarah.png', 'image_alt' => 'Sarah portrait'],
                ],
            ],
            'seo' => [
                'meta_title' => 'About Wide Web Blog',
                'meta_description' => 'Learn more about our mission, values, and team.',
            ],
        ];

        $this->withToken($token)
            ->putJson('/api/v1/admin/about-page', $payload)
            ->assertOk()
            ->assertJsonPath('data.hero.media_url', 'https://cdn.widewebblog.test/about/hero.png')
            ->assertJsonPath('data.stats_section.items.1.label', 'Monthly Readers')
            ->assertJsonPath('data.values_section.items.0.title', 'Community Focused')
            ->assertJsonPath('data.values_section.items.2.icon', 'growth')
            ->assertJsonPath('data.team_section.primary_cta_url', 'https://widewebblog.test/careers')
            ->assertJsonPath('data.team_section.members.0.name', 'Marcus Thorne')
            ->assertJsonPath('data.team_section.members.1.name', 'Sarah Jenkins')
            ->assertJsonPath('data.updated_by.id', $admin->id);

        $aboutPage = AboutPage::query()->firstOrFail();

        $this->assertSame([
            ['label' => 'Articles Published', 'value' => '500+'],
            ['label' => 'Monthly Readers', 'value' => '120K'],
            ['label' => 'Global Experts', 'value' => '12'],
        ], $aboutPage->stats_section['items']);
        $this->assertSame('Community Focused', $aboutPage->values_section['items'][0]['title']);
        $this->assertSame('Marcus Thorne', $aboutPage->team_section['members'][0]['name']);
        $this->assertSame($admin->id, $aboutPage->updated_by_user_id);
    }

    public function test_about_page_update_validates_nested_payloads(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/admin/about-page', [
                'hero' => [
                    'title' => str_repeat('x', 256),
                    'media_url' => 'bad-url',
                ],
                'mission_section' => [
                    'quote' => str_repeat('q', 1001),
                ],
                'stats_section' => [
                    'items' => [
                        ['label' => '', 'value' => '500+'],
                        ['label' => 'Monthly Readers'],
                    ],
                ],
                'values_section' => [
                    'items' => [
                        ['icon' => str_repeat('i', 81), 'title' => '', 'description' => 'ok'],
                    ],
                ],
                'team_section' => [
                    'primary_cta_url' => 'bad-url',
                    'members' => [
                        ['name' => '', 'role' => 'Lead Developer', 'image_url' => 'bad-url'],
                    ],
                ],
                'seo' => [
                    'meta_description' => str_repeat('d', 321),
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => [
                    'hero.title',
                    'hero.media_url',
                    'mission_section.quote',
                    'stats_section.items.0.label',
                    'stats_section.items.1.value',
                    'values_section.items.0.icon',
                    'values_section.items.0.title',
                    'team_section.primary_cta_url',
                    'team_section.members.0.name',
                    'team_section.members.0.image_url',
                    'seo.meta_description',
                ],
            ]);
    }
}
