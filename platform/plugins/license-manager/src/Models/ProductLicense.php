<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;
use Botble\LicenseManager\Tests\Factories\ProductLicenseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductLicense extends BaseModel
{
    /** @use HasFactory<ProductLicenseFactory> */
    use HasFactory;

    protected static function newFactory(): ProductLicenseFactory
    {
        return ProductLicenseFactory::new();
    }

    protected static function booted(): void
    {
        static::updated(function (self $license): void {
            if ($license->wasChanged('is_valid') && ! $license->is_valid) {
                $license->activations()
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });
    }

    protected $table = 'lm_licenses';

    protected $fillable = [
        'product_reference_id',
        'license_code',
        'type',
        'invoice',
        'is_envato',
        'customer_id',
        'email',
        'uses',
        'parallel_uses',
        'expires_at',
        'expiry_days',
        'updates_until',
        'support_until',
        'domains',
        'ips',
        'comments',
        'is_valid',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'updates_until' => 'date',
            'support_until' => 'date',
            'is_valid' => 'bool',
            'is_envato' => 'bool',
            'domains' => 'array',
            'ips' => 'array',
        ];
    }

    public function activations(): HasMany
    {
        return $this->hasMany(ProductActivation::class, 'license_code', 'license_code');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_reference_id', 'reference_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'client_id');
    }

    public function isBelongsToCustomer(Customer $customer): bool
    {
        return Str::lower($this->customer_id) !== Str::lower($customer->client_id);
    }

    public function scopeWithActivatedActivationsCount(Builder $query): Builder
    {
        return $query->withCount([
            'activations as activated_activations_count' => fn (Builder $query) => $query
                ->where('is_active', true),
        ]);
    }

    protected function activatedActivationsCount(): Attribute
    {
        return Attribute::get(function ($_, $attributes = []): string {
            if (! isset($attributes['activated_activations_count'])) {
                return '-';
            }

            $activatedCount = (int) $attributes['activated_activations_count'];
            $parallelUses = $attributes['parallel_uses'] ?? 0;

            return trans(
                'plugins/license-manager::license-manager.licenses.activated_count',
                ['activatedCount' => $activatedCount, 'parallelUses' => $parallelUses]
            );
        });
    }

    protected function parallelLeft(): Attribute
    {
        return Attribute::get(function ($_, $attributes = []): string {
            if (! isset($attributes['activated_activations_count'])) {
                return '-';
            }

            $activatedCount = (int) $attributes['activated_activations_count'];
            $parallelUses = $attributes['parallel_uses'] ?? 0;
            $parallelLeft = $parallelUses === null ? $parallelUses : $parallelUses - $activatedCount;

            return $parallelLeft === null ? '∞' : max($parallelLeft, 0);
        });
    }
}
