<?php

namespace Botble\LicenseManager\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\UpdateDownload;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\DateTimeColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\LinkableColumn;
use Botble\Table\Columns\StatusColumn;
use Illuminate\Contracts\Database\Eloquent\Builder;

class UpdateDownloadTable extends TableAbstract
{
    public function setup(): void
    {
        $this->pageLength = 25;

        $this->defaultSortColumn = 5;

        $this
            ->model(new UpdateDownload())
            ->queryUsing(fn (Builder $query): Builder => $query->with(['downloadProduct', 'downloadVersion']))
            ->addAction(DeleteAction::make()->route('lm.update-downloads.destroy'))
            ->addBulkAction(DeleteBulkAction::make())
            ->addColumns([
                FormattedColumn::make('product_reference_id')
                    ->label(trans('plugins/license-manager::license-manager.licenses.product'))
                    ->searchable(false)
                    ->orderable(false)
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->downloadProduct->name),
                FormattedColumn::make('version')
                    ->searchable(false)
                    ->orderable(false)
                    ->label(trans('plugins/license-manager::license-manager.update_downloads.version'))
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->downloadVersion->version)
                    ->withEmptyState(),
                FormattedColumn::make('url')
                    ->label(trans('core/base::tables.url'))
                    ->limit(30)
                    ->copyable(),
                LinkableColumn::make('ip_address')
                    ->label(trans('plugins/license-manager::license-manager.update_downloads.ip'))
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
                DateTimeColumn::make('downloaded_at')
                    ->label(trans('plugins/license-manager::license-manager.update_downloads.download_date')),
                StatusColumn::make('is_valid')
                    ->renderUsing(function (FormattedColumn $column) {
                        if ($column->getItem()->is_valid) {
                            return BaseHelper::renderBadge(trans('plugins/license-manager::license-manager.activations.valid'), 'success');
                        }

                        return BaseHelper::renderBadge(trans('plugins/license-manager::license-manager.activations.invalid'), 'danger');
                    }),
            ]);
    }
}
