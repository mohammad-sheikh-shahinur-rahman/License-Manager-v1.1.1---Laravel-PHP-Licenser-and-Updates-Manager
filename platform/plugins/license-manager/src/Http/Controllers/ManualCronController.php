<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Services\CronService;
use Illuminate\View\View;

class ManualCronController extends LicenseManagerController
{
    public function __construct(protected CronService $cronService)
    {
    }

    public function index(): View
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.tools.run_manual_cron'));

        return view('plugins/license-manager::tools.manual-cron');
    }

    public function run(BaseHttpResponse $response): BaseHttpResponse
    {
        $results = [];

        // Run license expirations
        $expirationResult = $this->cronService->processLicenseExpirations();
        $results[] = [
            'command' => trans('plugins/license-manager::license-manager.tools.process_license_expirations'),
            'output' => trans('plugins/license-manager::license-manager.tools.cron_result_expirations', [
                'warnings_sent' => $expirationResult['warnings_sent'],
                'expired_logged' => $expirationResult['expired_logged'],
                'updates_expired' => $expirationResult['updates_expired'],
            ]),
        ];

        // Run auto-blacklist
        $blacklistResult = $this->cronService->processAutoBlacklist();
        $results[] = [
            'command' => trans('plugins/license-manager::license-manager.tools.process_auto_blacklist'),
            'output' => trans('plugins/license-manager::license-manager.tools.cron_result_blacklist', [
                'domains_blacklisted' => $blacklistResult['domains_blacklisted'],
                'ips_blacklisted' => $blacklistResult['ips_blacklisted'],
            ]),
        ];

        return $response
            ->setMessage(trans('plugins/license-manager::license-manager.tools.cron_executed_successfully'))
            ->setData(['results' => $results]);
    }
}
