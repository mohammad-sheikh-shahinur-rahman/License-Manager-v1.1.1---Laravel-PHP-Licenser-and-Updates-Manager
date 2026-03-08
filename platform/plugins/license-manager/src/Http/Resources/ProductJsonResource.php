<?php

namespace Botble\LicenseManager\Http\Resources;

use Botble\LicenseManager\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reference_id' => $this->reference_id,
            'envato_id' => $this->envato_id,
            'description' => $this->description,
            'license_update' => $this->license_update,
            'serve_latest_updates' => $this->serve_latest_updates,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
