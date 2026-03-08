<?php

namespace Botble\LicenseManager\Services;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\UpdateDownload;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class CronService
{
    public function __construct(protected WebhookService $webhookService)
    {
    }

    public function processLicenseExpirations(): array
    {
        $results = ['warnings_sent' => 0, 'expired_logged' => 0, 'updates_expired' => 0];

        if (setting('lm_send_expiration_warnings') != '1') {
            return $results;
        }

        $warningDays = $this->getWarningDays();
        $today = Carbon::today();

        $results['warnings_sent'] = $this->processExpiringLicenses($today, $warningDays);
        $results['expired_logged'] = $this->processExpiredLicenses($today);
        $results['updates_expired'] = $this->processExpiredUpdates($today);

        return $results;
    }

    public function processAutoBlacklist(): array
    {
        $results = ['domains_blacklisted' => 0, 'ips_blacklisted' => 0];

        $domainThreshold = (int) setting('lm_blacklist_domain_after_failed_attempts', 0);
        $ipThreshold = (int) setting('lm_blacklist_ip_after_failed_attempts', 0);

        if ($domainThreshold <= 0 && $ipThreshold <= 0) {
            return $results;
        }

        $invalidDomains = [];
        $invalidIps = [];

        $failedActivations = ProductActivation::query()
            ->where('is_valid', false)
            ->select(['url', 'ip_address'])
            ->get();

        foreach ($failedActivations as $activation) {
            if ($domainThreshold > 0 && $activation->url) {
                $domain = parse_url($activation->url, PHP_URL_HOST);
                if ($domain) {
                    $invalidDomains[] = $domain;
                }
            }
            if ($ipThreshold > 0 && $activation->ip_address) {
                $invalidIps[] = $activation->ip_address;
            }
        }

        $failedDownloads = UpdateDownload::query()
            ->where('is_valid', false)
            ->select(['url', 'ip_address'])
            ->get();

        foreach ($failedDownloads as $download) {
            if ($domainThreshold > 0 && $download->url) {
                $domain = parse_url($download->url, PHP_URL_HOST);
                if ($domain) {
                    $invalidDomains[] = $domain;
                }
            }
            if ($ipThreshold > 0 && $download->ip_address) {
                $invalidIps[] = $download->ip_address;
            }
        }

        if ($domainThreshold > 0 && ! empty($invalidDomains)) {
            $results['domains_blacklisted'] = $this->processBlacklist(
                $invalidDomains,
                $domainThreshold,
                'lm_blacklisted_domains',
                'Domain'
            );
        }

        if ($ipThreshold > 0 && ! empty($invalidIps)) {
            $results['ips_blacklisted'] = $this->processBlacklist(
                $invalidIps,
                $ipThreshold,
                'lm_blacklisted_ips',
                'IP'
            );
        }

        return $results;
    }

    protected function getWarningDays(): array
    {
        $setting = setting('lm_expiration_warning_days', '7,1');

        return array_map('intval', array_filter(explode(',', $setting)));
    }

    protected function processExpiringLicenses(Carbon $today, array $warningDays): int
    {
        $totalSent = 0;

        foreach ($warningDays as $days) {
            $targetDate = $today->copy()->addDays($days);

            $licenses = ProductLicense::query()
                ->whereNotNull('expires_at')
                ->whereDate('expires_at', $targetDate)
                ->where('is_valid', true)
                ->whereNotNull('email')
                ->with('product')
                ->get();

            foreach ($licenses as $license) {
                $this->sendExpirationWarning($license, $days);
                $totalSent++;
            }
        }

        return $totalSent;
    }

    protected function processExpiredLicenses(Carbon $today): int
    {
        $expiredLicenses = ProductLicense::query()
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', $today)
            ->where('is_valid', true)
            ->with('product')
            ->get();

        foreach ($expiredLicenses as $license) {
            $this->logExpiredLicense($license);
        }

        return $expiredLicenses->count();
    }

    protected function processExpiredUpdates(Carbon $today): int
    {
        $expiredUpdates = ProductLicense::query()
            ->whereNotNull('updates_until')
            ->where('updates_until', '<', $today)
            ->where('is_valid', true)
            ->with('product')
            ->get();

        foreach ($expiredUpdates as $license) {
            $this->logUpdateSupportExpired($license);
        }

        return $expiredUpdates->count();
    }

    protected function sendExpirationWarning(ProductLicense $license, int $daysUntilExpiry): void
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown_product');

        $this->webhookService->dispatchLicenseExpirationWarning($license, $daysUntilExpiry);

        if (! $license->email) {
            return;
        }

        try {
            Mail::send(
                'plugins/license-manager::emails.license-expiring',
                [
                    'license' => $license,
                    'productName' => $productName,
                    'daysUntilExpiry' => $daysUntilExpiry,
                    'expiryDate' => $license->expires_at->format('Y-m-d'),
                ],
                function ($message) use ($license, $productName, $daysUntilExpiry): void {
                    $message->to($license->email)
                        ->subject(trans(
                            'plugins/license-manager::license-manager.emails.expiring_subject',
                            ['product_reference_id' => $productName, 'days' => $daysUntilExpiry]
                        ));
                }
            );

            ActivityLog::query()->create([
                'message' => trans('plugins/license-manager::license-manager.activity_log.expiration_warning_email_sent', [
                    'email' => e($license->email),
                    'license' => e($license->license_code),
                    'product' => e($productName),
                    'days' => $daysUntilExpiry,
                ]),
                'created_at' => Carbon::now(),
            ]);
        } catch (\Throwable) {
            // Silently fail for web requests
        }
    }

    protected function logExpiredLicense(ProductLicense $license): void
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown');

        $this->webhookService->dispatchLicenseExpired($license);

        ActivityLog::query()->create([
            'message' => trans('plugins/license-manager::license-manager.activity_log.license_expired', [
                'license' => e($license->license_code),
                'product' => e($productName),
                'date' => $license->expires_at->format('Y-m-d'),
            ]),
            'created_at' => Carbon::now(),
        ]);
    }

    protected function logUpdateSupportExpired(ProductLicense $license): void
    {
        $productName = $license->product?->name ?? trans('plugins/license-manager::license-manager.general.unknown');

        $this->webhookService->dispatchUpdateSupportExpired($license);

        ActivityLog::query()->create([
            'message' => trans('plugins/license-manager::license-manager.activity_log.license_updates_expired', [
                'license' => e($license->license_code),
                'product' => e($productName),
            ]),
            'created_at' => Carbon::now(),
        ]);
    }

    protected function processBlacklist(array $items, int $threshold, string $settingKey, string $type): int
    {
        $counts = array_count_values($items);
        $currentBlacklist = setting($settingKey, '[]');
        $currentBlacklist = $currentBlacklist ? Arr::flatten(json_decode($currentBlacklist, true)) : [];

        $newItems = [];

        foreach ($counts as $item => $count) {
            if ($count >= $threshold && ! in_array($item, $currentBlacklist, true)) {
                $newItems[] = $item;
                $currentBlacklist[] = $item;

                ActivityLog::query()->create([
                    'action' => trans('plugins/license-manager::license-manager.activity_log.blacklisted', [
                        'type' => $type,
                        'item' => $item,
                        'threshold' => $threshold,
                    ]),
                ]);
            }
        }

        if (! empty($newItems)) {
            setting()->set([$settingKey => json_encode($currentBlacklist)])->save();
        }

        return count($newItems);
    }
}
