<?php

namespace Botble\LicenseManager\Http\Requests\Api;

use Botble\Support\Http\Requests\Request;

class UpdateCheckRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string'],
            'current_version' => ['required', 'string'],
        ];
    }
}
