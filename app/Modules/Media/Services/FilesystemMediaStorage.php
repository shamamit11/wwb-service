<?php

namespace App\Modules\Media\Services;

use App\Modules\Media\Services\Contracts\MediaStorage;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;

class FilesystemMediaStorage implements MediaStorage
{
    public function __construct(
        private readonly FilesystemFactory $filesystem,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function put(string $objectKey, string $contents, array $options = []): void
    {
        $this->disk()->put($objectKey, $contents, $options);
    }

    public function read(string $objectKey): string
    {
        return $this->disk()->get($objectKey);
    }

    public function delete(string $objectKey): void
    {
        $this->disk()->delete($objectKey);
    }

    public function url(string $objectKey): string
    {
        return $this->disk()->url($objectKey);
    }

    private function disk(): Filesystem
    {
        return $this->filesystem->disk((string) config('filesystems.media_disk', 'r2'));
    }
}
