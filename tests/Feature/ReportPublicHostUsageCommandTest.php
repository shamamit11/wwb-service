<?php

namespace Tests\Feature;

use App\Models\AboutPage;
use App\Models\Homepage;
use App\Models\SeoMetadata;
use App\Models\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPublicHostUsageCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_reports_matching_stored_fields_across_supported_models(): void
    {
        SeoMetadata::query()->create([
            'seoable_type' => 'post',
            'seoable_id' => 123,
            'canonical_url' => 'https://service.widewebblog.com/example-post',
            'robots_index' => true,
            'robots_follow' => true,
        ]);

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

        AboutPage::query()->create([
            ...AboutPage::defaultPayload(),
            'singleton_key' => AboutPage::SINGLETON_KEY,
            'hero' => [
                'media_url' => 'https://service.widewebblog.com/media/about/hero.png',
            ],
            'team_section' => [
                'primary_cta_url' => 'https://service.widewebblog.com/careers',
                'members' => [[
                    'name' => 'Sarah Jenkins',
                    'role' => 'Head of AI Research',
                    'image_url' => 'https://service.widewebblog.com/media/about/sarah.png',
                    'image_alt' => 'Sarah portrait',
                ]],
            ],
        ]);

        SiteSettings::query()->create([
            ...SiteSettings::defaultPayload(),
            'singleton_key' => SiteSettings::SINGLETON_KEY,
            'footer' => [
                'brand_name' => 'Wide Web Blog',
                'description' => 'Footer copy',
                'social_links' => [[
                    'label' => 'Share',
                    'url' => 'https://service.widewebblog.com/share',
                    'icon' => 'share',
                ]],
                'legal_links' => [[
                    'label' => 'Terms',
                    'slug' => null,
                    'url' => 'https://service.widewebblog.com/terms',
                ]],
            ],
        ]);

        $this->artisan('content:report-public-host-usage', [
            'from' => 'https://service.widewebblog.com',
        ])
            ->expectsOutputToContain('seo_metadata')
            ->expectsOutputToContain('homepages.default.hero.primary_cta_url')
            ->expectsOutputToContain('homepages.default.hero.media_url')
            ->expectsOutputToContain('about_pages.default.team_section.members[0].image_url')
            ->expectsOutputToContain('site_settings.default.footer.legal_links[0].url')
            ->expectsOutputToContain('Total matches: 9')
            ->assertSuccessful();
    }

    public function test_command_reports_when_no_matching_fields_are_found(): void
    {
        $this->artisan('content:report-public-host-usage', [
            'from' => 'https://service.widewebblog.com',
        ])
            ->expectsOutputToContain('No stored DB fields currently point at [https://service.widewebblog.com].')
            ->assertSuccessful();
    }
}
