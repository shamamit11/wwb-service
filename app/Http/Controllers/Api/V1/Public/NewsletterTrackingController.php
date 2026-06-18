<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Modules\Newsletter\Services\NewsletterTrackingService;
use App\Modules\Newsletter\Services\TrackNewsletterClickService;
use App\Modules\Newsletter\Services\TrackNewsletterOpenService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class NewsletterTrackingController extends Controller
{
    public function open(
        Request $request,
        int $recipient,
        TrackNewsletterOpenService $service,
    ): Response {
        abort_unless($request->hasValidSignature(), 403);

        $service->handle($recipient);

        return response(base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='), 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    public function click(
        Request $request,
        int $recipient,
        string $target,
        TrackNewsletterClickService $service,
        NewsletterTrackingService $tracking,
    ): RedirectResponse {
        abort_unless($request->hasValidSignature(), 403);

        $url = $tracking->decodeTarget($target);
        abort_if($url === null, 404);

        $service->handle($recipient);

        return redirect()->away($url);
    }
}
