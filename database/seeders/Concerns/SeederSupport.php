<?php

namespace Database\Seeders\Concerns;

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;

trait SeederSupport
{
    protected function adminUser(): ?User
    {
        return User::query()
            ->where('email', AdminUserSeeder::EMAIL)
            ->first()
            ?? User::query()->where('is_admin', true)->first()
            ?? User::query()->first();
    }

    protected function firstCategory(): ?Category
    {
        return Category::query()->orderBy('id')->first();
    }

    /**
     * @return list<int>
     */
    protected function firstTagIds(int $limit = 2): array
    {
        return Tag::query()
            ->orderBy('id')
            ->limit($limit)
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    protected function warn(string $message): void
    {
        $this->command?->warn($message);
    }
}
