<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External\Concerns;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Support\Arr;

trait InteractsWithProducts
{
    protected function findProductByUniqueId(string $uniqueId): ?Product
    {
        /** @var Product|null */
        return Product::query()->where('reference_id', $uniqueId)->first();
    }

    protected function findLatestVersionByProduct(Product $product): ?ProductVersion
    {
        /** @var ProductVersion|null */
        return ProductVersion::query()
            ->where('product_reference_id', $product->reference_id)
            ->where('is_active', 1)
            ->latest('released_at')
            ->first();
    }

    protected function prepareProductVersionData(ProductVersion $productVersion, Product $product): array
    {
        $data = $productVersion->toArray();
        $data['id'] = $data['version_id'] ?? null;
        $data['update_id'] = $data['version_id'] ?? null;
        $data['product_id'] = $product->reference_id;
        $data['has_sql'] = (bool) $productVersion->sql_file;

        // Ensure released_at is in Y-m-d format for API compatibility
        if (! empty($data['released_at'])) {
            $data['released_at'] = date('Y-m-d', strtotime($data['released_at']));
        }

        return Arr::except($data, ['main_file', 'sql_file']);
    }

    /**
     * Find a product version by its version_id (version identifier).
     */
    protected function findVersionByVid(string $vid): ?ProductVersion
    {
        /** @var ProductVersion|null */
        return ProductVersion::query()->where('version_id', $vid)->first();
    }

    /**
     * Get the file path for a version's main or sql file.
     * Checks multiple path structures for backward compatibility.
     * Returns null if file doesn't exist or filename is empty.
     */
    protected function getVersionFilePath(ProductVersion $version, string $type): ?string
    {
        $product = $version->versionProduct;
        $fileName = $type === 'main' ? $version->main_file : $version->sql_file;

        if (! $fileName || ! $product) {
            return null;
        }

        $paths = [
            // New path with product subdirectory
            storage_path('app/version-files/' . $product->reference_id . '/' . $fileName),
            // Flat storage path (no product subdirectory)
            storage_path('app/version-files/' . $fileName),
            // Legacy LicenseBox path
            base_path('version-files/' . $fileName),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Sanitize a string for use as a filename.
     * Converts to lowercase, replaces spaces/dashes with hyphens, dots with underscores.
     */
    protected function sanitizeFilename(string $string): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[\s-]+/', ' ', $string);
        $string = preg_replace('/[\s_]/', '-', $string);

        return str_replace('.', '_', $string);
    }
}
