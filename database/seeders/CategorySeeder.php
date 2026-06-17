<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Concerns\SeederSupport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    use SeederSupport;

    public function run(): void
    {
        $admin = $this->adminUser();

        if ($admin === null) {
            $this->warn('Skipping category seeding because no user record exists.');

            return;
        }

        foreach ($this->records() as $sortOrder => $name) {
            Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'parent_id' => null,
                    'created_by_user_id' => $admin->id,
                    'updated_by_user_id' => $admin->id,
                    'name' => $name,
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $sortOrder + 1,
                ],
            );
        }
    }

    /**
     * @return list<string>
     */
    private function records(): array
    {
        return [
            'AI Tools',
            'AI Agents',
            'SEO',
            'Content Marketing',
            'Productivity & Automation',
            'Developer AI',
            'News & Trends',
        ];
    }
}
