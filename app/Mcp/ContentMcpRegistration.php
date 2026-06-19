<?php

namespace App\Mcp;

final class ContentMcpRegistration
{
    public static function shouldRegister(): bool
    {
        if (! app()->environment('production')) {
            return true;
        }

        return (bool) config('ai.service.mcp.enabled', false);
    }

    /**
     * @return list<string>
     */
    public static function middleware(): array
    {
        return [
            'auth:sanctum',
            'can:access-admin-api',
        ];
    }

    public static function path(): string
    {
        return trim((string) config('ai.service.mcp.path', 'mcp/content-operations'), '/');
    }
}
