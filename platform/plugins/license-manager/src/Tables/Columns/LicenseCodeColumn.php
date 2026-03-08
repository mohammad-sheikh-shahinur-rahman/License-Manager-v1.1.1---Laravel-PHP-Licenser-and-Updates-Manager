<?php

namespace Botble\LicenseManager\Tables\Columns;

use Botble\Table\Columns\FormattedColumn;
use Illuminate\Support\Str;

class LicenseCodeColumn extends FormattedColumn
{
    public static function make(array|string $data = [], string $name = ''): static
    {
        return parent::make($data ?: 'license_code', $name)
            ->title(trans('plugins/license-manager::license-manager.activations.table.using_license'))
            ->nowrap()
            ->fontMono()
            ->copyable()
            ->getValueUsing(function (LicenseCodeColumn $column, $value): string {
                return Str::of($value)->substr(-8)->prepend('*****')->toString();
            });
    }
}
