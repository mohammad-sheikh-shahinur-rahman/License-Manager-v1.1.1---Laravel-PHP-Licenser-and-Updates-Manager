<?php

namespace Botble\LicenseManager\Tables;

use Botble\LicenseManager\Models\Customer;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\EmailColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\NameColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;

class CustomerTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(new Customer())
            ->addColumns([
                NameColumn::make()->route('lm.customers.edit'),
                FormattedColumn::make('client_id')->copyable(),
                EmailColumn::make()->copyable(),
                FormattedColumn::make('licences_count')
                    ->orderable(false)
                    ->searchable(false)
                    ->withEmptyState()
                    ->getValueUsing(function (FormattedColumn $column) {
                        $licenseCount = $column->getItem()->lb_licenses_count;

                        if ($licenseCount) {
                            return sprintf('<a href="%s">%s</a>', route('lm.licenses.index', ['customer_id' => $column->getItem()->client_id]), $licenseCount);
                        }

                        return 0;
                    }),
                CreatedAtColumn::make(),
                DateColumn::make('last_login_at')
                    ->title(trans('plugins/license-manager::customer.last_login_at'))
                    ->diffForHumans(),
            ])
            ->addActions([
                EditAction::make()->route('lm.customers.edit'),
                DeleteAction::make()->route('lm.customers.destroy'),
            ])
            ->addBulkActions([
                DeleteBulkAction::make()->permission('lm.customers.destroy'),
            ])
            ->addBulkChanges([
                NameBulkChange::make(),
            ])
            ->addHeaderAction(
                CreateHeaderAction::make()
                    ->route('lm.customers.create')
            )
            ->queryUsing(function ($query) {
                return $query
                    ->select(['id', 'client_id', 'name', 'email', 'created_at', 'last_login_at'])
                    ->withCount('lbLicenses')
                    ->latest();
            });
    }
}
