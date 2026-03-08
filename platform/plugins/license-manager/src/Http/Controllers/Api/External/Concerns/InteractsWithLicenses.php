<?php

namespace Botble\LicenseManager\Http\Controllers\Api\External\Concerns;

use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Support\IpHelper;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

trait InteractsWithLicenses
{
    protected function verifyLicense(Product $product, ?string $clientName, string $licenseData): ProductActivation|false
    {
        $licenseManager = app(LicenseManager::class);

        // Try modern encryption first
        try {
            $encrypter = $licenseManager->createEncrypter();
            $decryptedLicenseData = $encrypter->decrypt($licenseData);

            return $this->verifyModernLicense($product, $clientName, $decryptedLicenseData, $licenseManager);
        } catch (DecryptException $e) {
            Log::debug('[LicenseVerify] Modern decryption failed', [
                'product' => $product->reference_id,
                'error' => $e->getMessage(),
            ]);
        }

        // Try legacy (CodeIgniter) encryption
        if ($licenseManager->hasLegacyEncryption()) {
            $legacyEncrypter = $licenseManager->createLegacyEncrypter();

            if ($legacyEncrypter) {
                $decrypted = $legacyEncrypter->decrypt($licenseData);

                if ($decrypted !== false) {
                    $decryptedData = json_decode($decrypted, true);

                    if (is_array($decryptedData)) {
                        return $this->verifyLegacyLicense($product, $clientName, $decryptedData, $licenseManager);
                    }
                }
            }
        }

        Log::warning('[LicenseVerify] All decryption methods failed', [
            'product' => $product->reference_id,
        ]);

        return false;
    }

    protected function verifyModernLicense(
        Product $product,
        ?string $clientName,
        array $decryptedLicenseData,
        LicenseManager $licenseManager
    ): ProductActivation|false {
        $activationId = Arr::get($decryptedLicenseData, 'id');
        $activation = ProductActivation::query()
            ->whereKey($activationId)
            ->first();

        if (! $activation) {
            Log::warning('[LicenseVerify] Activation not found', [
                'activation_id' => $activationId,
                'product' => $product->reference_id,
            ]);

            return false;
        }

        if ($activation->product_reference_id !== $product->reference_id) {
            Log::warning('[LicenseVerify] Product mismatch', [
                'activation_id' => $activationId,
                'activation_product' => $activation->product_reference_id,
                'request_product' => $product->reference_id,
            ]);

            return false;
        }

        // Check client name only if provided in request (case-insensitive like legacy system)
        if ($clientName && strtolower($activation->customer_id) !== strtolower($clientName)) {
            Log::warning('[LicenseVerify] Client name mismatch', [
                'activation_id' => $activationId,
                'activation_customer' => $activation->customer_id,
                'request_client' => $clientName,
            ]);

            return false;
        }

        if (! $activation->is_active || ! $activation->is_valid) {
            Log::warning('[LicenseVerify] Activation not active/valid', [
                'activation_id' => $activationId,
                'is_active' => $activation->is_active,
                'is_valid' => $activation->is_valid,
            ]);

            return false;
        }

        // Use normalized domain comparison to allow variations (http/https, www/non-www)
        $activationDomain = $activation->normalized_domain;
        $clientDomain = $licenseManager->getNormalizedClientDomain();

        if ($activationDomain !== $clientDomain) {
            Log::warning('[LicenseVerify] Domain mismatch', [
                'activation_id' => $activationId,
                'activation_domain' => $activationDomain,
                'client_domain' => $clientDomain,
                'activation_url' => $activation->url,
                'client_url' => $licenseManager->getClientUrl(),
            ]);

            return false;
        }

        // Optionally verify IP hasn't changed (disabled by default as IPs can change)
        if (setting('lm_verify_license_ip', false) && ! IpHelper::isSameIp($activation->ip_address, $licenseManager->getClientIp())) {
            Log::warning('[LicenseVerify] IP mismatch', [
                'activation_id' => $activationId,
                'activation_ip' => $activation->ip_address,
                'client_ip' => $licenseManager->getClientIp(),
            ]);

            return false;
        }

        return $activation;
    }

    protected function verifyLegacyLicense(
        Product $product,
        ?string $clientName,
        array $decryptedData,
        LicenseManager $licenseManager
    ): ProductActivation|false {
        $licenseCode = Arr::get($decryptedData, 'license');
        $client = Arr::get($decryptedData, 'customer_id') ?? Arr::get($decryptedData, 'client');

        if (! $licenseCode) {
            Log::warning('[LicenseVerify:Legacy] No license code in decrypted data');

            return false;
        }

        // Client name must match (only check if both are provided)
        if ($client && $clientName && strtolower($client) !== strtolower($clientName)) {
            Log::warning('[LicenseVerify:Legacy] Client name mismatch', [
                'decrypted_client' => $client,
                'request_client' => $clientName,
            ]);

            return false;
        }

        // Find license (case-insensitive like legacy system)
        $license = ProductLicense::query()
            ->whereRaw('LOWER(license_code) = ?', [strtolower($licenseCode)])
            ->where('product_reference_id', $product->reference_id)
            ->first();

        if (! $license || ! $license->is_valid) {
            Log::warning('[LicenseVerify:Legacy] License not found or invalid', [
                'license_exists' => (bool) $license,
                'is_valid' => $license?->is_valid,
                'product' => $product->reference_id,
            ]);

            return false;
        }

        // Find active activation for this license matching current domain
        // Normalize domains to allow variations (http/https, www/non-www)
        $clientDomain = $licenseManager->getNormalizedClientDomain();

        $activations = ProductActivation::query()
            ->whereRaw('LOWER(license_code) = ?', [strtolower($licenseCode)])
            ->where('product_reference_id', $product->reference_id)
            ->where('is_active', true)
            ->where('is_valid', true)
            ->get();

        if ($activations->isEmpty()) {
            Log::warning('[LicenseVerify:Legacy] No active activations found', [
                'license_code' => $licenseCode,
                'product' => $product->reference_id,
            ]);

            return false;
        }

        foreach ($activations as $activation) {
            $activationDomain = $licenseManager->extractDomainFromUrl($activation->url);

            if ($activationDomain === $clientDomain) {
                return $activation;
            }
        }

        Log::warning('[LicenseVerify:Legacy] Domain mismatch for all activations', [
            'client_domain' => $clientDomain,
            'activation_domains' => $activations->map(fn ($a) => $licenseManager->extractDomainFromUrl($a->url))->all(),
            'client_url' => $licenseManager->getClientUrl(),
        ]);

        return false;
    }
}
