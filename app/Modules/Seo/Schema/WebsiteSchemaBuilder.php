<?php

namespace App\Modules\Seo\Schema;

class WebsiteSchemaBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $baseUrl = rtrim((string) config('app.url'), '/');

        return [
            '@type' => 'WebSite',
            '@id' => "{$baseUrl}/#website",
            'name' => (string) config('app.name'),
            'url' => "{$baseUrl}/",
            'publisher' => [
                '@id' => "{$baseUrl}/#organization",
            ],
        ];
    }
}
