<?php

namespace Botble\LicenseManager\Enums\Concerns;

trait HasChoices
{
    public static function choices(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn ($case) => $case->mapChoice()
        )->all();
    }

    public function mapChoice(): array
    {
        return [$this->value => $this->value];
    }
}
