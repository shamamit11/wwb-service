<?php

namespace Tests\Feature;

use App\Jobs\Newsletter\DeliverNewsletterCampaignRecipientJob;
use App\Jobs\Newsletter\SendNewsletterCampaignJob;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignRecipient;
use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use App\Modules\Newsletter\Enums\NewsletterListStatus;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Modules\Newsletter\Mail\NewsletterCampaignMail;
use App\Modules\Newsletter\Services\NewsletterTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class NewsletterRuntimeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_subscribe_and_unsubscribe_without_verification(): void
    {
        $list = NewsletterList::query()->create([
            'name' => 'Weekly Roundup',
            'slug' => 'weekly-roundup',
            'status' => NewsletterListStatus::Active,
        ]);

        $this->postJson('/api/v1/public/newsletter/subscribe', [
            'email' => 'reader@example.com',
            'name' => 'Reader',
            'list_ids' => [$list->id],
        ])->assertCreated()
            ->assertJsonPath('data.email', 'reader@example.com')
            ->assertJsonPath('data.status', NewsletterSubscriberStatus::Active->value)
            ->assertJsonPath('data.lists.0.id', $list->id);

        $subscriber = NewsletterSubscriber::query()->where('email', 'reader@example.com')->firstOrFail();

        $this->getJson('/api/v1/public/newsletter/unsubscribe?token='.$subscriber->unsubscribe_token)
            ->assertOk()
            ->assertJsonPath('data.status', NewsletterSubscriberStatus::Unsubscribed->value);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'status' => NewsletterSubscriberStatus::Unsubscribed->value,
        ]);
        $this->assertDatabaseHas('newsletter_list_subscriber', [
            'newsletter_subscriber_id' => $subscriber->id,
            'newsletter_list_id' => $list->id,
        ]);
    }

    public function test_admin_send_endpoint_queues_campaign_job(): void
    {
        Queue::fake();

        $token = $this->adminToken();
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => str_repeat('a', 64),
            'subscribed_at' => now(),
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'title' => 'June Update',
            'subject' => 'June Update',
            'content_markdown' => 'Hello world',
            'status' => NewsletterCampaignStatus::Draft,
        ]);
        NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $campaign->id,
            'newsletter_subscriber_id' => $subscriber->id,
            'email' => $subscriber->email,
            'status' => NewsletterRecipientStatus::Pending,
        ]);

        $this->withToken($token)->postJson("/api/v1/admin/newsletter/campaigns/{$campaign->id}/send")
            ->assertOk()
            ->assertJsonPath('data.status', NewsletterCampaignStatus::Sending->value);

        Queue::assertPushed(SendNewsletterCampaignJob::class);
    }

    public function test_delivery_jobs_send_mail_and_update_recipient_and_campaign_statuses(): void
    {
        Mail::fake();

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => str_repeat('b', 64),
            'subscribed_at' => now(),
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'title' => 'Launch',
            'subject' => 'Launch',
            'content_html' => '<p><a href="https://example.com/post">Read more</a></p>',
            'status' => NewsletterCampaignStatus::Sending,
        ]);
        $recipient = NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $campaign->id,
            'newsletter_subscriber_id' => $subscriber->id,
            'email' => $subscriber->email,
            'status' => NewsletterRecipientStatus::Pending,
        ]);

        app(\App\Modules\Newsletter\Services\SendNewsletterCampaignService::class)->handle((int) $campaign->id);

        DeliverNewsletterCampaignRecipientJob::dispatchSync((int) $recipient->id);

        Mail::assertSent(NewsletterCampaignMail::class, 1);
        $this->assertDatabaseHas('newsletter_campaign_recipients', [
            'id' => $recipient->id,
            'status' => NewsletterRecipientStatus::Sent->value,
        ]);
        $this->assertDatabaseHas('newsletter_campaigns', [
            'id' => $campaign->id,
            'status' => NewsletterCampaignStatus::Sent->value,
        ]);
    }

    public function test_open_and_click_tracking_update_recipient_metrics(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => str_repeat('c', 64),
            'subscribed_at' => now(),
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'title' => 'Tracked',
            'subject' => 'Tracked',
            'content_html' => '<p>Tracked</p>',
            'status' => NewsletterCampaignStatus::Sent,
        ]);
        $recipient = NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $campaign->id,
            'newsletter_subscriber_id' => $subscriber->id,
            'email' => $subscriber->email,
            'status' => NewsletterRecipientStatus::Sent,
            'sent_at' => now(),
        ]);

        $tracking = app(NewsletterTrackingService::class);
        $this->get($tracking->openUrl($recipient))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/gif');

        $this->get($tracking->clickUrl($recipient, 'https://example.com/destination'))
            ->assertRedirect('https://example.com/destination');

        $recipient->refresh();

        $this->assertNotNull($recipient->opened_at);
        $this->assertNotNull($recipient->clicked_at);
    }

    public function test_webhook_events_can_mark_bounces_and_complaints(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => str_repeat('d', 64),
            'subscribed_at' => now(),
        ]);
        $campaign = NewsletterCampaign::query()->create([
            'title' => 'Webhook',
            'subject' => 'Webhook',
            'content_markdown' => 'Webhook body',
            'status' => NewsletterCampaignStatus::Sent,
        ]);
        $recipient = NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $campaign->id,
            'newsletter_subscriber_id' => $subscriber->id,
            'email' => $subscriber->email,
            'status' => NewsletterRecipientStatus::Sent,
            'sent_at' => now(),
        ]);

        $this->postJson('/api/v1/public/newsletter/webhooks/events', [
            'event_type' => 'bounced',
            'recipient_id' => $recipient->id,
        ])->assertOk()
            ->assertJsonPath('data.processed', true);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'status' => NewsletterSubscriberStatus::Bounced->value,
        ]);

        $this->postJson('/api/v1/public/newsletter/webhooks/events', [
            'event_type' => 'complained',
            'recipient_id' => $recipient->id,
        ])->assertOk();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'status' => NewsletterSubscriberStatus::Complained->value,
        ]);
    }

    private function adminToken(): string
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $admin->createToken('test-suite', ['admin:access'])->plainTextToken;
    }
}
