<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External;

use Botble\LicenseManager\Events\UpdateDownloaded;
use Botble\LicenseManager\Http\Controllers\Api\External\Concerns\InteractsWithProducts;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\ProductVersion;
use Botble\LicenseManager\Models\UpdateDownload;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UpdateDownloadController
{
    use InteractsWithProducts;

    /**
     * Legacy API endpoint with query params.
     * Accepts query params: type (main/sql), vid (version ID)
     * Accepts POST body: license_data/license_file OR (license_code + client_name)
     */
    public function legacy(Request $request, LicenseManager $licenseManager): Response|BinaryFileResponse
    {
        $type = $request->query('type');
        $vid = $request->query('version_id') ?: $request->query('vid');

        if (! $type || ! $vid) {
            return $this->abortDownload(404);
        }

        return $this->__invoke($request, $licenseManager, $vid, $type);
    }

    /**
     * Legacy API endpoint with path params.
     * Route: download_update/{type}/{version} - type comes first in legacy URLs
     */
    public function legacyPath(
        Request $request,
        LicenseManager $licenseManager,
        string $type,
        string $version
    ): Response|BinaryFileResponse {
        return $this->__invoke($request, $licenseManager, $version, $type);
    }

    /**
     * Legacy API endpoint with single param (Core.php CMS v7.x).
     * Route: download_update/{version} - defaults type to 'main'
     */
    public function legacySingleParam(
        Request $request,
        LicenseManager $licenseManager,
        string $version
    ): Response|BinaryFileResponse {
        return $this->__invoke($request, $licenseManager, $version, 'main');
    }

    public function __invoke(
        Request $request,
        LicenseManager $licenseManager,
        string $version,
        string $type
    ): Response|BinaryFileResponse {
        // Validate type parameter
        if (! in_array($type, ['main', 'sql'])) {
            Log::warning('[UpdateDownload] Invalid type', ['type' => $type, 'version' => $version]);

            return $this->abortDownload(404);
        }

        // Find version by vid
        $productVersion = $this->findVersionByVid($version);

        if (! $productVersion) {
            Log::warning('[UpdateDownload] Version not found', ['version_id' => $version]);

            return $this->abortDownload(404);
        }

        // Validate product is active
        $product = $productVersion->versionProduct;

        if (! $product || ! $product->exists || $product->is_active != 1) {
            Log::warning('[UpdateDownload] Product not active', [
                'version_id' => $version,
                'product_ref' => $productVersion->product_reference_id,
                'product_exists' => $product?->exists,
                'is_active' => $product?->is_active,
            ]);

            return $this->abortDownload(404);
        }

        // Check file exists
        $filePath = $this->getVersionFilePath($productVersion, $type);

        if (! $filePath) {
            Log::warning('[UpdateDownload] File not found', [
                'version_id' => $version,
                'product_ref' => $product->reference_id,
                'type' => $type,
                'file' => $type === 'main' ? $productVersion->main_file : $productVersion->sql_file,
            ]);

            return $this->abortDownload(404);
        }

        $clientUrl = $licenseManager->getClientUrl();
        $clientIp = $licenseManager->getClientIp();

        // License validation (if required by product)
        if ($product->license_update == 1) {
            $licenseValid = $this->validateUpdateLicense($request, $licenseManager, $product->reference_id);

            if (! $licenseValid) {
                $this->logDownload($productVersion, $clientUrl, $clientIp, false);
                $this->logFailedLicenseAttempt($product->name, $productVersion->version, $clientUrl);

                return $this->abortDownload(401);
            }
        }

        // Log successful download
        $this->logDownload($productVersion, $clientUrl, $clientIp, true);

        do_action('lm_update_downloaded', $productVersion, $product, $type);
        UpdateDownloaded::dispatch($productVersion, $product, $type);

        return $this->createFileResponse($request, $filePath, $product, $productVersion, $type);
    }

    /**
     * Validate license for update download.
     * Accepts either encrypted license_data/license_file or plain license_code + client_name.
     */
    protected function validateUpdateLicense(
        Request $request,
        LicenseManager $licenseManager,
        string $productPid
    ): bool {
        $licenseCode = null;
        $clientName = null;

        // Support both license_data (modern) and license_file (legacy CMS)
        $licenseFileInput = $request->input('license_data') ?: $request->input('license_file');

        if ($licenseFileInput) {
            $decrypted = $this->decryptLicenseFile($licenseFileInput, $licenseManager);

            if ($decrypted === null) {
                Log::warning('[UpdateDownload] License file decryption failed', ['product_pid' => $productPid]);

                return false;
            }

            // Handle modern format: {id, activation_id}
            if (isset($decrypted['id']) || isset($decrypted['activation_id'])) {
                $activationId = $decrypted['id'] ?? $decrypted['activation_id'];
                $activation = ProductActivation::query()->find($activationId);

                if (! $activation || ! $activation->is_valid || ! $activation->is_active) {
                    Log::warning('[UpdateDownload] Activation invalid', [
                        'activation_id' => $activationId,
                        'exists' => (bool) $activation,
                        'is_valid' => $activation?->is_valid,
                        'is_active' => $activation?->is_active,
                    ]);

                    return false;
                }

                $licenseCode = $activation->license_code;
                $clientName = $activation->customer_id;
            } else {
                // Handle legacy format: {license, client}
                $licenseCode = $decrypted['license'] ?? null;
                $clientName = $decrypted['customer_id'] ?? $decrypted['client'] ?? null;
            }
        } elseif ($request->filled('license_code') && $request->filled('client_name')) {
            $licenseCode = $request->input('license_code');
            $clientName = $request->input('client_name');
        } else {
            Log::warning('[UpdateDownload] No license data provided', ['product_pid' => $productPid]);

            return false;
        }

        if (! $licenseCode) {
            Log::warning('[UpdateDownload] No license code resolved', ['product_pid' => $productPid]);

            return false;
        }

        // Find license for this product
        $license = ProductLicense::query()
            ->whereRaw('LOWER(license_code) = ?', [Str::lower($licenseCode)])
            ->where('product_reference_id', $productPid)
            ->first();

        if (! $license) {
            Log::warning('[UpdateDownload] License not found', [
                'license_code' => Str::mask($licenseCode, '*', 4, -4),
                'product_pid' => $productPid,
            ]);

            return false;
        }

        // Check license validity (not blocked)
        if (! $license->is_valid) {
            return false;
        }

        // Check client name matches (if assigned)
        if ($license->customer_id && $clientName && strtolower($license->customer_id) !== strtolower($clientName)) {
            return false;
        }

        // Check license expiry
        if ($license->expires_at && Carbon::now() >= $license->expires_at) {
            return false;
        }

        // Check updates_till expiry (specific for update downloads)
        if ($license->updates_until && Carbon::now() >= $license->updates_until) {
            return false;
        }

        // Check domain restriction (partial match like old LicenseBox)
        $clientDomain = $licenseManager->getClientDomain();
        $allowedDomains = $this->parseDomainsOrIps($license->domains);

        if (! empty($allowedDomains) && $clientDomain) {
            $domainAllowed = false;

            foreach ($allowedDomains as $allowedDomain) {
                // Partial match: if client domain contains allowed domain (legacy behavior)
                if (str_contains($clientDomain, $allowedDomain)) {
                    $domainAllowed = true;

                    break;
                }
            }

            if (! $domainAllowed) {
                return false;
            }
        }

        // Check IP restriction
        $clientIp = $licenseManager->getClientIp();
        $allowedIps = $this->parseDomainsOrIps($license->ips);

        if (! empty($allowedIps) && ! in_array($clientIp, $allowedIps, true)) {
            return false;
        }

        return true;
    }

    /**
     * Parse domains or IPs from JSON string, comma-separated string, or array.
     *
     * @return array<string>
     */
    protected function parseDomainsOrIps(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_array($value)) {
            return array_map('trim', array_filter($value));
        }

        if (! is_string($value)) {
            return [];
        }

        // Try JSON decode first
        $decoded = json_decode($value, true);

        if (is_array($decoded)) {
            return array_map('trim', array_filter($decoded));
        }

        // Fall back to comma-separated
        return array_map('trim', array_filter(explode(',', $value)));
    }

    /**
     * Log download attempt to update_downloads table.
     */
    protected function logDownload(
        ProductVersion $version,
        ?string $url,
        ?string $ip,
        bool $isValid
    ): void {
        UpdateDownload::query()->create([
            'download_id' => Str::random(15),
            'product_reference_id' => $version->product_reference_id,
            'version_id' => $version->version_id,
            'url' => $url ?? '',
            'ip_address' => $ip ?? '',
            'is_valid' => $isValid ? 1 : 0,
            'downloaded_at' => Carbon::now(),
        ]);
    }

    /**
     * Log failed license validation to activity_logs table.
     */
    protected function logFailedLicenseAttempt(
        string $productName,
        string $versionNumber,
        ?string $url
    ): void {
        $cleanUrl = $this->cleanUrl($url ?? '');
        $log = trans('plugins/license-manager::license-manager.activity_log.update_download_blocked', [
            'product' => e($productName),
            'version' => e($versionNumber),
            'url' => e($url ?? ''),
            'domain' => e($cleanUrl),
        ]);

        ActivityLog::query()->create([
            'message' => $log,
            'created_at' => Carbon::now(),
        ]);
    }

    /**
     * Clean URL for display in logs.
     */
    protected function cleanUrl(string $url): string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $input = trim($url, '/');

        if (! preg_match('#^http(s)?://#', $input)) {
            $input = 'http://' . $input;
        }

        $urlParts = parse_url($input);
        $domain = preg_replace('/^www\./', '', $urlParts['host'] ?? '');

        if (! empty($urlParts['path'])) {
            $domain .= $urlParts['path'];
        }

        return $domain;
    }

    /**
     * Create a BinaryFileResponse that supports Range requests (resumable downloads).
     */
    protected function createFileResponse(
        Request $request,
        string $filePath,
        $product,
        ProductVersion $version,
        string $type
    ): BinaryFileResponse {
        $productName = $this->sanitizeFilename($product->name);
        $versionName = $this->sanitizeFilename($version->version);
        $extension = $type === 'main' ? 'zip' : 'sql';
        $filename = "{$type}_{$productName}_{$versionName}.{$extension}";
        $contentType = $type === 'main' ? 'application/zip' : 'application/sql';

        $response = new BinaryFileResponse($filePath, 200, [
            'Content-Type' => $contentType,
            'Content-Transfer-Encoding' => 'Binary',
        ]);

        $response->setContentDisposition('attachment', $filename);
        $response->prepare($request);

        return $response;
    }

    /**
     * Return an empty response with the given status code.
     */
    protected function abortDownload(int $status): Response
    {
        return response()->noContent($status);
    }

    /**
     * Decrypt license file using modern or legacy encryption.
     */
    protected function decryptLicenseFile(string $licenseFile, LicenseManager $licenseManager): ?array
    {
        // Try modern encryption first (if configured)
        try {
            $encrypter = $licenseManager->createEncrypter();
            $decrypted = $encrypter->decrypt($licenseFile);

            if (is_array($decrypted)) {
                return $decrypted;
            }
        } catch (DecryptException|\RuntimeException) {
            // Fall through to try legacy encryption
        }

        // Try legacy (CodeIgniter) encryption
        if ($licenseManager->hasLegacyEncryption()) {
            $legacyEncrypter = $licenseManager->createLegacyEncrypter();

            if ($legacyEncrypter) {
                $decrypted = $legacyEncrypter->decrypt($licenseFile);

                if ($decrypted !== false) {
                    $data = json_decode($decrypted, true);

                    if (is_array($data)) {
                        return $data;
                    }
                }
            }
        }

        return null;
    }
}
