<?php

namespace Botble\LicenseManager\Http\Controllers\Customer;

use Botble\Base\Http\Controllers\BaseController;
use Botble\LicenseManager\Models\CustomerActivityLog;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Support\Helper;

class DashboardController extends BaseController
{
    public function __invoke()
    {
        $this->pageTitle(trans('plugins/license-manager::customer.dashboard'));

        $customer = auth('lm_customer')->user();

        $licensesCount = ProductLicense::query()
            ->where(function ($query) use ($customer): void {
                if ($customer->client_id) {
                    $query->where('customer_id', $customer->client_id);
                }

                $query->orWhere('email', $customer->email);
            })
            ->count();

        $clientIdCondition = fn ($q) => $customer->client_id
            ? $q->where('customer_id', $customer->client_id)
            : $q->whereRaw('1 = 0');

        $activeActivationsCount = ProductActivation::query()
            ->where($clientIdCondition)
            ->where('is_active', true)
            ->count();

        $inactiveActivationsCount = ProductActivation::query()
            ->where($clientIdCondition)
            ->where('is_active', false)
            ->count();

        $activityLogs = CustomerActivityLog::query()
            ->where(fn ($q) => $customer->client_id ? $q->where('customer_id', $customer->client_id) : $q->whereRaw('1 = 0'))
            ->latest()
            ->limit(10)
            ->get();

        return view(Helper::viewPath('customer.dashboard'), compact('licensesCount', 'activeActivationsCount', 'inactiveActivationsCount', 'activityLogs'));
    }
}
