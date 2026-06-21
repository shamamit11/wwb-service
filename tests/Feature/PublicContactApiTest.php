<?php

namespace Tests\Feature;

use App\Models\ContactPage;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_returns_structured_contact_page_payload(): void
    {
        ContactPage::query()->create([
            'singleton_key' => ContactPage::SINGLETON_KEY,
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
                ],
            ],
            'seo' => [
                'meta_title' => 'Contact Wide Web Blog',
                'meta_description' => 'Contact us about collaborations.',
            ],
            'updated_by_user_id' => null,
        ]);

        $this->getJson('/api/v1/public/contact')
            ->assertOk()
            ->assertJsonPath('data.hero.title', 'Tell us what you are building.')
            ->assertJsonPath('data.contact_form.submit_label', 'Send Message')
            ->assertJsonPath('data.contact_reasons.items.0.title', 'Editorial partnerships')
            ->assertJsonPath('data.seo.meta_title', 'Contact Wide Web Blog');
    }

    public function test_public_can_submit_contact_message(): void
    {
        $this->postJson('/api/v1/public/contact/submit', [
            'name' => 'Alex Rivera',
            'email' => 'alex@example.com',
            'topic' => 'Partnership, editorial idea, sponsorship',
            'message' => 'We want to collaborate on an editorial campaign.',
        ])->assertCreated()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.message', 'Your message has been received.');

        $this->assertDatabaseHas('contact_submissions', [
            'email' => 'alex@example.com',
            'status' => ContactSubmission::STATUS_NEW,
        ]);
    }

    public function test_public_contact_submission_validation_uses_consistent_shape(): void
    {
        $this->postJson('/api/v1/public/contact/submit', [
            'name' => '',
            'email' => 'not-an-email',
            'topic' => '',
            'message' => '',
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['name', 'email', 'topic', 'message'],
            ]);
    }
}
