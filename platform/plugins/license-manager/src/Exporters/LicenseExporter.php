<?php

namespace Botble\LicenseManager\Exporters;

use Botble\DataSynchronize\Exporter\ExportColumn;
use Botble\DataSynchronize\Exporter\ExportCounter;
use Botble\DataSynchronize\Exporter\Exporter;
use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LicenseExporter extends Exporter
{
    protected int|string|null $productId = null;

    protected ?bool $isValid = null;

    protected ?string $startDate = null;

    protected ?string $endDate = null;

    public function setProductId(int|string|null $productId): static
    {
        $this->productId = $productId;

        return $this;
    }

    public function setIsValid(?bool $isValid): static
    {
        $this->isValid = $isValid;

        return $this;
    }

    public function setDateRange(?string $startDate, ?string $endDate): static
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;

        return $this;
    }

    public function getLabel(): string
    {
        return trans('plugins/license-manager::license-manager.export.name');
    }

    public function columns(): array
    {
        return [
            ExportColumn::make('license_code'),
            ExportColumn::make('product_name'),
            ExportColumn::make('product_reference_id'),
            ExportColumn::make('type'),
            ExportColumn::make('invoice'),
            ExportColumn::make('is_envato')->boolean(),
            ExportColumn::make('customer_email'),
            ExportColumn::make('customer_id'),
            ExportColumn::make('email'),
            ExportColumn::make('uses'),
            ExportColumn::make('parallel_uses'),
            ExportColumn::make('expires_at'),
            ExportColumn::make('expiry_days'),
            ExportColumn::make('updates_until'),
            ExportColumn::make('support_until'),
            ExportColumn::make('domains'),
            ExportColumn::make('ips'),
            ExportColumn::make('comments'),
            ExportColumn::make('is_valid')->boolean(),
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->productId) {
            $query->whereHas('product', fn ($q) => $q->where('id', $this->productId));
        }

        if ($this->isValid !== null) {
            $query->where('is_valid', $this->isValid);
        }

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->startDate));
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->endDate));
        }
    }

    public function counters(): array
    {
        $query = ProductLicense::query();

        $this->applyFilters($query);

        return [
            ExportCounter::make()
                ->label(trans('plugins/license-manager::license-manager.export.total'))
                ->value($query->count()),
        ];
    }

    public function hasDataToExport(): bool
    {
        return ProductLicense::query()->exists();
    }

    public function collection(): Collection
    {
        $query = ProductLicense::query()
            ->with(['product', 'customer']);

        $this->applyFilters($query);

        return $query->get()
            ->transform(fn (ProductLicense $license) => [
                'license_code' => $license->license_code,
                'product_name' => $license->product?->name,
                'product_reference_id' => $license->product_reference_id,
                'type' => $license->type,
                'invoice' => $license->invoice,
                'is_envato' => $license->is_envato,
                'customer_email' => $license->customer?->email,
                'customer_id' => $license->customer_id,
                'email' => $license->email,
                'uses' => $license->uses,
                'parallel_uses' => $license->parallel_uses,
                'expires_at' => $license->expires_at?->toDateString(),
                'expiry_days' => $license->expiry_days,
                'updates_until' => $license->updates_until?->toDateString(),
                'support_until' => $license->support_until?->toDateString(),
                'domains' => is_array($license->domains) ? implode(',', $license->domains) : $license->domains,
                'ips' => is_array($license->ips) ? implode(',', $license->ips) : $license->ips,
                'comments' => $license->comments,
                'is_valid' => $license->is_valid,
            ]);
    }

    protected function getView(): string
    {
        return 'plugins/license-manager::licenses.export';
    }
}
