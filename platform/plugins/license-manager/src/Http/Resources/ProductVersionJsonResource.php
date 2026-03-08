<?php

namespace Botble\LicenseManager\Http\Resources;

use Botble\LicenseManager\Models\ProductVersion;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductVersion */
class ProductVersionJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'version_id' => $this->version_id,
            'product_reference_id' => $this->product_reference_id,
            'version' => $this->version,
            'released_at' => $this->released_at,
            'summary' => $this->summary,
            'changelog' => $this->changelog,
            'main_file' => $this->main_file,
            'sql_file' => $this->sql_file,
            'is_active' => $this->is_active,
        ];
    }
}
