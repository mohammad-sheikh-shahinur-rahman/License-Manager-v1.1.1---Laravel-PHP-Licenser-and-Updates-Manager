<?php

namespace Botble\LegacyApi\Http\Controllers;

use Botble\LegacyApi\Http\Controllers\Concerns\LegacyResponse;
use Illuminate\Http\JsonResponse;

class LegacyConnectionController
{
    use LegacyResponse;

    public function __invoke(): JsonResponse
    {
        return $this->legacySuccess('Connection successful.');
    }
}
