<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;
use Botble\LicenseManager\LicenseManager;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductActivation extends BaseModel
{
    protected $table = 'lm_activations';

    protected $fillable = [
        'product_reference_id',
        'customer_id',
        'license_code',
        'url',
        'ip_address',
        'activated_at',
        'user_agent',
        'is_valid',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'is_valid' => 'bool',
            'is_active' => 'bool',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_reference_id', 'reference_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'client_id');
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(ProductLicense::class, 'license_code', 'license_code');
    }

    protected function domain(): Attribute
    {
        return Attribute::get(function ($_, array $attributes = []): ?string {
            if (! empty($attributes['url'])) {
                return parse_url($attributes['url'], PHP_URL_HOST);
            }

            return null;
        });
    }

    protected function normalizedDomain(): Attribute
    {
        return Attribute::get(function (): ?string {
            $domain = $this->domain;

            if (! $domain) {
                return null;
            }

            return app(LicenseManager::class)->normalizeDomain($domain);
        });
    }
}
