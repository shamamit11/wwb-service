<?php

namespace Database\Seeders;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignRecipient;
use App\Models\NewsletterSubscriber;
use App\Modules\Newsletter\Enums\NewsletterCampaignStatus;
use App\Modules\Newsletter\Enums\NewsletterRecipientStatus;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class NewsletterCampaignSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        foreach ($this->records($admin?->id) as $record) {
            $recipients = $record['recipients'];
            unset($record['recipients']);

            $campaign = NewsletterCampaign::query()->updateOrCreate(
                ['title' => $record['title']],
                $record,
            );

            foreach ($recipients as $recipient) {
                NewsletterCampaignRecipient::query()->updateOrCreate(
                    [
                        'newsletter_campaign_id' => $campaign->id,
                        'newsletter_subscriber_id' => $recipient['newsletter_subscriber_id'],
                    ],
                    [
                        'email' => $recipient['email'],
                        'status' => $recipient['status'],
                        'sent_at' => $recipient['sent_at'],
                        'failed_at' => $recipient['failed_at'],
                        'error_message' => $recipient['error_message'],
                        'opened_at' => $recipient['opened_at'],
                        'clicked_at' => $recipient['clicked_at'],
                        'unsubscribed_at' => $recipient['unsubscribed_at'],
                    ],
                );
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(?int $adminUserId): array
    {
        $subscriberOne = NewsletterSubscriber::query()->where('email', 'reader.one@example.com')->first();
        $subscriberTwo = NewsletterSubscriber::query()->where('email', 'reader.two@example.com')->first();

        return [
            [
                'title' => 'AI Publishing Workflow Notes - June',
                'subject' => 'AI publishing workflow notes for June',
                'preview_text' => 'Draft-ready notes on editorial QA, AI jobs, and workflow improvements.',
                'content_markdown' => "# AI Publishing Workflow Notes\n\nThis issue covers editorial QA, prompt workflows, and review-only refinement tools.",
                'content_html' => '<h1>AI Publishing Workflow Notes</h1><p>This issue covers editorial QA, prompt workflows, and review-only refinement tools.</p>',
                'status' => NewsletterCampaignStatus::Sent,
                'scheduled_at' => now()->subDays(4),
                'sent_at' => now()->subDays(4),
                'created_by' => $adminUserId,
                'metadata' => ['seeded' => true, 'list_slug' => 'weekly-editorial-systems'],
                'recipients' => array_values(array_filter([
                    $subscriberOne ? [
                        'newsletter_subscriber_id' => $subscriberOne->id,
                        'email' => $subscriberOne->email,
                        'status' => NewsletterRecipientStatus::Sent,
                        'sent_at' => now()->subDays(4),
                        'failed_at' => null,
                        'error_message' => null,
                        'opened_at' => now()->subDays(3),
                        'clicked_at' => now()->subDays(3),
                        'unsubscribed_at' => null,
                    ] : null,
                    $subscriberTwo ? [
                        'newsletter_subscriber_id' => $subscriberTwo->id,
                        'email' => $subscriberTwo->email,
                        'status' => NewsletterRecipientStatus::Sent,
                        'sent_at' => now()->subDays(4),
                        'failed_at' => null,
                        'error_message' => null,
                        'opened_at' => null,
                        'clicked_at' => null,
                        'unsubscribed_at' => null,
                    ] : null,
                ])),
            ],
            [
                'title' => 'Platform Product Updates',
                'subject' => 'Product updates for editors and subscribers',
                'preview_text' => 'Newsletter delivery foundation and AI workflow improvements.',
                'content_markdown' => "# Product Updates\n\nUpcoming improvements include richer admin tools and newsletter automation.",
                'content_html' => null,
                'status' => NewsletterCampaignStatus::Draft,
                'scheduled_at' => null,
                'sent_at' => null,
                'created_by' => $adminUserId,
                'metadata' => ['seeded' => true, 'list_slug' => 'product-updates'],
                'recipients' => [],
            ],
        ];
    }
}
