<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasUploadedFile
{
    protected function uploadedFileUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disco = config('filesystems.uploads_disk');
        $storage = Storage::disk($disco);

        if (config("filesystems.disks.{$disco}.driver") === 's3') {
            return $storage->temporaryUrl($path, now()->addMinutes(10));
        }

        return $storage->url($path);
    }
}
