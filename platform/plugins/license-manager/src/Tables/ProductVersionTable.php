<?php

namespace Botble\LicenseManager\Tables;

use Botble\Base\Facades\BaseHelper;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductVersion;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\Actions\ViewAction;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\DateColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder;

class ProductVersionTable extends TableAbstract
{
    protected Product $product;

    public function setup(): void
    {
        $this
            ->model(new ProductVersion())
            ->addHeaderAction(
                CreateHeaderAction::make()
                    ->route('lm.products.versions.create', [$this->request()->route('product')])
            )
            ->queryUsing(fn (Builder $query) => $query->withCount('downloads')->where('product_reference_id', $this->product->reference_id))
            ->addColumns([
                Column::make('version'),
                DateColumn::make('released_at'),
                FormattedColumn::make('summary')
                    ->withEmptyState()
                    ->limit(30),
                FormattedColumn::make('downloads')
                    ->orderable(false)
                    ->searchable(false)
                    ->getValueUsing(fn (FormattedColumn $column) => $column->getItem()->downloads_count ?: null)
                    ->withEmptyState(),
                FormattedColumn::make('is_active')
                    ->label(trans('core/base::tables.status'))
                    ->renderUsing(function (FormattedColumn $column) {
                        if ($column->getItem()->is_active) {
                            return BaseHelper::renderBadge(trans('plugins/license-manager::license-manager.product.version.published'), 'success');
                        }

                        return BaseHelper::renderBadge(trans('plugins/license-manager::license-manager.product.version.unpublished'), 'secondary');
                    }),
            ]);
    }

    public function getRowActions(): array
    {
        return [
            ViewAction::make()->route('lm.products.versions.show', [$this->product]),
            EditAction::make()->route('lm.products.versions.edit', [$this->product]),
            DeleteAction::make()->route('lm.products.versions.destroy', [$this->product]),
        ];
    }

    public function product(Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
