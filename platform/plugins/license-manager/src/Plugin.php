<?php

namespace Botble\LicenseManager;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('lm_activations');
        Schema::dropIfExists('lm_update_downloads');
        Schema::dropIfExists('lm_licenses');
        Schema::dropIfExists('lm_product_versions');
        Schema::dropIfExists('lm_products');
        Schema::dropIfExists('lm_activity_logs');
        Schema::dropIfExists('lm_customers');
        Schema::dropIfExists('lm_customer_password_reset_tokens');
        Schema::dropIfExists('lm_api_keys');

        Setting::delete([
            'lm_customer_recaptcha_enabled',
            'lm_customer_math_captcha_enabled',
        ]);
    }
}
