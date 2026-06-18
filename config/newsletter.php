<?php

return [
    'driver' => env('NEWSLETTER_DRIVER', 'mail'),

    'from' => [
        'address' => env('NEWSLETTER_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
        'name' => env('NEWSLETTER_FROM_NAME', env('MAIL_FROM_NAME', env('APP_NAME', 'Wide Web Blog'))),
    ],

    'queues' => [
        'campaigns' => env('NEWSLETTER_CAMPAIGN_QUEUE', 'newsletter'),
        'recipients' => env('NEWSLETTER_RECIPIENT_QUEUE', 'newsletter'),
    ],

    'webhook_secret' => env('NEWSLETTER_WEBHOOK_SECRET'),
];
