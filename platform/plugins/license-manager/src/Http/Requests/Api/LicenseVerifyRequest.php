<?php

namespace Botble\LicenseManager\Http\Requests\Api;

use Botble\Support\Http\Requests\Request;

class LicenseVerifyRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string'],
            // Support license_data (modern), license_file (legacy CMS), or license_code (revoke)
            'license_data' => ['required_without_all:license_file,license_code', 'string'],
            'license_file' => ['required_without_all:license_data,license_code', 'string'],
            'license_code' => ['required_without_all:license_data,license_file', 'string'],
            'client_name' => ['nullable', 'string'],
        ];
    }
}
