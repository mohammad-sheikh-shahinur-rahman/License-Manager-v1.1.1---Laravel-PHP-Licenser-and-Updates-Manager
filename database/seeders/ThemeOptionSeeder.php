<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Setting\Facades\Setting;
use Botble\Theme\Facades\ThemeOption;

class ThemeOptionSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('general');

        Setting::newQuery()->where('key', 'LIKE', ThemeOption::getOptionKey('%'))->delete();

        $options = [
            'site_title' => 'Botble License',
            'seo_description' => 'Manage your software licenses with ease. Activate, verify, and track licenses for your products.',
            'logo' => $this->filePath('general/logo.png'),
            'logo_dark' => $this->filePath('general/logo-dark.png'),
            'favicon' => $this->filePath('general/favicon.png'),
            'primary_color' => '#206bc4',
            'secondary_color' => '#6c7a91',
            'heading_color' => 'inherit',
            'text_color' => '#182433',
            'link_color' => '#206bc4',
            'link_hover_color' => '#1a569d',
            'primary_font' => 'Inter',
            'support_email' => 'support@botble.com',
            'documentation_url' => 'https://docs.botble.com',
            'social_facebook' => 'https://facebook.com/botaboratories',
            'social_twitter' => 'https://twitter.com/nicksaenzllc',
            'social_github' => 'https://github.com/botble',
            'terms_url' => 'https://policies.google.com/terms',
            'privacy_url' => 'https://policies.google.com/privacy',
            'copyright' => '© %Y Botble Technologies. All rights reserved.',
        ];

        Setting::set(ThemeOption::prepareFromArray($options));

        Setting::save();
    }
}
