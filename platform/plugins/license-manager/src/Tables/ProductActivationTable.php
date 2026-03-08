<?php

namespace Botble\LicenseManager\Tables;

use Botble\Base\Models\BaseModel;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Tables\Columns\LicenseCodeColumn;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\DateTimeColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\LinkableColumn;
use Illuminate\Http\JsonResponse;

class ProductActivationTable extends TableAbstract
{
    public function setup(): void
    {
        $this->pageLength = 25;

        $this->defaultSortColumn = 6;

        $this
            ->model(new ProductActivation())
            ->addActions([
                Action::make('activations.activate')
                    ->label(trans('plugins/license-manager::license-manager.activations.mark_as_active'))
                    ->color('warning')
                    ->icon('ti ti-check')
                    ->action()
                    ->route('lm.activations.activated-product-activations.store')
                    ->confirmation()
                    ->confirmationModalTitle(
                        trans('plugins/license-manager::license-manager.activations.activation_modal.title')
                    )
                    ->confirmationModalMessage(
                        trans('plugins/license-manager::license-manager.activations.activation_modal.description')
                    )
                    ->renderUsing(function (Action $action) {
                        /**
                         * @var ProductActivation $item
                         */
                        $item = $action->getItem();

                        if ($item->is_active || ! $item->is_valid) {
                            return '';
                        }

                        return null;
                    }),
                Action::make('activations.deactivate')
                    ->label(trans('plugins/license-manager::license-manager.activations.mask_as_inactive'))
                    ->color('warning')
                    ->icon('ti ti-x')
                    ->action('DELETE')
                    ->route('lm.activations.activated-product-activations.destroy')
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
                DeleteAction::make()->route('lm.activations.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('lm.activations.destroy')->beforeDispatch(
                    function (BaseModel $model, array $ids): void {
                        abort_if(ProductActivation::query()->whereIn('id', $ids)->where('is_active', true)->exists(), 403, 'You can not delete active product activations using bulk delete. Please delete one by one.');
                    }
                ),
            ])
            ->queryUsing(function ($query) {
                return $query
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
                    ->with(['customer', 'product'])
                    ->latest('activated_at');
            })
            ->onAjax(function (): JsonResponse {
                return $this->toJson(
                    $this
                        ->table
                        ->eloquent($this->query())
                        ->filter(function ($query) {
                            if ($keyword = $this->request->input('search.value')) {
                                $keyword = '%' . $keyword . '%';

                                return $query
                                    ->whereHas('product', function ($subQuery) use ($keyword) {
                                        return $subQuery->where('name', 'LIKE', $keyword);
                                    })
                                    ->orWhere('customer_id', 'LIKE', $keyword)
                                    ->orWhere('license_code', 'LIKE', $keyword)
                                    ->orWhere('url', 'LIKE', $keyword)
                                    ->orWhere('ip_address', 'LIKE', $keyword);
                            }

                            return $query;
                        })
                );
            });
    }

    public function columns(): array
    {
        return [
            FormattedColumn::make('product_reference_id')
                ->title(trans('plugins/license-manager::license-manager.activations.table.product'))
                ->orderable(false)
                ->searchable(false)
                ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->product?->name)
                ->withEmptyState(),
            FormattedColumn::make('customer_id')
                ->title(trans('plugins/license-manager::license-manager.activations.table.client'))
                ->copyable(),
            LicenseCodeColumn::make('license_code'),
            FormattedColumn::make('url')
                ->title(trans('plugins/license-manager::license-manager.activations.table.url'))
                ->orderable(false)
                ->searchable(false)
                ->copyable()
                ->limit(30),
            LinkableColumn::make('ip_address')
                ->title(trans('plugins/license-manager::license-manager.activations.table.ip_address'))
                ->externalLink()
                ->copyable()
                ->urlUsing(function (LinkableColumn $column) {
                    /**
                     * @var ProductActivation $item
                     */
                    $item = $column->getItem();

                    return 'https://ipinfo.io/' . $item->ip_address;
                })
                ->limit(20),
            DateTimeColumn::make('activated_at')
                ->title(trans('plugins/license-manager::license-manager.activations.table.activation_date')),
            FormattedColumn::make('is_active')
                ->title(trans('plugins/license-manager::license-manager.activations.table.status'))
                ->getValueUsing(function (FormattedColumn $column) {
                    return view('plugins/license-manager::product-activations.partials.status', [
                        'item' => $column->getItem(),
                    ])->render();
                }),
        ];
    }

    public function getFilters(): array
    {
        $filters = parent::getFilters();

        $products = Product::query()->pluck('name', 'reference_id')->all();

        return array_merge($filters, [
            'product_reference_id' => [
                'title' => trans('plugins/license-manager::license-manager.activations.filter.product'),
                'type' => 'select',
                'choices' => $products,
            ],
            'customer_id' => [
                'title' => trans('plugins/license-manager::license-manager.activations.filter.customer'),
                'type' => 'select-ajax',
                'validate' => 'required',
                'callback' => function (mixed $value = null): array {
                    $customerSelected = [];
                    if ($value && $customer = Customer::query()->find($value)) {
                        $customerSelected = [$customer->client_id => $customer->name];
                    }

                    return [
                        'url' => route('lm.customers.search'),
                        'selected' => $customerSelected,
                        'minimum-input' => 1,
                    ];
                },
            ],
            'is_active' => [
                'title' => trans('plugins/license-manager::license-manager.activations.filter.status'),
                'type' => 'select',
                'choices' => [
                    0 => trans('plugins/license-manager::license-manager.activations.inactive'),
                    1 => trans('plugins/license-manager::license-manager.activations.active'),
                ],
            ],
            'is_valid' => [
                'title' => trans('plugins/license-manager::license-manager.activations.filter.is_valid'),
                'type' => 'select',
                'choices' => [
                    0 => trans('core/base::base.no'),
                    1 => trans('core/base::base.yes'),
                ],
            ],
        ]);
    }
}
