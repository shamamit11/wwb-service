<?php

namespace Tests\Feature;

use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use App\Modules\Newsletter\Enums\NewsletterListStatus;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_newsletter_routes_require_authentication(): void
    {
        $this->getJson('/api/v1/admin/newsletter/lists')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'UNAUTHORIZED');
    }

    public function test_admin_can_crud_newsletter_lists(): void
    {
        $token = $this->adminToken();

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/newsletter/lists', [
            'name' => 'Product Updates',
            'description' => 'Release and roadmap updates.',
            'status' => NewsletterListStatus::Active->value,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', 'Product Updates')
            ->assertJsonPath('data.slug', 'product-updates')
            ->assertJsonPath('data.status', NewsletterListStatus::Active->value);

        $listId = (int) $createResponse->json('data.id');

        $this->withToken($token)->getJson('/api/v1/admin/newsletter/lists')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $listId);

        $this->withToken($token)->patchJson("/api/v1/admin/newsletter/lists/{$listId}", [
            'name' => 'Editorial Updates',
            'slug' => 'editorial-updates',
            'description' => 'Updated description.',
            'status' => NewsletterListStatus::Inactive->value,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Editorial Updates')
            ->assertJsonPath('data.slug', 'editorial-updates')
            ->assertJsonPath('data.status', NewsletterListStatus::Inactive->value);

        $this->withToken($token)->deleteJson("/api/v1/admin/newsletter/lists/{$listId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('newsletter_lists', [
            'id' => $listId,
        ]);
    }

    public function test_admin_can_manage_newsletter_subscribers(): void
    {
        $token = $this->adminToken();
        $list = NewsletterList::query()->create([
            'name' => 'Weekly Roundup',
            'slug' => 'weekly-roundup',
            'status' => NewsletterListStatus::Active,
        ]);

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/newsletter/subscribers', [
            'email' => 'reader@example.com',
            'name' => 'Reader',
            'source' => 'admin',
            'metadata' => ['segment' => 'beta'],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.email', 'reader@example.com')
            ->assertJsonPath('data.status', NewsletterSubscriberStatus::Active->value)
            ->assertJsonPath('data.unsubscribe_token', fn (string $value): bool => strlen($value) === 64);

        $subscriberId = (int) $createResponse->json('data.id');
        $subscriber = NewsletterSubscriber::query()->findOrFail($subscriberId);
        $subscriber->lists()->attach($list->id, ['subscribed_at' => now()]);

        $this->withToken($token)->getJson("/api/v1/admin/newsletter/subscribers/{$subscriberId}")
            ->assertOk()
            ->assertJsonPath('data.lists.0.id', $list->id);

        $this->withToken($token)->patchJson("/api/v1/admin/newsletter/subscribers/{$subscriberId}", [
            'email' => 'reader+updated@example.com',
            'name' => 'Reader Updated',
            'status' => NewsletterSubscriberStatus::Bounced->value,
            'source' => 'import',
            'metadata' => ['segment' => 'engaged'],
        ])->assertOk()
            ->assertJsonPath('data.email', 'reader+updated@example.com')
            ->assertJsonPath('data.status', NewsletterSubscriberStatus::Bounced->value);

        $this->withToken($token)->postJson("/api/v1/admin/newsletter/subscribers/{$subscriberId}/unsubscribe")
            ->assertOk()
            ->assertJsonPath('data.status', NewsletterSubscriberStatus::Unsubscribed->value)
            ->assertJsonPath('data.unsubscribed_at', fn (?string $value): bool => $value !== null);

        $this->withToken($token)->postJson("/api/v1/admin/newsletter/subscribers/{$subscriberId}/resubscribe")
            ->assertOk()
            ->assertJsonPath('data.status', NewsletterSubscriberStatus::Active->value)
            ->assertJsonPath('data.unsubscribed_at', null);
    }

    public function test_admin_can_manage_newsletter_campaigns_and_stage_recipients(): void
    {
        $token = $this->adminToken();
        $subscriberA = NewsletterSubscriber::query()->create([
            'email' => 'a@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-a-1234567890123456789012345678901234567890123456789012345',
            'subscribed_at' => now(),
        ]);
        $subscriberB = NewsletterSubscriber::query()->create([
            'email' => 'b@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-b-1234567890123456789012345678901234567890123456789012345',
            'subscribed_at' => now(),
        ]);
        $subscriberC = NewsletterSubscriber::query()->create([
            'email' => 'c@example.com',
            'status' => NewsletterSubscriberStatus::Unsubscribed,
            'unsubscribe_token' => 'token-c-1234567890123456789012345678901234567890123456789012345',
            'subscribed_at' => now(),
            'unsubscribed_at' => now(),
        ]);
        $list = NewsletterList::query()->create([
            'name' => 'Launch List',
            'slug' => 'launch-list',
            'status' => NewsletterListStatus::Active,
        ]);
        $list->subscribers()->attach([$subscriberB->id, $subscriberC->id], ['subscribed_at' => now()]);

        $createResponse = $this->withToken($token)->postJson('/api/v1/admin/newsletter/campaigns', [
            'title' => 'June Launch',
            'subject' => 'June launch update',
            'preview_text' => 'Everything shipping this month',
            'content_markdown' => '# Hello',
            'metadata' => ['source' => 'admin'],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'June Launch')
            ->assertJsonPath('data.status', NewsletterCampaignStatus::Draft->value);

        $campaignId = (int) $createResponse->json('data.id');

        $this->withToken($token)->patchJson("/api/v1/admin/newsletter/campaigns/{$campaignId}", [
            'title' => 'June Launch Updated',
            'subject' => 'Updated subject',
            'preview_text' => 'Updated preview',
            'content_markdown' => '# Updated',
            'content_html' => '<h1>Updated</h1>',
            'status' => NewsletterCampaignStatus::Scheduled->value,
            'scheduled_at' => now()->addDay()->toISOString(),
            'metadata' => ['source' => 'editorial'],
        ])->assertOk()
            ->assertJsonPath('data.title', 'June Launch Updated')
            ->assertJsonPath('data.status', NewsletterCampaignStatus::Scheduled->value);

        $this->withToken($token)->postJson("/api/v1/admin/newsletter/campaigns/{$campaignId}/stage-recipients", [
            'subscriber_ids' => [$subscriberA->id],
            'list_ids' => [$list->id],
        ])->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.status', NewsletterRecipientStatus::Pending->value);

        $this->withToken($token)->postJson("/api/v1/admin/newsletter/campaigns/{$campaignId}/stage-recipients", [
            'subscriber_ids' => [$subscriberA->id],
            'list_ids' => [$list->id],
        ])->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withToken($token)->getJson("/api/v1/admin/newsletter/campaigns/{$campaignId}/recipients")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonMissing(['email' => 'c@example.com']);

        $this->withToken($token)->getJson("/api/v1/admin/newsletter/campaigns/{$campaignId}")
            ->assertOk()
            ->assertJsonPath('data.recipients_count', 2);

        $this->withToken($token)->deleteJson("/api/v1/admin/newsletter/campaigns/{$campaignId}")
            ->assertNoContent();
    }

    public function test_newsletter_validation_errors_use_consistent_json_shape(): void
    {
        $token = $this->adminToken();

        $this->withToken($token)->postJson('/api/v1/admin/newsletter/subscribers', [
            'email' => 'not-an-email',
            'name' => str_repeat('a', 161),
        ])->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure([
                'message',
                'error_code',
                'errors' => ['email', 'name'],
                'meta' => ['request_id'],
            ]);
    }

    private function adminToken(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
    }
}
