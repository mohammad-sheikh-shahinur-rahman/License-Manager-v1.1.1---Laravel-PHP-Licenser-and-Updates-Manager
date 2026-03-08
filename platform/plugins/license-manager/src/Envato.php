<?php

namespace Botble\LicenseManager;

use Illuminate\Support\Facades\Http;

class Envato
{
    protected const URL = 'https://api.envato.com';

    protected ?string $personalToken;

    public function __construct()
    {
        $this->personalToken = setting('lm_envato_personal_token') ?: setting('envato_personal_token');
    }

    public function verifyPurchaseCode(string $purchaseCode): array|false
    {
        $response = Http::baseUrl(static::URL)
            ->withoutVerifying()
            ->withToken($this->personalToken)
            ->get('/v3/market/author/sale', ['code' => $purchaseCode]);

        if (! $response->ok()) {
            return false;
        }

        return $response->json();
    }
}
