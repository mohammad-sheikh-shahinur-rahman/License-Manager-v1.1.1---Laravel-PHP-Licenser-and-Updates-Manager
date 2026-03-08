<?php

namespace Botble\LicenseManager\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;

class ActivityLogTable extends TableAbstract
{
    public function setup(): void
    {
        $this->pageLength = 25;

        $this
            ->model(new ActivityLog())
            ->addAction(DeleteAction::make()->route('lm.activity-logs.destroy'))
            ->addBulkAction(DeleteBulkAction::make())
            ->addColumns([
                IdColumn::make('id'),
                FormattedColumn::make('message')
                    ->title(trans('plugins/license-manager::license-manager.activity_log.description'))
                    ->renderUsing(function (FormattedColumn $column, $value) {
                        if (str_contains($value, 'blocked')) {
                            return sprintf('<span class="text-danger">%s</span>', BaseHelper::clean($value));
                        }

                        return BaseHelper::clean($value);
                    }),
                DateColumn::make('created_at')->label(trans('plugins/license-manager::license-manager.activity_log.date')),
            ])
            ->queryUsing(function ($query) {
                return $query
                    ->select(['id', 'message', 'created_at'])
                    ->latest('created_at');
            });
    }
}
