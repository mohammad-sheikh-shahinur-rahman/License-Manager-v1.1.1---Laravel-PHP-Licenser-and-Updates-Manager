<?php

namespace Botble\LicenseManager\Http\Resources;

use Botble\LicenseManager\Models\Customer;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Customer */
class CustomerJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'client_id' => $this->client_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
