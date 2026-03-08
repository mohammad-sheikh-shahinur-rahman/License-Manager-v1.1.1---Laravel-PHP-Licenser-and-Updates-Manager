<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;
use Botble\LicenseManager\Tests\Factories\ProductVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVersion extends BaseModel
{
    /** @use HasFactory<ProductVersionFactory> */
    use HasFactory;

    protected static function newFactory(): ProductVersionFactory
    {
        return ProductVersionFactory::new();
    }

    protected $table = 'lm_product_versions';

    protected $fillable = [
        'version_id',
        'product_reference_id',
        'version',
        'released_at',
        'summary',
        'changelog',
        'main_file',
        'sql_file',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'date',
            'is_active' => 'bool',
        ];
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(UpdateDownload::class, 'version_id', 'version_id');
    }

    public function versionProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_reference_id', 'reference_id')
            ->withDefault();
    }

    public function deleteVersionFiles(): void
    {
        $path = storage_path('app/version-files/' . $this->product_reference_id);

        if (! empty($this->sql_file)) {
            $sqlFile = $path . '/' . $this->sql_file;
            is_readable($sqlFile) && unlink($sqlFile);
        }

        if (! empty($this->main_file)) {
            $mainFile = $path . '/' . $this->main_file;
            is_readable($mainFile) && unlink($mainFile);
        }
    }

    protected static function booted(): void
    {
        static::deleted(function (ProductVersion $productVersion): void {
            $productVersion->downloads()->delete();
            $productVersion->deleteVersionFiles();
        });
    }
}
