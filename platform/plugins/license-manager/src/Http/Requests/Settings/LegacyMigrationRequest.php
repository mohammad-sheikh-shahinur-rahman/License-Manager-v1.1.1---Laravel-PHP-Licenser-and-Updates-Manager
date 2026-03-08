<?php

namespace Botble\LicenseManager\Http\Requests\Settings;

use Botble\Support\Http\Requests\Request;

class LegacyMigrationRequest extends Request
{
    public function rules(): array
    {
        return [
            'lm_legacy_encryption_key' => ['nullable', 'string', 'max:255'],
        ];
    }
}
