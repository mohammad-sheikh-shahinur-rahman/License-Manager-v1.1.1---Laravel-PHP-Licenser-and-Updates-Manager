<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Setting\Facades\Setting;

class SettingSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('general');

        Setting::set([
            'admin_title' => 'License Manager',
            'admin_logo' => $this->filePath('general/logo-dark.png'),
            'admin_favicon' => $this->filePath('general/favicon.png'),
            'theme' => 'license',
            'show_admin_bar' => false,
            'time_zone' => 'UTC',
            'locale' => 'en',

            'enable_page_visual_builder' => false,

            // Social login - Envato (fake credentials for demo)
            'social_login_enable' => true,
            'social_login_envato_enable' => true,
            'social_login_envato_app_id' => 'demo-envato-app-id',
            'social_login_envato_app_secret' => 'demo-envato-app-secret',
        ]);

        Setting::save();
    }
}
