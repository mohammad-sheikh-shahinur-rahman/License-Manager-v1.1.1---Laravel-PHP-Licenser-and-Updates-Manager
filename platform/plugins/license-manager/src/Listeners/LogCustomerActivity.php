<?php

namespace Botble\LicenseManager\Listeners;

use Botble\LicenseManager\Events\LicenseActivated;
use Botble\LicenseManager\Events\LicenseDeactivated;
use Botble\LicenseManager\Events\LicenseVerified;
use Botble\LicenseManager\Events\UpdateDownloaded;
use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\CustomerActivityLog;
use Botble\LicenseManager\Models\ProductActivation;
use Illuminate\Support\Facades\Request;

class LogCustomerActivity
{
    public function handleLicenseActivated(LicenseActivated $event): void
    {
        $activation = $event->activation;
        $domain = $this->extractDomain($activation->url);

        $this->log(
            $activation->customer_id,
            'license_activated',
            trans('plugins/license-manager::license-manager.activity_log.customer_license_activated', [
                'license' => e($activation->license_code),
                'domain' => e($domain ?: $activation->url),
            ]),
            $activation->ip_address
        );
    }

    public function handleLicenseDeactivated(LicenseDeactivated $event): void
    {
        $activation = $event->activation;
        $domain = $this->extractDomain($activation->url);

        $this->log(
            $activation->customer_id,
            'license_deactivated',
            trans('plugins/license-manager::license-manager.activity_log.customer_license_deactivated', [
                'license' => e($activation->license_code),
                'domain' => e($domain ?: $activation->url),
            ]),
            $activation->ip_address
        );
    }

    public function handleLicenseVerified(LicenseVerified $event): void
    {
        $activation = $event->activation;
        $domain = $this->extractDomain($activation->url);

        $this->log(
            $activation->customer_id,
            'license_verified',
            trans('plugins/license-manager::license-manager.activity_log.customer_license_verified', [
                'license' => e($activation->license_code),
                'domain' => e($domain ?: $activation->url),
            ]),
            $activation->ip_address ?: Request::ip()
        );
    }

    public function handleUpdateDownloaded(UpdateDownloaded $event): void
    {
        $version = $event->productVersion;
        $product = $event->product;

        $clientIp = Request::ip();
        $clientUrl = Request::input('url') ?: Request::header('Referer') ?: '';

        $licenseCode = Request::input('license_code');
        $clientName = Request::input('client_name');

        if (! $clientName && ($licenseData = Request::input('license_data') ?: Request::input('license_file'))) {
            $clientName = $this->resolveClientNameFromActivation($licenseData);
        }

        if (! $clientName) {
            return;
        }

        $this->log(
            $clientName,
            'update_downloaded',
            trans('plugins/license-manager::license-manager.activity_log.customer_update_downloaded', [
                'version' => e($version->version),
                'product' => e($product->name),
            ]),
            $clientIp
        );
    }

    protected function log(
        ?string $customerId,
        string $type,
        string $message,
        ?string $ipAddress = null
    ): void {
        if (! $customerId) {
            return;
        }

        CustomerActivityLog::query()->create([
            'customer_id' => $customerId,
            'type' => $type,
            'message' => $message,
            'ip_address' => $ipAddress ?: Request::ip(),
            'user_agent' => Request::userAgent() ?: '',
        ]);
    }

    protected function extractDomain(?string $url): string
    {
        if (! $url) {
            return '';
        }

        $parsed = parse_url($url);

        return $parsed['host'] ?? $url;
    }

    protected function resolveClientNameFromActivation(string $licenseData): ?string
    {
        try {
            $licenseManager = app(LicenseManager::class);
            $encrypter = $licenseManager->createEncrypter();
            $decrypted = $encrypter->decrypt($licenseData);

            if (is_array($decrypted) && isset($decrypted['id'])) {
                $activation = ProductActivation::query()->find($decrypted['id']);

                return $activation?->customer_id;
            }
        } catch (\Throwable) {
        }

        return null;
    }
}
