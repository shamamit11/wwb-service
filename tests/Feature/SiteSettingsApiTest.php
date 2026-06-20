<?php

namespace Tests\Feature;

use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_settings_admin_routes_require_admin_access(): void
    {
        $this->getJson('/api/v1/admin/site-settings')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');

        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/site-settings')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN');
    }

    public function test_admin_can_fetch_bootstrapped_site_settings_defaults(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->getJson('/api/v1/admin/site-settings')
            ->assertOk()
            ->assertJsonPath('data.footer.brand_name', null)
            ->assertJsonPath('data.footer.description', null)
            ->assertJsonPath('data.footer.social_links', [])
            ->assertJsonPath('data.footer.legal_links', [])
            ->assertJsonPath('data.updated_by', null);

        $this->assertDatabaseHas('site_settings', [
            'singleton_key' => SiteSettings::SINGLETON_KEY,
        ]);
    }

    public function test_admin_can_update_site_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/admin/site-settings', [
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
                            'label' => 'Email',
                            'url' => 'mailto:hello@widewebblog.test',
                            'icon' => 'email',
                        ],
                    ],
                    'legal_links' => [
                        [
                            'label' => 'Privacy Policy',
                            'slug' => 'privacy-policy',
                            'url' => null,
                        ],
                        [
                            'label' => 'Terms',
                            'slug' => null,
                            'url' => 'https://widewebblog.test/terms',
                        ],
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.footer.brand_name', 'Wide Web Blog')
            ->assertJsonPath('data.footer.social_links.0.icon', 'share')
            ->assertJsonPath('data.footer.legal_links.1.url', 'https://widewebblog.test/terms')
            ->assertJsonPath('data.updated_by.id', $admin->id);

        $siteSettings = SiteSettings::query()->firstOrFail();

        $this->assertSame('Wide Web Blog', $siteSettings->footer['brand_name']);
        $this->assertSame('share', $siteSettings->footer['social_links'][0]['icon']);
        $this->assertSame('privacy-policy', $siteSettings->footer['legal_links'][0]['slug']);
        $this->assertSame($admin->id, $siteSettings->updated_by_user_id);
    }

    public function test_site_settings_validation_errors_use_consistent_json_shape(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->putJson('/api/v1/admin/site-settings', [
                'footer' => [
                    'brand_name' => str_repeat('x', 121),
                    'social_links' => [
                        [
                            'label' => '',
                            'url' => 42,
                        ],
                    ],
                    'legal_links' => [
                        [
                            'label' => '',
                            'url' => 42,
                        ],
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => [
                    'footer.brand_name',
                    'footer.social_links.0.label',
                    'footer.social_links.0.url',
                    'footer.legal_links.0.label',
                    'footer.legal_links.0.url',
                ],
            ]);
    }

    private function adminToken(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
    }
}
