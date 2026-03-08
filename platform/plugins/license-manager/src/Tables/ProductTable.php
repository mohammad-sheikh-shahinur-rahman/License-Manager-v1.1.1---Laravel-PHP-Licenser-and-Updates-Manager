<?php

namespace Botble\LicenseManager\Tables;

use Botble\LicenseManager\Models\Product;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\Action;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Actions\ViewAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ProductTable extends TableAbstract
{
    public function setup(): void
    {
        $this->pageLength = 25;

        $this->defaultSortColumnName = 'last_release_date';

        $this
            ->model(new Product())
            ->queryUsing(fn (Builder $query): Builder => $query->with('latestVersion'))
            ->addHeaderAction(CreateHeaderAction::make()->route('lm.products.create'))
            ->displayActionsAsDropdownWhenActionsMoresThan(5)
            ->addActions([
                Action::make('add_version')
                    ->route('lm.products.versions.create')
                    ->label(trans('plugins/license-manager::license-manager.product.add_version'))
                    ->icon('ti ti-plus')
                    ->color('success'),
                Action::make('manage_versions')
                    ->route('lm.products.versions.index')
                    ->label(trans('plugins/license-manager::license-manager.product.manage_versions'))
                    ->icon('ti ti-versions')
                    ->color('info'),
                ViewAction::make()->route('lm.products.show'),
                EditAction::make()->route('lm.products.edit'),
                DeleteAction::make()->route('lm.products.destroy'),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->addColumns([
                FormattedColumn::make('reference_id')
                    ->label(trans('plugins/license-manager::license-manager.product.unique_id'))
                    ->fontMono()
                    ->copyable()
                    ->nowrap(),
                NameColumn::make('name')
                    ->label(trans('core/base::tables.name'))
                    ->urlUsing(fn (FormattedColumn $column) => route('lm.products.edit', $column->getItem()->id)),
                FormattedColumn::make('latest_version')
                    ->label(trans('plugins/license-manager::license-manager.product.table.latest_version'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->latestVersion?->version)
                    ->withEmptyState(),
                FormattedColumn::make('last_release_date')
                    ->label(trans('plugins/license-manager::license-manager.product.table.last_release_date'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->latestVersion?->released_at?->toDateString())
                    ->withEmptyState(),
                FormattedColumn::make('is_active')
                    ->label(trans('core/base::tables.status'))
                    ->renderUsing(function (FormattedColumn $column) {
                        $product = $column->getItem();

                        return view('plugins/license-manager::products.partials.status', compact('product'));
                    }),
            ]);
    }
}
