<?php

namespace Botble\LicenseManager\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdateDownload extends BaseModel
{
    protected $table = 'lm_update_downloads';

    protected $fillable = [
        'download_id',
        'product_reference_id',
        'version_id',
        'url',
        'ip_address',
        'is_valid',
        'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
            'is_valid' => 'bool',
        ];
    }

    public function downloadProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_reference_id', 'reference_id')
            ->withDefault();
    }

    public function downloadVersion(): BelongsTo
    {
        return $this->belongsTo(ProductVersion::class, 'version_id', 'version_id')
            ->withDefault();
    }
}
