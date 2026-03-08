<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Http\Controllers\Api\External\Concerns\InteractsWithProducts;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UpdateDownloadSizeController
{
    use InteractsWithProducts;

    /**
     * Legacy API endpoint with query params.
     * Accepts query params: type (main/sql), vid (version ID)
     */
    public function legacy(Request $request): Response
    {
        $type = $request->query('type');
        $vid = $request->query('version_id') ?: $request->query('vid');

        if (! $type || ! $vid) {
            return response()->noContent(404);
        }

        return $this->__invoke($vid, $type);
    }

    /**
     * Legacy API endpoint with path params.
     * Route: get_update_size/{type}/{version} - type comes first in legacy URLs
     */
    public function legacyPath(string $type, string $version): Response
    {
        return $this->__invoke($version, $type);
    }

    /**
     * Legacy API endpoint with single param (Core.php CMS v7.x).
     * Route: get_update_size/{version} - defaults type to 'main'
     */
    public function legacySingleParam(string $version): Response
    {
        return $this->__invoke($version, 'main');
    }

    public function __invoke(string $version, string $type): Response
    {
        // Validate type parameter
        if (! in_array($type, ['main', 'sql'])) {
            return response()->noContent(404);
        }

        // Find version by vid
        $productVersion = $this->findVersionByVid($version);

        if (! $productVersion) {
            return response()->noContent(404);
        }

        // Validate product is active
        $product = $productVersion->versionProduct;

        if (! $product || $product->is_active != 1) {
            return response()->noContent(404);
        }

        // Check file exists
        $filePath = $this->getVersionFilePath($productVersion, $type);

        if (! $filePath) {
            return response()->noContent(404);
        }

        // Build download filename
        $productName = $this->sanitizeFilename($product->name);
        $versionName = $this->sanitizeFilename($productVersion->version);
        $extension = $type === 'main' ? 'zip' : 'sql';
        $downloadFilename = "{$type}_{$productName}_{$versionName}.{$extension}";

        $contentType = $type === 'main' ? 'application/zip' : 'application/sql';

        return response()->noContent(200, [
            'Content-Type' => $contentType,
            'Content-Transfer-Encoding' => 'Binary',
            'Content-Length' => filesize($filePath),
            'Content-Disposition' => "attachment; filename={$downloadFilename}",
        ]);
    }
}
