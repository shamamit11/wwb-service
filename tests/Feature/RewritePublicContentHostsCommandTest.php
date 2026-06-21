<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\Homepage;
use App\Models\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RewritePublicContentHostsCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.frontend_url', 'https://www.widewebblog.com');
        config()->set('filesystems.disks.r2.url', 'https://media.widewebblog.com');
    }

    public function test_command_can_preview_matching_public_content_urls_without_updating(): void
    {
        Homepage::query()->create([
            ...Homepage::defaultPayload(),
            'singleton_key' => Homepage::SINGLETON_KEY,
            'hero' => [
                'primary_cta_url' => 'https://service.widewebblog.com/featured',
                'secondary_cta_url' => null,
                'media_url' => 'https://service.widewebblog.com/media/home/hero.png',
            ],
            'promo_section' => [
                'primary_cta_url' => 'https://service.widewebblog.com/kit',
            ],
        ]);

        $this->artisan('content:rewrite-public-hosts', [
            'from' => 'https://service.widewebblog.com',
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('homepages', [
            'singleton_key' => Homepage::SINGLETON_KEY,
        ]);

        $homepage = Homepage::query()->firstOrFail();

        $this->assertSame('https://service.widewebblog.com/featured', $homepage->hero['primary_cta_url']);
        $this->assertSame('https://service.widewebblog.com/media/home/hero.png', $homepage->hero['media_url']);
        $this->assertSame('https://service.widewebblog.com/kit', $homepage->promo_section['primary_cta_url']);
    }

    public function test_command_rewrites_site_and_media_urls_in_public_content_models(): void
    {
        Homepage::query()->create([
            ...Homepage::defaultPayload(),
            'singleton_key' => Homepage::SINGLETON_KEY,
            'hero' => [
                'primary_cta_url' => 'https://service.widewebblog.com/featured',
                'secondary_cta_url' => 'https://service.widewebblog.com/resources',
                'media_url' => 'https://service.widewebblog.com/media/home/hero.png',
            ],
            'promo_section' => [
                'primary_cta_url' => 'https://service.widewebblog.com/kit',
            ],
        ]);

        AboutPage::query()->create([
            ...AboutPage::defaultPayload(),
            'singleton_key' => AboutPage::SINGLETON_KEY,
            'hero' => [
                'media_url' => 'https://service.widewebblog.com/media/about/hero.png',
            ],
            'team_section' => [
                'primary_cta_url' => 'https://service.widewebblog.com/careers',
                'members' => [
                    [
                        'name' => 'Sarah Jenkins',
                        'role' => 'Head of AI Research',
                        'image_url' => 'https://service.widewebblog.com/media/about/sarah.png',
                        'image_alt' => 'Sarah portrait',
                    ],
                ],
            ],
        ]);

        SiteSettings::query()->create([
            ...SiteSettings::defaultPayload(),
            'singleton_key' => SiteSettings::SINGLETON_KEY,
            'footer' => [
                'brand_name' => 'Wide Web Blog',
                'description' => 'Footer copy',
                'social_links' => [
                    [
                        'label' => 'Share',
                        'url' => 'https://service.widewebblog.com/share',
                        'icon' => 'share',
                    ],
                ],
                'legal_links' => [
                    [
                        'label' => 'Terms',
                        'slug' => null,
                        'url' => 'https://service.widewebblog.com/terms',
                    ],
                ],
            ],
        ]);

        $this->artisan('content:rewrite-public-hosts', [
            'from' => 'https://service.widewebblog.com',
        ])->assertSuccessful();

        $homepage = Homepage::query()->firstOrFail();
        $about = AboutPage::query()->firstOrFail();
        $settings = SiteSettings::query()->firstOrFail();

        $this->assertSame('https://www.widewebblog.com/featured', $homepage->hero['primary_cta_url']);
        $this->assertSame('https://www.widewebblog.com/resources', $homepage->hero['secondary_cta_url']);
        $this->assertSame('https://media.widewebblog.com/media/home/hero.png', $homepage->hero['media_url']);
        $this->assertSame('https://www.widewebblog.com/kit', $homepage->promo_section['primary_cta_url']);

        $this->assertSame('https://media.widewebblog.com/media/about/hero.png', $about->hero['media_url']);
        $this->assertSame('https://www.widewebblog.com/careers', $about->team_section['primary_cta_url']);
        $this->assertSame(
            'https://media.widewebblog.com/media/about/sarah.png',
            $about->team_section['members'][0]['image_url'],
        );

        $this->assertSame('https://www.widewebblog.com/share', $settings->footer['social_links'][0]['url']);
        $this->assertSame('https://www.widewebblog.com/terms', $settings->footer['legal_links'][0]['url']);
    }
}
