<?php

namespace App\Modules\Media\Services\Contracts;

interface MediaStorage
{
    /**
     * @param  array<string, mixed>  $options
     */
    public function put(string $objectKey, string $contents, array $options = []): void;

    public function read(string $objectKey): string;

    public function delete(string $objectKey): void;

    public function url(string $objectKey): string;
}
