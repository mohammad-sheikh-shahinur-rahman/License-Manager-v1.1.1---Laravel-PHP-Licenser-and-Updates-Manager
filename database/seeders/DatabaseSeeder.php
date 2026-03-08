<?php

namespace Database\Seeders;

use Botble\ACL\Database\Seeders\UserSeeder;
use Botble\Base\Supports\BaseSeeder;
use Botble\LicenseManager\Database\Seeders\LicenseManagerSeeder;

class DatabaseSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->prepareRun();

        $this->call([
            UserSeeder::class,
            SettingSeeder::class,
            PageSeeder::class,
            ThemeOptionSeeder::class,
            LicenseManagerSeeder::class,
        ]);

        $this->finished();
    }
}
