<?php

namespace App\Console\Commands;

use App\Models\SeoMetadata;
use Illuminate\Console\Command;

class RewriteSeoCanonicalHostCommand extends Command
{
    protected $signature = 'seo:rewrite-canonical-host
        {from : Existing canonical URL origin to replace, for example https://service.widewebblog.com}
        {--to= : Replacement origin. Defaults to FRONTEND_URL/app.frontend_url}
        {--dry-run : Preview the number of matching records without updating them}';

    protected $description = 'Rewrite stored SEO canonical URL origins to a new public frontend host.';

    public function handle(): int
    {
        $from = $this->normalizeOrigin((string) $this->argument('from'));
        $to = $this->normalizeOrigin((string) ($this->option('to') ?: config('app.frontend_url', config('app.url'))));

        if ($from === null) {
            $this->components->error('The [from] origin must be a valid absolute URL.');

            return self::INVALID;
        }

        if ($to === null) {
            $this->components->error('The target origin is invalid. Set FRONTEND_URL or pass --to=');

            return self::INVALID;
        }

        if ($from === $to) {
            $this->components->error('The source and target origins are the same.');

            return self::INVALID;
        }

        $matches = 0;

        SeoMetadata::query()
            ->whereNotNull('canonical_url')
            ->where('canonical_url', 'like', $from.'%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($from, &$matches): void {
                foreach ($rows as $row) {
                    $current = (string) $row->canonical_url;

                    if (! $this->matchesOrigin($current, $from)) {
                        continue;
                    }

                    $matches++;
                }
            });

        if ($matches === 0) {
            $this->components->info('No stored canonical URLs matched the requested source origin.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->components->info("Dry run: {$matches} canonical URL(s) would be updated from [{$from}] to [{$to}].");

            return self::SUCCESS;
        }

        $updated = 0;

        SeoMetadata::query()
            ->whereNotNull('canonical_url')
            ->where('canonical_url', 'like', $from.'%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($from, $to, &$updated): void {
                foreach ($rows as $row) {
                    $current = (string) $row->canonical_url;

                    if (! $this->matchesOrigin($current, $from)) {
                        continue;
                    }

                    $row->forceFill([
                        'canonical_url' => $to.substr($current, strlen($from)),
                    ])->save();

                    $updated++;
                }
            });

        $this->components->info("Updated {$updated} canonical URL(s) from [{$from}] to [{$to}].");

        return self::SUCCESS;
    }

    private function normalizeOrigin(string $url): ?string
    {
        $trimmed = rtrim(trim($url), '/');

        if ($trimmed === '' || filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $trimmed;
    }

    private function matchesOrigin(string $url, string $origin): bool
    {
        if (! str_starts_with($url, $origin)) {
            return false;
        }

        $remainder = substr($url, strlen($origin));

        return $remainder === '' || str_starts_with($remainder, '/');
    }
}
