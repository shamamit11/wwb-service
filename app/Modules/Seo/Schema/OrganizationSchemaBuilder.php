<?php

namespace App\Modules\Seo\Schema;

class OrganizationSchemaBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $baseUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return [
            '@type' => 'Organization',
            '@id' => "{$baseUrl}/#organization",
            'name' => (string) config('app.name'),
            'url' => "{$baseUrl}/",
        ];
    }
}
