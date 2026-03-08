<?php

namespace Botble\LicenseManager\Http\Requests\Api;

use Botble\Support\Http\Requests\Request;

class UpdateLatestRequest extends Request
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string'],
        ];
    }
}
