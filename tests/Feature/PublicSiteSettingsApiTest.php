<?php

namespace Tests\Feature;

use App\Models\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_site_settings_returns_structured_footer_payload(): void
    {
        SiteSettings::query()->create([
            'singleton_key' => SiteSettings::SINGLETON_KEY,
            'footer' => [
                'brand_name' => 'Wide Web Blog',
                'description' => 'An authoritative digital editorial focused on technical SEO, AI implementation, and content architecture for the modern web.',
                'social_links' => [
                    [
                        'label' => 'Share',
                        'url' => 'https://widewebblog.test/share',
                        'icon' => 'share',
                    ],
                    [
                        'label' => 'Globe',
                        'url' => 'https://widewebblog.test',
                        'icon' => 'globe',
                    ],
                ],
                'legal_links' => [
                    [
                        'label' => 'Privacy Policy',
                        'slug' => 'privacy-policy',
                        'url' => null,
                    ],
                ],
            ],
            'updated_by_user_id' => null,
        ]);

        $this->getJson('/api/v1/public/site-settings')
            ->assertOk()
            ->assertJsonPath('data.footer.brand_name', 'Wide Web Blog')
            ->assertJsonPath('data.footer.social_links.1.icon', 'globe')
            ->assertJsonPath('data.footer.legal_links.0.slug', 'privacy-policy')
            ->assertJsonMissingPath('data.updated_by')
            ->assertJsonMissingPath('data.updated_at');
    }

    public function test_public_site_settings_bootstraps_default_footer_shape(): void
    {
        $this->getJson('/api/v1/public/site-settings')
            ->assertOk()
            ->assertJsonPath('data.footer.brand_name', null)
            ->assertJsonPath('data.footer.description', null)
            ->assertJsonPath('data.footer.social_links', [])
            ->assertJsonPath('data.footer.legal_links', []);

        $this->assertDatabaseHas('site_settings', [
            'singleton_key' => SiteSettings::SINGLETON_KEY,
        ]);
    }
}
