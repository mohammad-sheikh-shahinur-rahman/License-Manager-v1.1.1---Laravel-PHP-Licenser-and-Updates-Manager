<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;
use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Tests\Factories\ApiKeyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Date;

class ApiKey extends BaseModel
{
    /** @use HasFactory<ApiKeyFactory> */
    use HasFactory;

    protected $table = 'lm_api_keys';

    protected static function newFactory(): ApiKeyFactory
    {
        return ApiKeyFactory::new();
    }

    protected $guarded = [];

    protected $casts = [
        'type' => ApiKeyType::class,
        'scopes' => 'array',
        'revoked' => 'bool',
        'special' => 'bool',
        'expires_at' => 'datetime',
    ];

    public function scopeWhereNotExpired(Builder $query): Builder
    {
        $query
            ->where('revoked', false)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', Date::now());
            });

        return $query;
    }
}
