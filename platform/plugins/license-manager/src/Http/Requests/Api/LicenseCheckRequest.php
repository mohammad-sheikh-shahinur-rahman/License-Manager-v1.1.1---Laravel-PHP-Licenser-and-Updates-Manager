<?php

namespace Botble\LicenseManager\Http\Requests\Api;

use Botble\Support\Http\Requests\Request;

class LicenseCheckRequest extends Request
{
    public function rules(): array
    {
        return [
            'purchase_code' => ['required', 'string'],
        ];
    }
}
