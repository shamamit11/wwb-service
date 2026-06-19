<?php

namespace Tests\Feature;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignRecipient;
use App\Models\NewsletterList;
use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use App\Modules\Newsletter\Enums\NewsletterListStatus;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use App\Modules\Newsletter\Enums\NewsletterSubscriberStatus;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NewsletterFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsletter_tables_match_the_foundation_design(): void
    {
        $this->assertTrue(Schema::hasTable('newsletter_subscribers'));
        $this->assertTrue(Schema::hasColumns('newsletter_subscribers', [
            'id',
            'email',
            'name',
            'status',
            'source',
            'subscribed_at',
            'unsubscribed_at',
            'unsubscribe_token',
            'metadata',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('newsletter_lists'));
        $this->assertTrue(Schema::hasColumns('newsletter_lists', [
            'id',
            'name',
            'slug',
            'description',
            'status',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('newsletter_list_subscriber'));
        $this->assertTrue(Schema::hasColumns('newsletter_list_subscriber', [
            'id',
            'newsletter_list_id',
            'newsletter_subscriber_id',
            'subscribed_at',
            'unsubscribed_at',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('newsletter_campaigns'));
        $this->assertTrue(Schema::hasColumns('newsletter_campaigns', [
            'id',
            'title',
            'subject',
            'preview_text',
            'content_markdown',
            'content_html',
            'status',
            'scheduled_at',
            'sent_at',
            'created_by',
            'metadata',
            'created_at',
            'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('newsletter_campaign_recipients'));
        $this->assertTrue(Schema::hasColumns('newsletter_campaign_recipients', [
            'id',
            'newsletter_campaign_id',
            'newsletter_subscriber_id',
            'email',
            'status',
            'sent_at',
            'failed_at',
            'error_message',
            'opened_at',
            'clicked_at',
            'unsubscribed_at',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_newsletter_models_expose_expected_relationships(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'reader@example.com',
            'name' => 'Reader',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-reader-1',
            'subscribed_at' => now(),
        ]);

        $list = NewsletterList::query()->create([
            'name' => 'Product Updates',
            'slug' => 'product-updates',
            'status' => NewsletterListStatus::Active,
        ]);

        $list->subscribers()->attach($subscriber->id, [
            'subscribed_at' => now(),
        ]);

        $campaign = NewsletterCampaign::query()->create([
            'title' => 'June Update',
            'subject' => 'What shipped this month',
            'status' => NewsletterCampaignStatus::Draft,
        ]);

        $recipient = NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $campaign->id,
            'newsletter_subscriber_id' => $subscriber->id,
            'email' => $subscriber->email,
            'status' => NewsletterRecipientStatus::Pending,
        ]);

        $this->assertSame($list->id, $subscriber->lists()->firstOrFail()->id);
        $this->assertSame($subscriber->id, $list->subscribers()->firstOrFail()->id);
        $this->assertSame($recipient->id, $campaign->recipients()->firstOrFail()->id);
        $this->assertSame($subscriber->id, $recipient->subscriber()->firstOrFail()->id);
        $this->assertSame($campaign->id, $recipient->campaign()->firstOrFail()->id);
    }

    public function test_newsletter_enum_values_match_the_mvp_contract(): void
    {
        $this->assertSame(
            ['active', 'unsubscribed', 'bounced', 'complained'],
            array_map(static fn (NewsletterSubscriberStatus $status): string => $status->value, NewsletterSubscriberStatus::cases()),
        );

        $this->assertSame(
            ['draft', 'scheduled', 'sending', 'sent', 'cancelled', 'failed'],
            array_map(static fn (NewsletterCampaignStatus $status): string => $status->value, NewsletterCampaignStatus::cases()),
        );

        $this->assertSame(
            ['pending', 'sent', 'failed', 'skipped', 'unsubscribed'],
            array_map(static fn (NewsletterRecipientStatus $status): string => $status->value, NewsletterRecipientStatus::cases()),
        );

        $this->assertSame(
            ['active', 'inactive'],
            array_map(static fn (NewsletterListStatus $status): string => $status->value, NewsletterListStatus::cases()),
        );
    }

    public function test_subscriber_email_must_be_unique(): void
    {
        NewsletterSubscriber::query()->create([
            'email' => 'duplicate@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-duplicate-1',
        ]);

        $this->expectException(QueryException::class);

        NewsletterSubscriber::query()->create([
            'email' => 'duplicate@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-duplicate-2',
        ]);
    }

    public function test_unsubscribe_token_must_be_unique(): void
    {
        NewsletterSubscriber::query()->create([
            'email' => 'first@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-shared',
        ]);

        $this->expectException(QueryException::class);

        NewsletterSubscriber::query()->create([
            'email' => 'second@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-shared',
        ]);
    }

    public function test_campaign_recipient_pair_must_be_unique(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'recipient@example.com',
            'status' => NewsletterSubscriberStatus::Active,
            'unsubscribe_token' => 'token-recipient-1',
        ]);

        $campaign = NewsletterCampaign::query()->create([
            'title' => 'Weekly Roundup',
            'subject' => 'This week on Wide Web Blog',
            'status' => NewsletterCampaignStatus::Draft,
        ]);

        NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $campaign->id,
            'newsletter_subscriber_id' => $subscriber->id,
            'email' => $subscriber->email,
            'status' => NewsletterRecipientStatus::Pending,
        ]);

        $this->expectException(QueryException::class);

        NewsletterCampaignRecipient::query()->create([
            'newsletter_campaign_id' => $campaign->id,
            'newsletter_subscriber_id' => $subscriber->id,
            'email' => $subscriber->email,
            'status' => NewsletterRecipientStatus::Pending,
        ]);
    }

    public function test_newsletter_subscribers_table_has_no_verification_fields(): void
    {
        $this->assertFalse(Schema::hasColumn('newsletter_subscribers', 'verification_token'));
        $this->assertFalse(Schema::hasColumn('newsletter_subscribers', 'verified_at'));
        $this->assertFalse(Schema::hasColumn('newsletter_subscribers', 'verification_sent_at'));
    }

    public function test_newsletter_subscriber_status_has_no_pending_state(): void
    {
        $values = array_map(
            static fn (NewsletterSubscriberStatus $status): string => $status->value,
            NewsletterSubscriberStatus::cases(),
        );

        $this->assertNotContains('pending', $values);
    }
}
