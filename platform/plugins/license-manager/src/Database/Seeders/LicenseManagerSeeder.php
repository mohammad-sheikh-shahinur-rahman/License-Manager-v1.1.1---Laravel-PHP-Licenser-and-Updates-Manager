<?php

namespace Botble\LicenseManager\Database\Seeders;

use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Models\ActivityLog;
use Botble\LicenseManager\Models\ApiKey;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\CustomerActivityLog;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductActivation;
use Botble\LicenseManager\Models\ProductLicense;
use Botble\LicenseManager\Models\ProductVersion;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LicenseManagerSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('lm_products')) {
            return;
        }

        Setting::set([
            'admin_appearance_layout' => 'horizontal',
            'admin_appearance_container_width' => 'container-fluid',
        ])->save();

        $this->seedEncryptionKey();

        $this->createProducts();
        $this->createCustomers();
        $this->createLicenses();
        $this->createApiKeys();
        $this->createActivityLogs();
        $this->createCustomerActivityLogs();
    }

    protected function createProducts(): void
    {
        $products = [
            [
                'reference_id' => 'BOTBLE-CMS',
                'envato_id' => '16928182',
                'name' => 'Botble CMS',
                'description' => 'Laravel CMS - Pair any design with CMS in no time',
                'license_update' => true,
                'serve_latest_updates' => true,
                'is_active' => true,
                'versions' => [
                    ['version' => '1.0.0', 'summary' => 'Initial release'],
                    ['version' => '1.1.0', 'summary' => 'Bug fixes and improvements'],
                    ['version' => '1.2.0', 'summary' => 'New features added'],
                    ['version' => '2.0.0', 'summary' => 'Major update with breaking changes'],
                ],
            ],
            [
                'reference_id' => 'FLEX-HOME',
                'envato_id' => '21705196',
                'name' => 'Flex Home',
                'description' => 'Laravel Real Estate Multilingual System',
                'license_update' => true,
                'serve_latest_updates' => true,
                'is_active' => true,
                'versions' => [
                    ['version' => '1.0.0', 'summary' => 'Initial release'],
                    ['version' => '1.5.0', 'summary' => 'Added property comparison'],
                    ['version' => '2.0.0', 'summary' => 'New dashboard design'],
                ],
            ],
            [
                'reference_id' => 'MARTFURY',
                'envato_id' => '29856498',
                'name' => 'Martfury',
                'description' => 'Laravel Ecommerce - Pair any design with CMS in no time',
                'license_update' => true,
                'serve_latest_updates' => true,
                'is_active' => true,
                'versions' => [
                    ['version' => '1.0.0', 'summary' => 'Initial release'],
                    ['version' => '1.3.0', 'summary' => 'Added multi-vendor support'],
                ],
            ],
            [
                'reference_id' => 'SHOFY',
                'envato_id' => '45003000',
                'name' => 'Shofy',
                'description' => 'Laravel Multipurpose Ecommerce',
                'license_update' => true,
                'serve_latest_updates' => true,
                'is_active' => true,
                'versions' => [
                    ['version' => '1.0.0', 'summary' => 'Initial release'],
                ],
            ],
            [
                'reference_id' => 'FARMART',
                'envato_id' => '34719755',
                'name' => 'Farmart',
                'description' => 'Laravel Ecommerce for grocery, food & organic',
                'license_update' => true,
                'serve_latest_updates' => false,
                'is_active' => true,
                'versions' => [
                    ['version' => '1.0.0', 'summary' => 'Initial release'],
                    ['version' => '1.2.0', 'summary' => 'Performance improvements'],
                ],
            ],
        ];

        foreach ($products as $productData) {
            $versions = $productData['versions'] ?? [];
            unset($productData['versions']);

            $product = Product::query()->updateOrCreate(
                ['reference_id' => $productData['reference_id']],
                $productData
            );

            foreach ($versions as $index => $versionData) {
                ProductVersion::query()->updateOrCreate(
                    [
                        'product_reference_id' => $product->reference_id,
                        'version' => $versionData['version'],
                    ],
                    [
                        'version_id' => Str::slug($product->reference_id . '-' . $versionData['version']),
                        'product_reference_id' => $product->reference_id,
                        'version' => $versionData['version'],
                        'summary' => $versionData['summary'],
                        'changelog' => $this->generateChangelog($versionData['version']),
                        'released_at' => Carbon::now()->subDays(count($versions) - $index)->subMonths(rand(0, 6)),
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    protected function createCustomers(): void
    {
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'client_id' => 'CUST-001',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'client_id' => 'CUST-002',
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'client_id' => 'CUST-003',
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice@example.com',
                'client_id' => 'CUST-004',
            ],
            [
                'name' => 'Demo Customer',
                'email' => 'customer@botble.com',
                'client_id' => 'CUST-DEMO',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::query()->updateOrCreate(
                ['client_id' => $customerData['client_id']],
                array_merge($customerData, [
                    'password' => Hash::make('12345678'),
                ])
            );
        }
    }

    protected function createLicenses(): void
    {
        $licenses = [
            // Botble CMS licenses
            [
                'product_reference_id' => 'BOTBLE-CMS',
                'license_code' => '550e8400-e29b-41d4-a716-446655440001',
                'type' => 'regular',
                'customer_id' => 'CUST-001',
                'email' => 'john@example.com',
                'parallel_uses' => 3,
                'expires_at' => Carbon::now()->addYear(),
                'updates_until' => Carbon::now()->addYear(),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://example.com', 'ip_address' => '192.168.1.100'],
                    ['url' => 'https://staging.example.com', 'ip_address' => '192.168.1.101'],
                ],
            ],
            [
                'product_reference_id' => 'BOTBLE-CMS',
                'license_code' => '550e8400-e29b-41d4-a716-446655440002',
                'type' => 'extended',
                'customer_id' => 'CUST-002',
                'email' => 'jane@example.com',
                'parallel_uses' => 5,
                'expires_at' => null, // Perpetual
                'updates_until' => Carbon::now()->addYears(2),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://jane-site.com', 'ip_address' => '10.0.0.50'],
                ],
            ],
            [
                'product_reference_id' => 'BOTBLE-CMS',
                'license_code' => '550e8400-e29b-41d4-a716-446655440003',
                'type' => 'regular',
                'customer_id' => 'CUST-003',
                'email' => 'bob@example.com',
                'parallel_uses' => 1,
                'expires_at' => Carbon::now()->subMonths(2),
                'updates_until' => Carbon::now()->subMonths(2),
                'is_valid' => false,
                'activations' => [],
            ],

            // Flex Home licenses
            [
                'product_reference_id' => 'FLEX-HOME',
                'license_code' => '6ba7b810-9dad-11d1-80b4-00c04fd430c1',
                'type' => 'regular',
                'customer_id' => 'CUST-001',
                'email' => 'john@example.com',
                'parallel_uses' => 2,
                'expires_at' => Carbon::now()->addMonths(6),
                'updates_until' => Carbon::now()->addMonths(6),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://realestate.example.com', 'ip_address' => '172.16.0.10'],
                ],
            ],

            // Martfury licenses
            [
                'product_reference_id' => 'MARTFURY',
                'license_code' => '6ba7b811-9dad-11d1-80b4-00c04fd430c2',
                'type' => 'extended',
                'customer_id' => 'CUST-004',
                'email' => 'alice@example.com',
                'parallel_uses' => 10,
                'expires_at' => null,
                'updates_until' => Carbon::now()->addYears(3),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://shop1.alice.com', 'ip_address' => '203.0.113.1'],
                    ['url' => 'https://shop2.alice.com', 'ip_address' => '203.0.113.2'],
                    ['url' => 'https://shop3.alice.com', 'ip_address' => '203.0.113.3'],
                ],
            ],

            // Demo customer licenses (customer@botble.com)
            [
                'product_reference_id' => 'BOTBLE-CMS',
                'license_code' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a11',
                'type' => 'extended',
                'customer_id' => 'CUST-DEMO',
                'email' => 'customer@botble.com',
                'parallel_uses' => 5,
                'expires_at' => null, // Perpetual
                'updates_until' => Carbon::now()->addYears(2),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://demo-cms.botble.com', 'ip_address' => '103.45.67.89'],
                    ['url' => 'https://staging-cms.botble.com', 'ip_address' => '103.45.67.90'],
                    ['url' => 'https://dev-cms.botble.com', 'ip_address' => '127.0.0.1'],
                    ['url' => 'https://old-site.botble.com', 'ip_address' => '103.45.67.88', 'is_active' => false],
                    ['url' => 'https://deprecated.botble.com', 'ip_address' => '103.45.67.87', 'is_active' => false],
                ],
            ],
            [
                'product_reference_id' => 'FLEX-HOME',
                'license_code' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a12',
                'type' => 'regular',
                'customer_id' => 'CUST-DEMO',
                'email' => 'customer@botble.com',
                'parallel_uses' => 3,
                'expires_at' => Carbon::now()->addYear(),
                'updates_until' => Carbon::now()->addYear(),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://realestate.botble.com', 'ip_address' => '103.45.67.91'],
                ],
            ],
            [
                'product_reference_id' => 'MARTFURY',
                'license_code' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a13',
                'type' => 'extended',
                'customer_id' => 'CUST-DEMO',
                'email' => 'customer@botble.com',
                'parallel_uses' => 10,
                'expires_at' => null,
                'updates_until' => Carbon::now()->addYears(3),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://shop.botble.com', 'ip_address' => '103.45.67.92'],
                    ['url' => 'https://marketplace.botble.com', 'ip_address' => '103.45.67.93'],
                    ['url' => 'https://old-shop.botble.com', 'ip_address' => '103.45.67.96', 'is_active' => false],
                ],
            ],
            [
                'product_reference_id' => 'SHOFY',
                'license_code' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a14',
                'type' => 'regular',
                'customer_id' => 'CUST-DEMO',
                'email' => 'customer@botble.com',
                'parallel_uses' => 2,
                'expires_at' => Carbon::now()->addMonths(6),
                'updates_until' => Carbon::now()->addMonths(6),
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://shofy-demo.botble.com', 'ip_address' => '103.45.67.94'],
                ],
            ],
            [
                'product_reference_id' => 'FARMART',
                'license_code' => 'a0eebc99-9c0b-4ef8-bb6d-6bb9bd380a15',
                'type' => 'regular',
                'customer_id' => 'CUST-DEMO',
                'email' => 'customer@botble.com',
                'parallel_uses' => 2,
                'expires_at' => Carbon::now()->addMonths(3),
                'updates_until' => Carbon::now()->subDays(5), // Updates expired
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://grocery.botble.com', 'ip_address' => '103.45.67.95'],
                ],
            ],

            // Farmart licenses
            [
                'product_reference_id' => 'FARMART',
                'license_code' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                'type' => 'regular',
                'customer_id' => 'CUST-002',
                'email' => 'jane@example.com',
                'parallel_uses' => 2,
                'expires_at' => Carbon::now()->addMonths(8),
                'updates_until' => Carbon::now()->subDays(10), // Updates expired
                'is_valid' => true,
                'activations' => [
                    ['url' => 'https://grocery.jane.com', 'ip_address' => '198.51.100.50'],
                ],
            ],
        ];

        foreach ($licenses as $licenseData) {
            $activations = $licenseData['activations'] ?? [];
            unset($licenseData['activations']);

            $license = ProductLicense::query()->updateOrCreate(
                ['license_code' => $licenseData['license_code']],
                $licenseData
            );

            foreach ($activations as $activationData) {
                ProductActivation::query()->updateOrCreate(
                    [
                        'license_code' => $license->license_code,
                        'url' => $activationData['url'],
                    ],
                    [
                        'product_reference_id' => $license->product_reference_id,
                        'customer_id' => $license->customer_id,
                        'license_code' => $license->license_code,
                        'url' => $activationData['url'],
                        'ip_address' => $activationData['ip_address'],
                        'activated_at' => Carbon::now()->subDays(rand(1, 90)),
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'is_valid' => $activationData['is_valid'] ?? true,
                        'is_active' => $activationData['is_active'] ?? true,
                    ]
                );
            }
        }
    }

    protected function createApiKeys(): void
    {
        $apiKeys = [
            [
                'key' => 'ext_demo_api_key_for_testing_purposes',
                'type' => ApiKeyType::External,
                'scopes' => ['connection:check', 'license:activate', 'license:verify', 'license:deactivate', 'license:check', 'update:list', 'update:latest', 'update:check', 'update:download'],
                'revoked' => false,
                'special' => false,
                'expires_at' => null,
            ],
            [
                'key' => 'int_admin_api_key_full_access',
                'type' => ApiKeyType::Internal,
                'scopes' => ['*'],
                'revoked' => false,
                'special' => true,
                'expires_at' => null,
            ],
            [
                'key' => 'ext_limited_verify_only_key',
                'type' => ApiKeyType::External,
                'scopes' => ['license:verify'],
                'revoked' => false,
                'special' => false,
                'expires_at' => Carbon::now()->addMonths(6),
            ],
            [
                'key' => 'ext_revoked_inactive_key',
                'type' => ApiKeyType::External,
                'scopes' => ['license:activate', 'license:verify'],
                'revoked' => true,
                'special' => false,
                'expires_at' => null,
            ],
        ];

        foreach ($apiKeys as $apiKeyData) {
            ApiKey::query()->updateOrCreate(
                ['key' => $apiKeyData['key']],
                $apiKeyData
            );
        }
    }

    protected function createActivityLogs(): void
    {
        $activities = [
            // Recent activities (within last 24 hours for dashboard widget)
            [
                'type' => 'license_activated',
                'message' => 'License <strong>LIC-DEMO-CMS-001</strong> activated for <a href="#">demo-cms.botble.com</a> by customer@botble.com',
                'created_at' => Carbon::now()->subHours(1),
            ],
            [
                'type' => 'license_verified',
                'message' => 'License <strong>LIC-DEMO-MART-001</strong> verified successfully for <a href="#">shop.botble.com</a>',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'type' => 'license_deactivated',
                'message' => 'License <strong>LIC-DEMO-CMS-001</strong> deactivated for <a href="#">old-site.botble.com</a> by customer@botble.com',
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'type' => 'customer_login',
                'message' => 'Customer <strong>customer@botble.com</strong> logged in from IP 103.45.67.89',
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'type' => 'update_downloaded',
                'message' => 'Update v2.0.0 for <strong>Botble CMS</strong> downloaded by license LIC-DEMO-CMS-001',
                'created_at' => Carbon::now()->subHours(5),
            ],
            [
                'type' => 'license_created',
                'message' => 'New license <strong>LIC-DEMO-SHOFY-001</strong> created for product Shofy',
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'type' => 'api_request',
                'message' => 'API request from <strong>ext_demo_api_key</strong>: POST /api/v1/license/verify',
                'created_at' => Carbon::now()->subHours(7),
            ],
            [
                'type' => 'license_verified',
                'message' => 'License <strong>LIC-DEMO-FLEX-001</strong> verified successfully for <a href="#">realestate.botble.com</a>',
                'created_at' => Carbon::now()->subHours(8),
            ],
            [
                'type' => 'customer_registered',
                'message' => 'New customer <strong>customer@botble.com</strong> registered',
                'created_at' => Carbon::now()->subHours(10),
            ],
            [
                'type' => 'license_activated',
                'message' => 'License <strong>LIC-DEMO-MART-001</strong> activated for <a href="#">marketplace.botble.com</a> by customer@botble.com',
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'type' => 'product_updated',
                'message' => 'Product <strong>Botble CMS</strong> updated to version 2.0.0',
                'created_at' => Carbon::now()->subHours(14),
            ],
            [
                'type' => 'update_downloaded',
                'message' => 'Update v1.5.0 for <strong>Flex Home</strong> downloaded by license LIC-DEMO-FLEX-001',
                'created_at' => Carbon::now()->subHours(16),
            ],
            [
                'type' => 'license_expired',
                'message' => 'License <strong>LIC-BOTBLE-EXPIRED</strong> has expired',
                'created_at' => Carbon::now()->subHours(20),
            ],
            [
                'type' => 'api_request',
                'message' => 'API request from <strong>int_admin_api_key</strong>: GET /api/v1/licenses',
                'created_at' => Carbon::now()->subHours(22),
            ],
            [
                'type' => 'customer_login',
                'message' => 'Customer <strong>john@example.com</strong> logged in from IP 192.168.1.100',
                'created_at' => Carbon::now()->subHours(23),
            ],
        ];

        foreach ($activities as $activityData) {
            ActivityLog::query()->updateOrCreate(
                [
                    'type' => $activityData['type'],
                    'message' => $activityData['message'],
                ],
                $activityData
            );
        }
    }

    protected function createCustomerActivityLogs(): void
    {
        $customerActivities = [
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'login',
                'message' => 'Logged in from IP 103.45.67.89',
                'ip_address' => '103.45.67.89',
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'license_activated',
                'message' => 'License <strong>LIC-DEMO-CMS-001</strong> activated for <strong>demo-cms.botble.com</strong>',
                'ip_address' => '103.45.67.89',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'license_verified',
                'message' => 'License <strong>LIC-DEMO-MART-001</strong> verified for <strong>shop.botble.com</strong>',
                'ip_address' => '103.45.67.92',
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'license_deactivated',
                'message' => 'License <strong>LIC-DEMO-CMS-001</strong> deactivated for <strong>old-site.botble.com</strong>',
                'ip_address' => '103.45.67.89',
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'update_downloaded',
                'message' => 'Downloaded update <strong>v2.0.0</strong> for <strong>Botble CMS</strong>',
                'ip_address' => '103.45.67.89',
                'created_at' => Carbon::now()->subHours(12),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'license_activated',
                'message' => 'License <strong>LIC-DEMO-MART-001</strong> activated for <strong>marketplace.botble.com</strong>',
                'ip_address' => '103.45.67.93',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'license_verified',
                'message' => 'License <strong>LIC-DEMO-FLEX-001</strong> verified for <strong>realestate.botble.com</strong>',
                'ip_address' => '103.45.67.91',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'update_downloaded',
                'message' => 'Downloaded update <strong>v1.5.0</strong> for <strong>Flex Home</strong>',
                'ip_address' => '103.45.67.91',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'login',
                'message' => 'Logged in from IP 103.45.67.90',
                'ip_address' => '103.45.67.90',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'customer_id' => 'CUST-DEMO',
                'type' => 'license_activated',
                'message' => 'License <strong>LIC-DEMO-FLEX-001</strong> activated for <strong>realestate.botble.com</strong>',
                'ip_address' => '103.45.67.91',
                'created_at' => Carbon::now()->subDays(7),
            ],
        ];

        foreach ($customerActivities as $activityData) {
            CustomerActivityLog::query()->updateOrCreate(
                [
                    'customer_id' => $activityData['customer_id'],
                    'type' => $activityData['type'],
                    'message' => $activityData['message'],
                ],
                $activityData
            );
        }
    }

    protected function seedEncryptionKey(): void
    {
        if (setting('lm_license_encryption_key')) {
            return;
        }

        $cipher = 'aes-128-cbc';
        $key = 'base64:' . base64_encode(Encrypter::generateKey($cipher));

        Setting::set([
            'lm_license_encryption_key' => $key,
            'lm_license_encryption_cipher' => $cipher,
        ])->save();
    }

    protected function generateChangelog(string $version): string
    {
        $items = [
            'Fixed bug in license validation',
            'Improved API response time',
            'Added new dashboard widgets',
            'Updated dependencies to latest versions',
            'Enhanced security measures',
            'Fixed compatibility issues',
            'Added multi-language support',
            'Improved error handling',
            'Optimized database queries',
            'Added webhook notifications',
        ];

        $selectedItems = array_rand(array_flip($items), rand(3, 6));
        $listItems = array_map(fn ($item) => "<li>{$item}</li>", $selectedItems);

        return "<ul>\n" . implode("\n", $listItems) . "\n</ul>";
    }
}
