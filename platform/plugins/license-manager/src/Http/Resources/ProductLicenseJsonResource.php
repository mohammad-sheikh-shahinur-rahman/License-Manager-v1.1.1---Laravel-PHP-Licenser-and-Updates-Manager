<?php

namespace Botble\LicenseManager\Http\Resources;

use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductLicense */
class ProductLicenseJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product_reference_id' => $this->product_reference_id,
            'license_code' => $this->license_code,
            'type' => $this->type,
            'is_envato' => $this->is_envato,
            'invoice' => $this->invoice,
            'customer_id' => $this->customer_id,
            'email' => $this->email,
            'comments' => $this->comments,
            'ips' => $this->ips,
            'domains' => $this->domains,
            'support_until' => $this->support_until,
            'updates_until' => $this->updates_until,
            'expires_at' => $this->expires_at,
            'expiry_days' => $this->expiry_days,
            'uses' => $this->uses,
            'parallel_uses' => $this->parallel_uses,
            'is_valid' => $this->is_valid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
