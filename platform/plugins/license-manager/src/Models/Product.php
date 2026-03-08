<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;
use Botble\LicenseManager\Tests\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends BaseModel
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    protected $table = 'lm_products';

    protected $fillable = [
        'reference_id',
        'envato_id',
        'name',
        'description',
        'license_update',
        'serve_latest_updates',
        'is_active',
        'default_license_type',
        'default_uses',
        'default_parallel_uses',
        'default_expiry_days',
        'default_updates_until_days',
        'default_support_until_days',
        'default_comments',
    ];

    protected function casts(): array
    {
        return [
            'license_update' => 'bool',
            'serve_latest_updates' => 'bool',
            'is_active' => 'bool',
        ];
    }

    protected function url(): Attribute
    {
        return Attribute::get(
            function ($_, $attributes = []): ?string {
                if (empty($attributes['envato_id'])) {
                    return null;
                }

                return sprintf('https://themeforest.net/item/x/%s', $attributes['envato_id']);
            }
        )->shouldCache();
    }

    public function productActivations(): HasMany
    {
        return $this->hasMany(ProductActivation::class, 'product_reference_id', 'reference_id');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(ProductVersion::class, 'product_reference_id', 'reference_id')
            ->latest('id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProductVersion::class, 'product_reference_id', 'reference_id');
    }
}
