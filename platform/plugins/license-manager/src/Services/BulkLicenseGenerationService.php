<?php

namespace Botble\LicenseManager\Services;

use Botble\LicenseManager\Actions\LicenseCode\GenerateLicenseCode;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkLicenseGenerationService
{
    /**
     * @return Collection<int, ProductLicense>
     */
    public function generate(Product $product, int $quantity, array $overrides = []): Collection
    {
        $data = $this->mergeDefaults($product, $overrides);
        $data['is_envato'] = ! empty($product->envato_id);
        $generator = GenerateLicenseCode::make();
        $licenses = collect();

        DB::transaction(function () use ($licenses, $data, $quantity, $generator): void {
            for ($i = 0; $i < $quantity; $i++) {
                $data['license_code'] = $generator->handle();
                $licenses->push(ProductLicense::query()->create($data));
            }
        });

        ActivityLog::log(trans(
            'plugins/license-manager::license-manager.activity_log.licenses_bulk_created_by_admin',
            ['count' => $licenses->count(), 'product' => e($product->name)]
        ));

        return $licenses;
    }

    protected function mergeDefaults(Product $product, array $overrides): array
    {
        $defaults = [
            'product_reference_id' => $product->reference_id,
            'type' => $product->default_license_type,
            'uses' => $product->default_uses ?? 0,
            'parallel_uses' => $product->default_parallel_uses,
            'expiry_days' => $product->default_expiry_days,
            'comments' => $product->default_comments,
        ];

        $merged = $defaults;
        foreach ($overrides as $key => $value) {
            if ($value !== null && $value !== '') {
                $merged[$key] = $value;
            }
        }

        return $this->computeDates($merged, $product);
    }

    protected function computeDates(array $data, Product $product): array
    {
        $now = now();

        if (empty($data['expires_at']) && ! empty($data['expiry_days'])) {
            $data['expires_at'] = $now->copy()->addDays($data['expiry_days']);
        }

        if (empty($data['updates_until']) && ! empty($product->default_updates_until_days)) {
            $data['updates_until'] = $now->copy()->addDays($product->default_updates_until_days);
        }

        if (empty($data['support_until']) && ! empty($product->default_support_until_days)) {
            $data['support_until'] = $now->copy()->addDays($product->default_support_until_days);
        }

        return $data;
    }
}
