<?php

namespace App\Console\Commands;

use App\Models\AboutPage;
use App\Models\Homepage;
use App\Models\SiteSettings;
use Illuminate\Console\Command;

class RewritePublicContentHostsCommand extends Command
{
    protected $signature = 'content:rewrite-public-hosts
        {from : Existing origin to replace, for example https://service.widewebblog.com}
        {--to-site= : Replacement site origin. Defaults to FRONTEND_URL/app.frontend_url}
        {--to-media= : Replacement media origin. Defaults to R2_URL}
        {--dry-run : Preview matching field counts without updating records}';

    protected $description = 'Rewrite stored public content/config URLs to the configured site and media hosts.';

    public function handle(): int
    {
        $from = $this->normalizeOrigin((string) $this->argument('from'));
        $toSite = $this->normalizeOrigin((string) ($this->option('to-site') ?: config('app.frontend_url', config('app.url'))));
        $toMedia = $this->normalizeOrigin((string) config('filesystems.disks.r2.url'));

        if ($this->option('to-media')) {
            $toMedia = $this->normalizeOrigin((string) $this->option('to-media'));
        }

        if ($from === null) {
            $this->components->error('The [from] origin must be a valid absolute URL.');

            return self::INVALID;
        }

        if ($toSite === null) {
            $this->components->error('The target site origin is invalid. Set FRONTEND_URL or pass --to-site=');

            return self::INVALID;
        }

        if ($toMedia === null) {
            $this->components->error('The target media origin is invalid. Set R2_URL or pass --to-media=');

            return self::INVALID;
        }

        $rewrites = [
            'homepage' => $this->rewriteHomepage($from, $toSite, $toMedia, true),
            'about_page' => $this->rewriteAboutPage($from, $toSite, $toMedia, true),
            'site_settings' => $this->rewriteSiteSettings($from, $toSite, true),
        ];

        $matches = array_sum($rewrites);

        if ($matches === 0) {
            $this->components->info('No stored public content/config URLs matched the requested source origin.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->components->info("Dry run: {$matches} URL field(s) would be updated from [{$from}].");
            $this->line('homepage: '.$rewrites['homepage']);
            $this->line('about_page: '.$rewrites['about_page']);
            $this->line('site_settings: '.$rewrites['site_settings']);

            return self::SUCCESS;
        }

        $updated = [
            'homepage' => $this->rewriteHomepage($from, $toSite, $toMedia, false),
            'about_page' => $this->rewriteAboutPage($from, $toSite, $toMedia, false),
            'site_settings' => $this->rewriteSiteSettings($from, $toSite, false),
        ];

        $total = array_sum($updated);

        $this->components->info("Updated {$total} public content/config URL field(s) from [{$from}].");
        $this->line('homepage: '.$updated['homepage']);
        $this->line('about_page: '.$updated['about_page']);
        $this->line('site_settings: '.$updated['site_settings']);

        return self::SUCCESS;
    }

    private function rewriteHomepage(string $from, string $toSite, string $toMedia, bool $dryRun): int
    {
        $homepage = Homepage::query()->first();

        if ($homepage === null) {
            return 0;
        }

        $count = 0;
        $hero = $this->rewriteFieldSet((array) ($homepage->hero ?? []), [
            'primary_cta_url' => $toSite,
            'secondary_cta_url' => $toSite,
            'media_url' => $toMedia,
        ], $from, $count);
        $promo = $this->rewriteFieldSet((array) ($homepage->promo_section ?? []), [
            'primary_cta_url' => $toSite,
        ], $from, $count);

        if (! $dryRun && $count > 0) {
            $homepage->forceFill([
                'hero' => $hero,
                'promo_section' => $promo,
            ])->save();
        }

        return $count;
    }

    private function rewriteAboutPage(string $from, string $toSite, string $toMedia, bool $dryRun): int
    {
        $about = AboutPage::query()->first();

        if ($about === null) {
            return 0;
        }

        $count = 0;
        $hero = $this->rewriteFieldSet((array) ($about->hero ?? []), [
            'media_url' => $toMedia,
        ], $from, $count);
        $team = $this->rewriteFieldSet((array) ($about->team_section ?? []), [
            'primary_cta_url' => $toSite,
        ], $from, $count);

        $members = array_map(function ($member) use ($from, $toMedia, &$count) {
            if (! is_array($member)) {
                return $member;
            }

            return $this->rewriteFieldSet($member, [
                'image_url' => $toMedia,
            ], $from, $count);
        }, (array) ($team['members'] ?? []));

        $team['members'] = array_values($members);

        if (! $dryRun && $count > 0) {
            $about->forceFill([
                'hero' => $hero,
                'team_section' => $team,
            ])->save();
        }

        return $count;
    }

    private function rewriteSiteSettings(string $from, string $toSite, bool $dryRun): int
    {
        $settings = SiteSettings::query()->first();

        if ($settings === null) {
            return 0;
        }

        $count = 0;
        $footer = (array) ($settings->footer ?? []);

        $footer['social_links'] = array_values(array_map(function ($link) use ($from, $toSite, &$count) {
            return is_array($link)
                ? $this->rewriteFieldSet($link, ['url' => $toSite], $from, $count)
                : $link;
        }, (array) ($footer['social_links'] ?? [])));

        $footer['legal_links'] = array_values(array_map(function ($link) use ($from, $toSite, &$count) {
            return is_array($link)
                ? $this->rewriteFieldSet($link, ['url' => $toSite], $from, $count)
                : $link;
        }, (array) ($footer['legal_links'] ?? [])));

        if (! $dryRun && $count > 0) {
            $settings->forceFill([
                'footer' => $footer,
            ])->save();
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $fieldTargets
     * @return array<string, mixed>
     */
    private function rewriteFieldSet(array $payload, array $fieldTargets, string $from, int &$count): array
    {
        foreach ($fieldTargets as $field => $targetOrigin) {
            $current = $payload[$field] ?? null;

            if (! is_string($current)) {
                continue;
            }

            $rewritten = $this->rewriteUrl($current, $from, $targetOrigin);

            if ($rewritten === null || $rewritten === $current) {
                continue;
            }

            $payload[$field] = $rewritten;
            $count++;
        }

        return $payload;
    }

    private function rewriteUrl(string $url, string $from, string $to): ?string
    {
        if (! str_starts_with($url, $from)) {
            return null;
        }

        $remainder = substr($url, strlen($from));

        if ($remainder !== '' && ! str_starts_with($remainder, '/')) {
            return null;
        }

        return $to.$remainder;
    }

    private function normalizeOrigin(string $url): ?string
    {
        $trimmed = rtrim(trim($url), '/');

        if ($trimmed === '' || filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return $trimmed;
    }
}
