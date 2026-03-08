<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardWidgetController extends BaseController
{
    public function licensesShare(BaseHttpResponse $response): BaseHttpResponse
    {
        $stats = $this->getLicenseShareStats();

        return $response->setData(
            view('plugins/license-manager::widgets.licenses-share', compact('stats'))->render()
        );
    }

    public function licensesActivationsChart(BaseHttpResponse $response): BaseHttpResponse
    {
        $chartData = $this->getMonthlyChartData();

        return $response->setData(
            view('plugins/license-manager::widgets.licenses-activations-chart', compact('chartData'))->render()
        );
    }

    public function recentActivities(BaseHttpResponse $response): BaseHttpResponse
    {
        $activities = DB::table('lm_activity_logs')
            ->where('created_at', '>', Carbon::now()->subHours(24))
            ->latest('created_at')
            ->limit(20)
            ->get();

        return $response->setData(
            view('plugins/license-manager::widgets.recent-activities', compact('activities'))->render()
        );
    }

    public function topCustomers(BaseHttpResponse $response): BaseHttpResponse
    {
        $customers = ProductLicense::query()
            ->select('customer_id', DB::raw('COUNT(*) as licenses_count'))
            ->whereNotNull('customer_id')
            ->where('customer_id', '!=', '')
            ->groupBy('customer_id')
            ->orderByDesc('licenses_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $customer = Customer::query()->where('client_id', $item->customer_id)->first();

                return [
                    'client_id' => $item->customer_id,
                    'name' => $customer?->name ?? $item->customer_id,
                    'email' => $customer?->email,
                    'customer_id' => $customer?->id,
                    'licenses_count' => $item->licenses_count,
                ];
            });

        return $response->setData(
            view('plugins/license-manager::widgets.top-customers', compact('customers'))->render()
        );
    }

    public function topProducts(BaseHttpResponse $response): BaseHttpResponse
    {
        $products = ProductLicense::query()
            ->select('product_reference_id', DB::raw('COUNT(*) as licenses_count'))
            ->whereNotNull('product_reference_id')
            ->groupBy('product_reference_id')
            ->orderByDesc('licenses_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $product = Product::query()->where('reference_id', $item->product_reference_id)->first();

                return [
                    'product_id' => $product?->id,
                    'product_pid' => $item->product_reference_id,
                    'name' => $product?->name ?? trans('plugins/license-manager::license-manager.general.unknown_product'),
                    'licenses_count' => $item->licenses_count,
                ];
            });

        return $response->setData(
            view('plugins/license-manager::widgets.top-products', compact('products'))->render()
        );
    }

    protected function getLicenseShareStats(): array
    {
        $licenses = ProductLicense::query()
            ->select(['license_code', 'is_valid', 'uses', 'parallel_uses', 'expires_at'])
            ->get();

        $valid = 0;
        $invalid = 0;
        $blocked = 0;

        foreach ($licenses as $license) {
            if (! $license->is_valid) {
                $blocked++;

                continue;
            }

            $activationCount = ProductActivation::query()
                ->where('license_code', $license->license_code)
                ->where('is_valid', true)
                ->count();

            $isExpired = $license->expires_at && Carbon::parse($license->expires_at)->isPast();
            $usesExhausted = $license->uses > 0 && $activationCount >= $license->uses;

            if ($isExpired || $usesExhausted) {
                $invalid++;
            } else {
                $valid++;
            }
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid,
            'blocked' => $blocked,
        ];
    }

    protected function getMonthlyChartData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $today = Carbon::now();
        $data = [];

        for ($date = $startOfMonth->copy(); $date <= $today; $date->addDay()) {
            $dateString = $date->format('Y-m-d');
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();

            $licensesCount = ProductLicense::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count();

            $activationsCount = ProductActivation::query()
                ->where('is_valid', true)
                ->whereBetween('activated_at', [$startOfDay, $endOfDay])
                ->count();

            $downloadsCount = DB::table('lm_update_downloads')
                ->where('is_valid', true)
                ->whereBetween('downloaded_at', [$startOfDay, $endOfDay])
                ->count();

            $data[] = [
                'date' => $dateString,
                'licenses' => $licensesCount,
                'activations' => $activationsCount,
                'downloads' => $downloadsCount,
            ];
        }

        return $data;
    }
}
