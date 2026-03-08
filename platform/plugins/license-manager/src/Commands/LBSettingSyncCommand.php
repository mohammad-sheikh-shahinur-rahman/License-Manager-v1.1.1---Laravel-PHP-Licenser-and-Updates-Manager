<?php

namespace Botble\LicenseManager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:lb:setting:sync', 'Sync LB Settings.')]
class LBSettingSyncCommand extends Command
{
    public function handle(): int
    {
        $this->components->info('Syncing LB settings...');

        $ips = old('lm_blacklisted_ips', setting('lm_blacklisted_ips')) ?: '[]';
        $ips = implode(',', Arr::flatten(json_decode($ips, true)));
        $domains = old('lm_blacklisted_domains', setting('lm_blacklisted_domains')) ?: '[]';
        $domains = implode(',', Arr::flatten(json_decode($domains, true)));

        $settings = [
            'failed_activation_logs' => setting('lm_add_entries_for_failed_activation_attempts', '1'),
            'failed_update_download_logs' => setting('lm_add_entries_for_failed_update_download_attempts', '1'),
            'auto_deactivate_activations' => setting('lm_deactivate_old_activations_on_new_activation', '0'),
            'auto_add_licensed_domain' => setting('lm_add_domain_of_first_activation_as_licensed_domain', '0'),
            'auto_domain_blacklist' => setting('lm_blacklist_domain_after_failed_attempts', '200'),
            'auto_ip_blacklist' => setting('lm_blacklist_ip_after_failed_attempts', '100'),
            'api_rate_limit_method' => setting('lm_requests_rate_limiting_method', 'ip_address'),
            'api_rate_limit' => setting('lm_requests_rate_limiting_period'),
            'blacklisted_ips' => $ips,
            'blacklisted_domains' => $domains,
        ];

        foreach ($settings as $key => $value) {
            DB::table('app_settings')->where('as_name', $key)->update([
                'as_value' => $value,
            ]);
        }

        $this->components->info('Done.');

        return self::SUCCESS;
    }
}
