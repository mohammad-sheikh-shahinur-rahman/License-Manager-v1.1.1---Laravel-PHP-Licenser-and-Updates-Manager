<?php

namespace Botble\LicenseManager\Http\Middleware\Concerns;

use Botble\LicenseManager\Enums\ApiExternalScope;
use Botble\LicenseManager\Enums\ApiInternalScope;
use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

trait HasApiKey
{
    protected function findApiKey(string $key, ApiKeyType $type): ?ApiKey
    {
        return ApiKey::query()
            ->where('key', $key)
            ->where('type', $type)
            ->whereNotExpired()
            ->first();
    }

    protected function invalidApiKeyResponse(): Response
    {
        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.validation.invalid_api_key'),
        ], Response::HTTP_UNAUTHORIZED);
    }

    protected function checkScope(Request $request, ApiKey $apiKey, ApiKeyType $type): bool
    {
        if ($apiKey->special) {
            return true;
        }

        $requestPath = $request->path();

        // Try modern path format (api/external/... or api/internal/...)
        $prefix = $type == ApiKeyType::External ? 'api/external/' : 'api/internal/';
        if (Str::startsWith($requestPath, $prefix)) {
            $path = Str::after($requestPath, $prefix);
        } else {
            // Legacy path format (api/activate_license, etc.)
            $path = Str::after($requestPath, 'api/');
        }

        $scope = $type == ApiKeyType::External
            ? ApiExternalScope::getScopeFromPath($path)
            : ApiInternalScope::getScopeFromPath($path);

        return $scope && in_array($scope->value, $apiKey->scopes ?? [], true);
    }

    protected function forbiddenScopeResponse(): Response
    {
        return new JsonResponse([
            'message' => trans('plugins/license-manager::license-manager.api.validation.forbidden_scope'),
        ], Response::HTTP_FORBIDDEN);
    }
}
