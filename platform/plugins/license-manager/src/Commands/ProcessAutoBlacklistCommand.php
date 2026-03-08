<?php

namespace Botble\LicenseManager\Commands;

use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\UpdateDownload;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'cms:license-manager:process-auto-blacklist', description: 'Auto-blacklist domains and IPs based on failed attempts')]
class ProcessAutoBlacklistCommand extends Command
{
    public function handle(): int
    {
        $domainThreshold = (int) setting('lm_blacklist_domain_after_failed_attempts', 0);
        $ipThreshold = (int) setting('lm_blacklist_ip_after_failed_attempts', 0);

        if ($domainThreshold <= 0 && $ipThreshold <= 0) {
            $this->info('Auto-blacklist is disabled (thresholds not set).');

            return self::SUCCESS;
        }

        $invalidDomains = [];
        $invalidIps = [];

        // Collect failed activations
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

        // Collect failed downloads
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

        $domainsBlacklisted = 0;
        $ipsBlacklisted = 0;

        // Process domain blacklisting
        if ($domainThreshold > 0 && ! empty($invalidDomains)) {
            $domainsBlacklisted = $this->processBlacklist(
                $invalidDomains,
                $domainThreshold,
                'lm_blacklisted_domains',
                'Domain'
            );
        }

        // Process IP blacklisting
        if ($ipThreshold > 0 && ! empty($invalidIps)) {
            $ipsBlacklisted = $this->processBlacklist(
                $invalidIps,
                $ipThreshold,
                'lm_blacklisted_ips',
                'IP'
            );
        }

        $this->components->info("Auto-blacklist complete: {$domainsBlacklisted} domains, {$ipsBlacklisted} IPs blacklisted.");

        return self::SUCCESS;
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
