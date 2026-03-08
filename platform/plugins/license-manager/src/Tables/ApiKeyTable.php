<?php

namespace Botble\LicenseManager\Tables;

use Botble\Base\Facades\Html;
use Botble\LicenseManager\Models\ApiKey;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\YesNoColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;

class ApiKeyTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(ApiKey::class)
            ->addHeaderAction(CreateHeaderAction::make()->route('lm.api-keys.create'))
            ->addBulkAction(DeleteBulkAction::make())
            ->addActions([
                EditAction::make()->route('lm.api-keys.edit'),
                DeleteAction::make()->route('lm.api-keys.destroy'),
            ])
            ->addColumns([
                FormattedColumn::make('key')
                    ->label(trans('plugins/license-manager::license-manager.api_key.key'))
                    ->fontMono()
                    ->copyable()
                    ->nowrap()
                    ->mask(length: -8),
                FormattedColumn::make('type')
                    ->label(trans('plugins/license-manager::license-manager.api_key.type'))
                    ->getValueUsing(function (FormattedColumn $column) {
                        return $column->getItem()->type->label();
                    }),
                FormattedColumn::make('scopes')
                    ->label(trans('plugins/license-manager::license-manager.api_key.scopes'))
                    ->renderUsing(function (FormattedColumn $column) {
                        $scopes = $column->getItem()->scopes;

                        if (empty($scopes)) {
                            return '-';
                        }

                        return implode('<br />', array_map(
                            fn ($scope) => Html::tag('code', $scope),
                            $scopes
                        ));
                    }),
                DateColumn::make('expires_at'),
                YesNoColumn::make('special')
                    ->trans('plugins/license-manager::license-manager.api_key.special'),
                YesNoColumn::make('revoked')
                    ->trans('plugins/license-manager::license-manager.api_key.revoked'),
                DateColumn::make('created_at'),
            ]);
    }
}
