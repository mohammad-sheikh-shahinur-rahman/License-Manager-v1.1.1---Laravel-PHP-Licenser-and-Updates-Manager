<?php

namespace Botble\LicenseManager\Tables;

use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Support\Helper;
use Botble\Table\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CustomerProductActivationTable extends ProductActivationTable
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setView(Helper::viewPath('customer.table'))
            ->removeAllActions()
            ->when(setting('lm_allow_customer_deactivation', true), function (): void {
                $this->addAction(
                    Action::make('activations.deactivate')
                        ->label(trans('plugins/license-manager::license-manager.activations.deactivate'))
                        ->color('warning')
                        ->icon('ti ti-x')
                        ->action('DELETE')
                        ->route('lm.customer.activated-product-activations.destroy')
                        ->confirmation()
                        ->confirmationModalTitle(
                            trans('plugins/license-manager::license-manager.activations.deactivation_modal.title')
                        )
                        ->confirmationModalMessage(
                            trans('plugins/license-manager::license-manager.activations.deactivation_modal.description')
                        )
                        ->renderUsing(function (Action $action) {
                            /**
                             * @var ProductActivation $item
                             */
                            $item = $action->getItem();

                            if (! $item->is_active) {
                                return '';
                            }

                            return null;
                        }),
                );
            })
            ->queryUsing(function ($query) {
                $customer = Auth::user();

                abort_unless($customer instanceof Customer, 403);

                $query
                    ->select([
                        'id',
                        'license_code',
                        'customer_id',
                        'ip_address',
                        'activated_at',
                        'product_reference_id',
                        'url',
                        'is_active',
                        'is_valid',
                    ])
                    ->where(function ($query) use ($customer): void {
                        if ($customer->client_id) {
                            $query->where('customer_id', $customer->client_id);
                        }

                        $query->orWhereHas('license', function ($query) use ($customer): void {
                            if ($customer->client_id) {
                                $query->where('customer_id', $customer->client_id);
                            }

                            $query->orWhere('email', $customer->email);
                        });
                    })
                    ->with(['product'])
                    ->latest('activated_at');

                return $query;
            })
            ->removeAllBulkActions()
            ->removeActions(['delete', 'activations.activate']);
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
