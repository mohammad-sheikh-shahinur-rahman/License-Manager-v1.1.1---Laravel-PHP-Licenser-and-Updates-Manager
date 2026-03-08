<?php

namespace Botble\LicenseManager\Actions\ProductVersions;

use Botble\LicenseManager\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SaveFile
{
    public function handle(UploadedFile $file, Product $product, string $filename): string
    {
        $path = sprintf('version-files/%s', $product->reference_id);

        File::ensureDirectoryExists(storage_path('app/' . $path));

        $filename = md5($filename . '_' . microtime(true) . '_' . Str::random(8)) . '.' . $file->getClientOriginalExtension();

        Storage::disk('local')->putFileAs($path, $file, $filename);

        return $filename;
    }
}
