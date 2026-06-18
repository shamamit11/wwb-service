<?php

namespace App\Modules\Newsletter\Services;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterCampaignRecipient;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NewsletterTrackingService
{
    public function trackedHtml(NewsletterCampaign $campaign, NewsletterCampaignRecipient $recipient): string
    {
        $html = $campaign->content_html ?: Str::markdown($campaign->content_markdown ?? '');
        $rewritten = preg_replace_callback(
            '/href=(["\'])(https?:\/\/[^"\']+)\1/i',
            fn (array $matches): string => 'href='.$matches[1].$this->clickUrl($recipient, $matches[2]).$matches[1],
            $html,
        );

        $pixel = '<img src="'.e($this->openUrl($recipient)).'" alt="" width="1" height="1" style="display:none" />';

        return ($rewritten ?? $html).$pixel;
    }

    public function trackedText(NewsletterCampaign $campaign, NewsletterCampaignRecipient $recipient): string
    {
        $text = trim((string) ($campaign->content_markdown ?? strip_tags((string) $campaign->content_html)));

        return $text."\n\nUnsubscribe: ".$this->unsubscribeUrl($recipient)."\n";
    }

    public function openUrl(NewsletterCampaignRecipient $recipient): string
    {
        return URL::signedRoute('api.v1.public.newsletter.track.open', [
            'recipient' => $recipient->id,
        ]);
    }

    public function clickUrl(NewsletterCampaignRecipient $recipient, string $targetUrl): string
    {
        return URL::signedRoute('api.v1.public.newsletter.track.click', [
            'recipient' => $recipient->id,
            'target' => rtrim(strtr(base64_encode($targetUrl), '+/', '-_'), '='),
        ]);
    }

    public function unsubscribeUrl(NewsletterCampaignRecipient $recipient): string
    {
        return URL::to('/api/v1/public/newsletter/unsubscribe?token='.$recipient->subscriber->unsubscribe_token);
    }

    public function decodeTarget(string $encoded): ?string
    {
        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);

        return is_string($decoded) && filter_var($decoded, FILTER_VALIDATE_URL) ? $decoded : null;
    }
}
