<?php

namespace Tests\Feature;

use App\Models\ContactPage;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_admin_routes_require_admin_access(): void
    {
        $this->getJson('/api/v1/admin/contact-page')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');

        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/admin/contact-page')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'FORBIDDEN');
    }

    public function test_admin_can_fetch_bootstrapped_contact_page_defaults(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->getJson('/api/v1/admin/contact-page')
            ->assertOk()
            ->assertJsonPath('data.hero.title', null)
            ->assertJsonPath('data.contact_form.submit_label', null)
            ->assertJsonPath('data.contact_reasons.items', [])
            ->assertJsonPath('data.seo.meta_title', null)
            ->assertJsonPath('data.updated_by', null);

        $this->assertDatabaseHas('contact_pages', [
            'singleton_key' => ContactPage::SINGLETON_KEY,
        ]);
    }

    public function test_admin_can_update_contact_page_and_manage_submissions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('test-suite', ['admin:access'])->plainTextToken;

        $this->withToken($token)
            ->putJson('/api/v1/admin/contact-page', [
                'hero' => [
                    'eyebrow' => 'Start the conversation',
                    'title' => 'Tell us what you are building.',
                    'description' => 'Reach out about collaborations and editorial opportunities.',
                ],
                'contact_form' => [
                    'eyebrow' => 'Contact form',
                    'title' => 'One message, clearly framed.',
                    'description' => 'Keep it concise but useful.',
                    'submit_label' => 'Send Message',
                    'success_message' => 'We received your message.',
                ],
                'contact_reasons' => [
                    'items' => [
                        ['title' => 'Editorial partnerships', 'description' => 'Share your concept.'],
                        ['title' => 'Product ideas', 'description' => 'Describe the gap clearly.'],
                    ],
                ],
                'seo' => [
                    'meta_title' => 'Contact Wide Web Blog',
                    'meta_description' => 'Contact us about collaborations.',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.hero.title', 'Tell us what you are building.')
            ->assertJsonPath('data.contact_form.submit_label', 'Send Message')
            ->assertJsonPath('data.contact_reasons.items.1.title', 'Product ideas')
            ->assertJsonPath('data.updated_by.id', $admin->id);

        $submission = ContactSubmission::query()->create([
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
            'topic' => 'Partnership',
            'message' => 'Let us discuss a collaboration.',
            'status' => ContactSubmission::STATUS_NEW,
            'admin_notes' => null,
            'metadata' => ['source' => 'test'],
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by_user_id' => null,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/contact-submissions')
            ->assertOk()
            ->assertJsonPath('data.0.id', $submission->id);

        $this->withToken($token)
            ->getJson("/api/v1/admin/contact-submissions/{$submission->id}")
            ->assertOk()
            ->assertJsonPath('data.email', 'alex@example.com');

        $this->withToken($token)
            ->patchJson("/api/v1/admin/contact-submissions/{$submission->id}", [
                'status' => ContactSubmission::STATUS_READ,
                'admin_notes' => 'Reply this week.',
                'metadata' => ['priority' => 'high'],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', ContactSubmission::STATUS_READ)
            ->assertJsonPath('data.admin_notes', 'Reply this week.')
            ->assertJsonPath('data.reviewed_by.id', $admin->id);
    }

    public function test_contact_page_validation_errors_use_consistent_json_shape(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)
            ->putJson('/api/v1/admin/contact-page', [
                'hero' => [
                    'title' => str_repeat('x', 256),
                ],
                'contact_form' => [
                    'submit_label' => str_repeat('s', 121),
                ],
                'contact_reasons' => [
                    'items' => [
                        ['title' => '', 'description' => 'ok'],
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
                    'contact_form.submit_label',
                    'contact_reasons.items.0.title',
                    'seo.meta_description',
                ],
            ]);
    }

    private function adminToken(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
    }
}
