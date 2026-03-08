<?php

namespace Botble\LicenseManager\Importers;

use Botble\DataSynchronize\Contracts\Importer\WithMapping;
use Botble\DataSynchronize\Importer\ImportColumn;
use Botble\DataSynchronize\Importer\Importer;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LicenseImporter extends Importer implements WithMapping
{
    private array $productCache = [];

    private array $customerCache = [];

    public function chunkSize(): int
    {
        return 100;
    }

    public function getLabel(): string
    {
        return trans('plugins/license-manager::license-manager.import.name');
    }

    public function columns(): array
    {
        return [
            ImportColumn::make('license_code')
                ->rules(['required', 'string', 'max:500']),
            ImportColumn::make('product_name')
                ->rules(['nullable', 'string', 'max:250']),
            ImportColumn::make('product_reference_id')
                ->rules(['nullable', 'string', 'max:250']),
            ImportColumn::make('type')
                ->rules(['nullable', 'string', 'max:100']),
            ImportColumn::make('invoice')
                ->rules(['nullable', 'string', 'max:250']),
            ImportColumn::make('is_envato')
                ->boolean(),
            ImportColumn::make('customer_email')
                ->rules(['nullable', 'email', 'max:250']),
            ImportColumn::make('customer_id')
                ->rules(['nullable', 'string', 'max:250']),
            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'max:250']),
            ImportColumn::make('uses')
                ->rules(['nullable', 'integer', 'min:0']),
            ImportColumn::make('parallel_uses')
                ->rules(['nullable', 'integer', 'min:0']),
            ImportColumn::make('expires_at')
                ->nullable(),
            ImportColumn::make('expiry_days')
                ->rules(['nullable', 'integer', 'min:0']),
            ImportColumn::make('updates_until')
                ->nullable(),
            ImportColumn::make('support_until')
                ->nullable(),
            ImportColumn::make('domains')
                ->nullable(),
            ImportColumn::make('ips')
                ->nullable(),
            ImportColumn::make('comments')
                ->nullable(),
            ImportColumn::make('is_valid')
                ->boolean(),
        ];
    }

    public function map(mixed $row): array
    {
        if (empty($row['product_reference_id']) && ! empty($row['product_name'])) {
            $name = trim($row['product_name']);
            $row['product_reference_id'] = $this->productCache[$name]
                ??= Product::query()->where('name', $name)->value('reference_id');
        }

        if (empty($row['customer_id']) && ! empty($row['customer_email'])) {
            $email = trim($row['customer_email']);
            $row['customer_id'] = $this->customerCache[$email]
                ??= Customer::query()->where('email', $email)->value('client_id');
        }

        $row['domains'] = $this->parseCommaSeparated($row['domains'] ?? null);
        $row['ips'] = $this->parseCommaSeparated($row['ips'] ?? null);

        $row['expires_at'] = $this->parseDate($row['expires_at'] ?? null);
        $row['updates_until'] = $this->parseDate($row['updates_until'] ?? null);
        $row['support_until'] = $this->parseDate($row['support_until'] ?? null);

        unset($row['product_name'], $row['customer_email']);

        return $row;
    }

    public function handle(array $data): int
    {
        $count = 0;

        foreach ($data as $row) {
            if (empty($row['license_code']) || empty($row['product_reference_id'])) {
                continue;
            }

            ProductLicense::query()->updateOrCreate(
                ['license_code' => $row['license_code']],
                collect($row)->except('license_code')->filter(fn ($v) => $v !== null && $v !== '')->all()
            );

            $count++;
        }

        return $count;
    }

    public function examples(): array
    {
        return [
            [
                'license_code' => 'ABCD-1234-EFGH-5678',
                'product_name' => 'My Product',
                'product_reference_id' => '',
                'type' => 'regular',
                'invoice' => 'INV-001',
                'is_envato' => 'No',
                'customer_email' => 'customer@example.com',
                'customer_id' => '',
                'email' => 'customer@example.com',
                'uses' => '0',
                'parallel_uses' => '1',
                'expires_at' => '2027-12-31',
                'expiry_days' => '365',
                'updates_until' => '2027-12-31',
                'support_until' => '2027-06-30',
                'domains' => 'example.com,app.example.com',
                'ips' => '',
                'comments' => 'Imported license',
                'is_valid' => 'Yes',
            ],
        ];
    }

    public function getDownloadExampleUrl(): ?string
    {
        return route('tools.data-synchronize.import.licenses.download-example');
    }

    public function getValidateUrl(): string
    {
        return route('tools.data-synchronize.import.licenses.validate');
    }

    public function getImportUrl(): string
    {
        return route('tools.data-synchronize.import.licenses.store');
    }

    public function getExportUrl(): ?string
    {
        return Auth::user() && Auth::user()->hasPermission('lm.licenses.export')
            ? route('tools.data-synchronize.export.licenses.index')
            : null;
    }

    protected function parseCommaSeparated(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        return array_map('trim', explode(',', $value));
    }

    protected function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
