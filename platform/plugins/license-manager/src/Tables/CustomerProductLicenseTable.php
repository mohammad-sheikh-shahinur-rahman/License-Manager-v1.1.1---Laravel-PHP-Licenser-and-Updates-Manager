<?php

namespace Botble\LicenseManager\Tables;

use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Support\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class CustomerProductLicenseTable extends ProductLicenseTable
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setView(Helper::viewPath('customer.table'))
            ->removeAllActions()
            ->removeHeaderActions(['create'])
            ->queryUsing(function ($query) {
                $customer = Auth::user();

                abort_unless($customer instanceof Customer, 403);

                $query
                    ->select(['id', 'license_code', 'customer_id', 'product_reference_id', 'created_at', 'parallel_uses', 'is_valid'])
                    ->where(function ($query) use ($customer): void {
                        if ($customer->client_id) {
                            $query->where('customer_id', $customer->client_id);
                        }

                        $query->orWhere('email', $customer->email);
                    })
                    ->with(['customer', 'product'])
                    ->withActivatedActivationsCount()
                    ->latest('created_at');

                return $query;
            });
    }

    public function buttons(): array
    {
        $url = setting('lm_get_more_licenses_url');

        if (! $url) {
            return [];
        }

        return [
            'get-more-licenses' => [
                'text' => Blade::render(
                    sprintf(
                        '<x-core::icon name="ti ti-cube-plus" /> %s',
                        trans('plugins/license-manager::license-manager.licenses.get_more_button')
                    )
                ),
                'link' => $url,
                'class' => 'empty-activities-logs-button btn-primary',
            ],
        ];
    }

    public function getFilters(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [];
    }
}
