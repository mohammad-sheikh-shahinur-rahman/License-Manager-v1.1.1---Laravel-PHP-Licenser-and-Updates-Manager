<?php

namespace Botble\LicenseManager\Http\Requests\Api;

use Botble\Support\Http\Requests\Request;

class LicenseDeactivateRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string'],
            'license_data' => ['required', 'string'],
            'client_name' => ['required', 'string'],
        ];
    }
}
