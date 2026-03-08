<?php

namespace Botble\LicenseManager\Enums;

use Botble\LicenseManager\Enums\Concerns\HasChoices;

enum ApiInternalScope: string
{
    use HasChoices;

    case ConnectionCheck = 'connection:check';

    case ProductsList = 'products:list';

    case ProductsCreate = 'products:create';

    case ProductsRead = 'products:read';

    case ProductsUpdate = 'products:update';

    case ProductsDelete = 'products:delete';

    case ProductsActivate = 'products:activate';

    case ProductsDeactivate = 'products:deactivate';

    case VersionsList = 'versions:list';

    case VersionsCreate = 'versions:create';

    case VersionsRead = 'versions:read';

    case VersionsUpdate = 'versions:update';

    case LicensesList = 'licenses:list';

    case LicensesCreate = 'licenses:create';

    case LicensesRead = 'licenses:read';

    case LicensesUpdate = 'licenses:update';

    case LicensesBlock = 'licenses:block';

    case LicensesUnblock = 'licenses:unblock';

    case ActivationsList = 'activations:list';

    case ActivationsActivate = 'activations:activate';

    case ActivationsDeactivate = 'activations:deactivate';

    case CustomersList = 'customers:list';

    case CustomersCreate = 'customers:create';

    case CustomersRead = 'customers:read';

    case CustomersUpdate = 'customers:update';

    public static function getScopeFromPath(string $path): ?self
    {
        return match ($path) {
            default => null,
        };
    }
}
