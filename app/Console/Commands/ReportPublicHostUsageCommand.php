<?php

namespace App\Console\Commands;

use App\Models\AboutPage;
use App\Models\Homepage;
use App\Models\SeoMetadata;
use App\Models\SiteSettings;
use Illuminate\Console\Command;

class ReportPublicHostUsageCommand extends Command
{
    protected $signature = 'content:report-public-host-usage
        {from : Origin to inspect, for example https://service.widewebblog.com}';

    protected $description = 'Report stored DB fields that still point at an old public/service host.';

    public function handle(): int
    {
        $from = $this->normalizeOrigin((string) $this->argument('from'));

        if ($from === null) {
            $this->components->error('The [from] origin must be a valid absolute URL.');

            return self::INVALID;
        }

        $matches = [
            ...$this->findSeoMetadataMatches($from),
            ...$this->findHomepageMatches($from),
            ...$this->findAboutPageMatches($from),
            ...$this->findSiteSettingsMatches($from),
        ];

        if ($matches === []) {
            $this->components->info("No stored DB fields currently point at [{$from}].");

            return self::SUCCESS;
        }

        $this->components->warn('Stored DB fields still using the inspected origin:');

        foreach ($matches as $match) {
            $this->line($match);
        }

        $this->newLine();
        $this->components->info('Total matches: '.count($matches));

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function findSeoMetadataMatches(string $from): array
    {
        $matches = [];

        SeoMetadata::query()
            ->whereNotNull('canonical_url')
            ->where('canonical_url', 'like', $from.'%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($from, &$matches): void {
                foreach ($rows as $row) {
                    $url = (string) $row->canonical_url;

                    if (! $this->matchesOrigin($url, $from)) {
                        continue;
                    }

                    $matches[] = sprintf(
                        'seo_metadata[%d].canonical_url = %s',
                        $row->id,
                        $url,
                    );
                }
            });

        return $matches;
    }

    /**
     * @return list<string>
     */
    private function findHomepageMatches(string $from): array
    {
        $homepage = Homepage::query()->first();

        if ($homepage === null) {
            return [];
        }

        return array_merge(
            $this->collectFieldMatches('homepages.default.hero', (array) ($homepage->hero ?? []), [
                'primary_cta_url',
                'secondary_cta_url',
                'media_url',
            ], $from),
            $this->collectFieldMatches('homepages.default.promo_section', (array) ($homepage->promo_section ?? []), [
                'primary_cta_url',
            ], $from),
        );
    }

    /**
     * @return list<string>
     */
    private function findAboutPageMatches(string $from): array
    {
        $about = AboutPage::query()->first();

        if ($about === null) {
            return [];
        }

        $matches = array_merge(
            $this->collectFieldMatches('about_pages.default.hero', (array) ($about->hero ?? []), [
                'media_url',
            ], $from),
            $this->collectFieldMatches('about_pages.default.team_section', (array) ($about->team_section ?? []), [
                'primary_cta_url',
            ], $from),
        );

        foreach (array_values((array) (($about->team_section ?? [])['members'] ?? [])) as $index => $member) {
            if (! is_array($member)) {
                continue;
            }

            $matches = array_merge(
                $matches,
                $this->collectFieldMatches("about_pages.default.team_section.members[{$index}]", $member, [
                    'image_url',
                ], $from),
            );
        }

        return $matches;
    }

    /**
     * @return list<string>
     */
    private function findSiteSettingsMatches(string $from): array
    {
        $settings = SiteSettings::query()->first();

        if ($settings === null) {
            return [];
        }

        $footer = (array) ($settings->footer ?? []);
        $matches = [];

        foreach (array_values((array) ($footer['social_links'] ?? [])) as $index => $link) {
            if (! is_array($link)) {
                continue;
            }

            $matches = array_merge(
                $matches,
                $this->collectFieldMatches("site_settings.default.footer.social_links[{$index}]", $link, [
                    'url',
                ], $from),
            );
        }

        foreach (array_values((array) ($footer['legal_links'] ?? [])) as $index => $link) {
            if (! is_array($link)) {
                continue;
            }

            $matches = array_merge(
                $matches,
                $this->collectFieldMatches("site_settings.default.footer.legal_links[{$index}]", $link, [
                    'url',
                ], $from),
            );
        }

        return $matches;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<string>  $fields
     * @return list<string>
     */
    private function collectFieldMatches(string $prefix, array $payload, array $fields, string $from): array
    {
        $matches = [];

        foreach ($fields as $field) {
            $value = $payload[$field] ?? null;

            if (! is_string($value) || ! $this->matchesOrigin($value, $from)) {
                continue;
            }

            $matches[] = "{$prefix}.{$field} = {$value}";
        }

        return $matches;
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
