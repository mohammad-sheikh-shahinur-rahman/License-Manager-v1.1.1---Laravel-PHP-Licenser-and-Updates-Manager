<?php

namespace Botble\LicenseManager\Tables;

use Botble\Base\Facades\Html;
use Botble\DataSynchronize\Table\HeaderActions\ExportHeaderAction;
use Botble\DataSynchronize\Table\HeaderActions\ImportHeaderAction;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Tables\BulkActions\LicenseBlockBulkAction;
use Botble\LicenseManager\Tables\BulkActions\LicenseDeleteBulkAction;
use Botble\LicenseManager\Tables\BulkActions\LicenseUnblockBulkAction;
use Botble\LicenseManager\Tables\Columns\LicenseCodeColumn;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Http\JsonResponse;

class ProductLicenseTable extends TableAbstract
{
    public function setup(): void
    {
        $this->pageLength = 25;
        $this->displayActionsAsDropdownWhenActionsMoresThan(5);

        $this
            ->model(new ProductLicense())
            ->addActions([
                EditAction::make()->route('lm.licenses.edit'),
                Action::make('licenses.block')
                    ->label(trans('plugins/license-manager::license-manager.licenses.block'))
                    ->icon('ti ti-lock')
                    ->color('warning')
                    ->action()
                    ->route('lm.licenses.blocked-licenses.store')
                    ->confirmation()
                    ->confirmationModalTitle(
                        trans('plugins/license-manager::license-manager.licenses.block_modal.title')
                    )
                    ->confirmationModalMessage(
                        trans('plugins/license-manager::license-manager.licenses.block_modal.description')
                    )
                    ->renderUsing(function (Action $action) {
                        /**
                         * @var ProductLicense $item
                         */
                        $item = $action->getItem();

                        if (! $item->is_valid) {
                            return '';
                        }

                        return null;
                    }),
                Action::make('licenses.unblock')
                    ->label(trans('plugins/license-manager::license-manager.licenses.unblock'))
                    ->icon('ti ti-lock-open')
                    ->action('DELETE')
                    ->route('lm.licenses.blocked-licenses.destroy')
                    ->confirmation()
                    ->confirmationModalTitle(
                        trans('plugins/license-manager::license-manager.licenses.unblock_modal.title')
                    )
                    ->confirmationModalMessage(
                        trans('plugins/license-manager::license-manager.licenses.unblock_modal.description')
                    )
                    ->renderUsing(function (Action $action) {
                        /**
                         * @var ProductLicense $item
                         */
                        $item = $action->getItem();

                        if ($item->is_valid) {
                            return '';
                        }

                        return null;
                    }),
                Action::make('licenses.send-email')
                    ->label(trans('plugins/license-manager::license-manager.emails.send_license_email'))
                    ->icon('ti ti-mail')
                    ->color('info')
                    ->action()
                    ->route('lm.licenses.send-email')
                    ->confirmation()
                    ->confirmationModalTitle(trans('plugins/license-manager::license-manager.emails.send_license_email'))
                    ->confirmationModalMessage(trans('plugins/license-manager::license-manager.emails.send_license_email_confirm'))
                    ->renderUsing(function (Action $action) {
                        /**
                         * @var ProductLicense $item
                         */
                        $item = $action->getItem();

                        if (! $item->email) {
                            return '';
                        }

                        return null;
                    }),
                DeleteAction::make()->route('lm.licenses.destroy'),
            ])
            ->addHeaderAction(CreateHeaderAction::make()->route('lm.licenses.create'))
            ->addHeaderActions([
                ExportHeaderAction::make()
                    ->route('tools.data-synchronize.export.licenses.index')
                    ->permission('lm.licenses.export'),
                ImportHeaderAction::make()
                    ->route('tools.data-synchronize.import.licenses.index')
                    ->permission('lm.licenses.import'),
            ])
            ->queryUsing(function ($query) {
                return $query
                    ->select(['id', 'license_code', 'customer_id', 'email', 'product_reference_id', 'created_at', 'parallel_uses', 'is_valid'])
                    ->with([
                        'customer:id,name,client_id',
                        'product:id,reference_id,name',
                    ])
                    ->withActivatedActivationsCount()
                    ->when($this->request->input('customer_id'), fn ($q, $customerId) => $q->where('customer_id', $customerId))
                    ->when($this->request->input('product_reference_id'), fn ($q, $productReferenceId) => $q->where('product_reference_id', $productReferenceId))
                    ->latest('created_at');
            })
            ->onAjax(function (): JsonResponse {
                return $this->toJson(
                    $this
                        ->table
                        ->eloquent($this->query())
                        ->addColumn('activated_activations_count', function (ProductLicense $productLicense) {
                            return Html::tag('strong', $productLicense->activated_activations_count, ['class' => 'text-primary']);
                        })
                        ->editColumn('parallel_left', function (ProductLicense $productLicense) {
                            return Html::tag('strong', $parallelLeft = $productLicense->parallel_left, [
                                'class' => $parallelLeft > 0 ? 'text-primary' : 'text-danger',
                            ]);
                        })
                        ->editColumn('is_valid', function (ProductLicense $productLicense) {
                            return view('plugins/license-manager::product-licenses.partials.status', [
                                'is_active' => $productLicense->is_valid,
                            ])->render();
                        })
                        ->filter(function ($query) {
                            if ($keyword = $this->request->input('search.value')) {
                                $keyword = '%' . $keyword . '%';

                                return $query->where(function ($q) use ($keyword): void {
                                    $q->where('license_code', 'LIKE', $keyword)
                                        ->orWhere('customer_id', 'LIKE', $keyword)
                                        ->orWhereHas('product', fn ($subQuery) => $subQuery->where('name', 'LIKE', $keyword));
                                });
                            }

                            return $query;
                        })
                );
            });
    }

    public function columns(): array
    {
        return [
            LicenseCodeColumn::make(),
            FormattedColumn::make('product_reference_id')
                ->orderable(false)
                ->searchable(false)
                ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->product?->name)
                ->withEmptyState(),
            FormattedColumn::make('customer_id')
                ->title(trans('plugins/license-manager::license-manager.licenses.customer'))
                ->orderable(false)
                ->searchable(false)
                ->getValueUsing(function (FormattedColumn $column) {
                    $item = $column->getItem();
                    $customer = $item->customer;

                    return $customer ? $customer->client_id : $item->customer_id;
                })
                ->withEmptyState()
                ->copyable(),
            CreatedAtColumn::make('created_at'),
            Column::make('activated_activations_count')
                ->title(trans('plugins/license-manager::license-manager.licenses.activations'))
                ->alignCenter()
                ->orderable(false)
                ->searchable(false),
            Column::make('parallel_left')
                ->title(trans('plugins/license-manager::license-manager.licenses.uses_left'))
                ->alignCenter()
                ->orderable(false)
                ->searchable(false),
            Column::make('is_valid')
                ->title(trans('plugins/license-manager::license-manager.licenses.status')),
        ];
    }

    public function getFilters(): array
    {
        $filters = parent::getFilters();

        $customers = Customer::query()
            ->select(['client_id', 'name'])
            ->orderBy('name')
            ->pluck('name', 'client_id')
            ->all();

        $products = Product::query()
            ->select(['reference_id', 'name'])
            ->orderBy('name')
            ->pluck('name', 'reference_id')
            ->all();

        return array_merge(
            $filters,
            [
                'customer_id' => [
                    'title' => trans('plugins/license-manager::license-manager.licenses.customer'),
                    'type' => 'select',
                    'choices' => $customers,
                ],
                'product_reference_id' => [
                    'title' => trans('plugins/license-manager::license-manager.licenses.product'),
                    'type' => 'select',
                    'choices' => $products,
                ],
            ],
        );
    }

    public function bulkActions(): array
    {
        return [
            LicenseBlockBulkAction::make()->permission('lm.licenses.edit'),
            LicenseUnblockBulkAction::make()->permission('lm.licenses.edit'),
            LicenseDeleteBulkAction::make()->permission('lm.licenses.destroy'),
        ];
    }
}
