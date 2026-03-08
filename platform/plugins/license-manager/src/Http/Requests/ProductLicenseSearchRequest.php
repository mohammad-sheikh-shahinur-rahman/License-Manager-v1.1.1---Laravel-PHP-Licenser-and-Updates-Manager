<?php

namespace Botble\LicenseManager\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ProductLicenseSearchRequest extends Request
{
    public function rules(): array
    {
        return [
            'keyword' => ['required', 'string'],
        ];
    }
}
