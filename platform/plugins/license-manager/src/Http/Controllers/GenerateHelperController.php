<?php

namespace Botble\LicenseManager\Http\Controllers;

use Botble\LicenseManager\Models\ApiKey;
use Botble\LicenseManager\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GenerateHelperController extends LicenseManagerController
{
    public function index(Request $request): View
    {
        $this->pageTitle(trans('plugins/license-manager::license-manager.tools.generate_helper'));

        $helperType = $request->input('helper_type', 'external');

        $products = Product::query()
            ->select(['reference_id', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'reference_id')
            ->all();

        $externalApiKeys = ApiKey::query()
            ->where('type', 'external')
            ->where('revoked', false)
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->pluck('key', 'key')
            ->all();

        $internalApiKeys = ApiKey::query()
            ->where('type', 'internal')
            ->where('revoked', false)
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', Carbon::now());
            })
            ->pluck('key', 'key')
            ->all();

        $generatedCode = null;

        if ($request->isMethod('post')) {
            if ($helperType === 'external') {
                $request->validate([
                    'product_id' => ['required', 'exists:lm_products,reference_id'],
                    'api_key' => ['required', 'exists:lm_api_keys,key'],
                    'verify_type' => ['required', 'in:envato,non_envato'],
                    'verification_period' => ['required', 'integer', 'min:0'],
                ]);

                $product = Product::query()->where('reference_id', $request->input('product_id'))->first();
                $latestVersion = $product?->versions()->where('is_active', true)->latest('created_at')->first();

                $generatedCode = $this->generateExternalHelper(
                    $request->input('product_id'),
                    $request->input('api_key'),
                    $request->input('verify_type'),
                    $latestVersion?->version ?? 'v1.0.0',
                    (int) $request->input('verification_period')
                );
            } else {
                $request->validate([
                    'api_key' => ['required', 'exists:lm_api_keys,key'],
                ]);

                $generatedCode = $this->generateInternalHelper($request->input('api_key'));
            }
        }

        return view('plugins/license-manager::tools.generate-helper', compact(
            'helperType',
            'products',
            'externalApiKeys',
            'internalApiKeys',
            'generatedCode'
        ));
    }

    protected function generateExternalHelper(
        string $productId,
        string $apiKey,
        string $verifyType,
        string $currentVersion,
        int $verificationPeriod
    ): string {
        $templatePath = __DIR__ . '/../../../resources/stubs/external-api-helper.stub';

        if (! file_exists($templatePath)) {
            return '// Template file not found';
        }

        $template = file_get_contents($templatePath);

        $replacements = [
            '{PRODUCT_ID}' => $productId,
            '{API_URL}' => rtrim(url('/'), '/') . '/',
            '{API_KEY}' => $apiKey,
            '{CURRENT_VERSION}' => $currentVersion,
            '{VERIFY_TYPE}' => $verifyType,
            '{VERIFICATION_PERIOD}' => $verificationPeriod,
            '{RANDOM_SESSION_KEY}' => substr(md5((string) microtime()), 0, 15),
        ];

        return strtr($template, $replacements);
    }

    protected function generateInternalHelper(string $apiKey): string
    {
        $templatePath = __DIR__ . '/../../../resources/stubs/internal-api-helper.stub';

        if (! file_exists($templatePath)) {
            return '// Template file not found';
        }

        $template = file_get_contents($templatePath);

        $replacements = [
            '{API_URL}' => rtrim(url('/'), '/') . '/',
            '{API_KEY}' => $apiKey,
        ];

        return strtr($template, $replacements);
    }
}
