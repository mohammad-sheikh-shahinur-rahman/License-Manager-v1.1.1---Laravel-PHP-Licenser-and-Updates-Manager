<?php

namespace Botble\LicenseManager\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Botble\LicenseManager\Models\Customer
 */
class CustomerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->client_id,
            'name' => $this->name,
        ];
    }
}
