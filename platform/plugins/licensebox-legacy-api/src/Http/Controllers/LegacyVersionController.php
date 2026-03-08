<?php

namespace Botble\LegacyApi\Http\Controllers;

use Botble\LicenseManager\Actions\ProductVersions\StoreProductVersion;
use Botble\LicenseManager\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LegacyVersionController
{
    public function __invoke(Request $request, StoreProductVersion $storeProductVersion): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'string'],
            'version' => ['required', 'string'],
            'released' => ['nullable', 'date'],
            'version_status' => ['nullable', 'string', 'in:on,off'],
            'delete_old_versions' => ['nullable', 'string', 'in:yes,no'],
            'changelog' => ['nullable', 'string'],
            'main_file' => ['required', 'file', 'mimes:zip,gz'],
        ]);

        $product = Product::query()->where('reference_id', $request->input('product_id'))->first();

        if (! $product) {
            return new JsonResponse([
                'status' => false,
                'message' => trans('plugins/licensebox-legacy-api::legacy-api.errors.product_not_found'),
            ], 404);
        }

        $data = [
            'version' => $request->input('version'),
            'released_at' => $request->input('released', Carbon::now()->toDateString()),
            'changelog' => $request->input('changelog', ''),
            'is_active' => $request->input('version_status', 'on') === 'on' ? 1 : 0,
            'delete_old_versions' => $request->input('delete_old_versions', 'no'),
            'main_file' => $request->file('main_file'),
        ];

        if ($request->hasFile('sql_file')) {
            $data['sql_file'] = $request->file('sql_file');
        }

        try {
            $productVersion = $storeProductVersion->handle($product, $data);
        } catch (ValidationException $e) {
            return new JsonResponse([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        return new JsonResponse([
            'status' => true,
            'message' => trans('plugins/licensebox-legacy-api::legacy-api.errors.version_created'),
            'data' => [
                'vid' => $productVersion->version_id,
                'version' => $productVersion->version,
                'release_date' => $productVersion->released_at,
            ],
        ], 200);
    }
}
