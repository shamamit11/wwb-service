<?php

namespace Database\Seeders;

use App\Models\ContactPage;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;

class ContactPageSeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        ContactPage::query()->updateOrCreate(
            ['singleton_key' => ContactPage::SINGLETON_KEY],
            [
                ...ContactPage::defaultPayload(),
                'singleton_key' => ContactPage::SINGLETON_KEY,
                'hero' => [
                    'eyebrow' => 'Start the conversation',
                    'title' => 'Tell us what you are building, writing, or rethinking.',
                    'description' => 'Wide Web Blog is built for thoughtful builders. Use the form to reach out about collaborations, editorial opportunities, sponsorships, or ideas worth exploring.',
                ],
                'contact_form' => [
                    'eyebrow' => 'Contact form',
                    'title' => 'One message, clearly framed.',
                    'description' => 'Keep it concise but useful. Tell us what you need, why it matters, and what a good outcome looks like.',
                    'submit_label' => 'Send Message',
                    'success_message' => 'Your message has been received.',
                ],
                'contact_reasons' => [
                    'items' => [
                        [
                            'title' => 'Editorial partnerships',
                            'description' => 'Share your concept, audience, and what the collaboration should accomplish.',
                        ],
                        [
                            'title' => 'Product or resource ideas',
                            'description' => 'If there is a template, guide, or workflow you want us to cover, describe the gap clearly.',
                        ],
                        [
                            'title' => 'Signal over noise',
                            'description' => 'The more specific your context is, the better the response will be.',
                        ],
                    ],
                ],
                'seo' => [
                    'meta_title' => 'Contact Wide Web Blog',
                    'meta_description' => 'Contact Wide Web Blog about collaborations, editorial ideas, and sponsorship opportunities.',
                ],
                'updated_by_user_id' => $admin?->id,
            ],
        );
    }
}
