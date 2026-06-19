<?php

namespace Database\Seeders;

use App\Models\NewsletterList;
use App\Modules\Newsletter\Enums\NewsletterListStatus;
use Illuminate\Database\Seeder;

class NewsletterListSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->records() as $attributes) {
            NewsletterList::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                $attributes,
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(): array
    {
        return [
            [
                'name' => 'Weekly Editorial Systems',
                'slug' => 'weekly-editorial-systems',
                'description' => 'AI publishing workflows, editorial process notes, and new practical guides.',
                'status' => NewsletterListStatus::Active,
            ],
            [
                'name' => 'Product Updates',
                'slug' => 'product-updates',
                'description' => 'Feature updates and backend workflow changes for Wide Web Blog.',
                'status' => NewsletterListStatus::Active,
            ],
        ];
    }
}
