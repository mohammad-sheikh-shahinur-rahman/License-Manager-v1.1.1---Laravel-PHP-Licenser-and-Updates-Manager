<?php

namespace Botble\LicenseManager\Enums;

enum ApiKeyType: string
{
    case Internal = 'internal';

    case External = 'external';

    public static function labels(): array
    {
        $label = [];

        foreach (self::cases() as $case) {
            $label[$case->value] = $case->label();
        }

        return $label;
    }

    public function label(): string
    {
        return match($this) {
            self::External => trans('plugins/license-manager::license-manager.api_key.type_external'),
            self::Internal => trans('plugins/license-manager::license-manager.api_key.type_internal'),
            default => trans('plugins/license-manager::license-manager.api_key.type_unknown'),
        };
    }
}
