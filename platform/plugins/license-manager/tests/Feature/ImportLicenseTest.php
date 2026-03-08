<?php

namespace Botble\LicenseManager\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\LicenseManager\Importers\LicenseImporter;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportLicenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->product = Product::factory()->create(['name' => 'Test Product']);
        $this->customer = Customer::factory()->create(['email' => 'customer@example.com']);
    }

    protected function createUser(): User
    {
        Schema::disableForeignKeyConstraints();
        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'super_user' => 1,
            'manage_supers' => 1,
        ]);
        $user->save();

        app(ActivateUserService::class)->activate($user);

        return $user;
    }

    public function testImportPageIsAccessibleWhenAuthenticated(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('tools.data-synchronize.import.licenses.index'));

        $response->assertStatus(200);
    }

    public function testImportPageRedirectsWhenNotAuthenticated(): void
    {
        $response = $this->get(route('tools.data-synchronize.import.licenses.index'));

        $response->assertRedirect();
    }

    public function testDownloadExampleIsAccessibleWhenAuthenticated(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('tools.data-synchronize.import.licenses.download-example'), [
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function testImportCreatesNewLicense(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'NEW-LICENSE-001',
                'product_reference_id' => $this->product->reference_id,
                'type' => 'regular',
                'invoice' => 'INV-001',
                'is_envato' => false,
                'customer_id' => $this->customer->client_id,
                'email' => 'test@example.com',
                'uses' => 0,
                'parallel_uses' => 1,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $count = $importer->handle($data);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('lm_licenses', [
            'license_code' => 'NEW-LICENSE-001',
            'product_reference_id' => $this->product->reference_id,
        ]);
    }

    public function testImportUpdatesExistingLicense(): void
    {
        $license = ProductLicense::factory()->create([
            'license_code' => 'EXISTING-LICENSE',
            'product_reference_id' => $this->product->reference_id,
            'type' => 'old_type',
            'comments' => 'old comments',
        ]);

        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'EXISTING-LICENSE',
                'product_reference_id' => $this->product->reference_id,
                'type' => 'new_type',
                'invoice' => 'INV-001',
                'is_envato' => false,
                'customer_id' => null,
                'email' => 'updated@example.com',
                'uses' => 5,
                'parallel_uses' => 2,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => 'updated comments',
                'is_valid' => true,
            ],
        ];

        $count = $importer->handle($data);

        $this->assertEquals(1, $count);
        $license->refresh();
        $this->assertEquals('new_type', $license->type);
        $this->assertEquals('updated comments', $license->comments);
        $this->assertEquals('updated@example.com', $license->email);
    }

    public function testImportResolvesProductName(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-NAME',
                'product_name' => 'Test Product',
                'product_reference_id' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertEquals($this->product->reference_id, $mapped['product_reference_id']);
        $this->assertArrayNotHasKey('product_name', $mapped);
    }

    public function testImportResolvesCustomerEmail(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-EMAIL',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_email' => 'customer@example.com',
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertEquals($this->customer->client_id, $mapped['customer_id']);
        $this->assertArrayNotHasKey('customer_email', $mapped);
    }

    public function testImportSkipsRowWithUnresolvedProduct(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-NO-PRODUCT',
                'product_reference_id' => null,
                'product_name' => null,
                'type' => 'regular',
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $count = $importer->handle($data);

        $this->assertEquals(0, $count);
        $this->assertDatabaseMissing('lm_licenses', [
            'license_code' => 'LICENSE-NO-PRODUCT',
        ]);
    }

    public function testImportSkipsRowWithEmptyLicenseCode(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => '',
                'product_reference_id' => $this->product->reference_id,
                'type' => 'regular',
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $count = $importer->handle($data);

        $this->assertEquals(0, $count);
    }

    public function testImportParsesCommaSeparatedDomains(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-DOMAINS',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => 'example.com,test.example.com,app.example.com',
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertEquals(['example.com', 'test.example.com', 'app.example.com'], $mapped['domains']);
    }

    public function testImportParsesCommaSeparatedIps(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-IPS',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => '192.168.1.1,10.0.0.1,172.16.0.1',
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertEquals(['192.168.1.1', '10.0.0.1', '172.16.0.1'], $mapped['ips']);
    }

    public function testImportTrimsCommaSeparatedValues(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-SPACES',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => ' example.com , test.example.com , app.example.com ',
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertEquals(['example.com', 'test.example.com', 'app.example.com'], $mapped['domains']);
    }

    public function testImportParsesDateFields(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-DATES',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => '2025-12-31',
                'expiry_days' => null,
                'updates_until' => '2025-12-31',
                'support_until' => '2025-06-30',
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertEquals('2025-12-31', $mapped['expires_at']);
        $this->assertEquals('2025-12-31', $mapped['updates_until']);
        $this->assertEquals('2025-06-30', $mapped['support_until']);
    }

    public function testImportHandlesInvalidDatesGracefully(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-INVALID-DATES',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => 'not-a-date',
                'expiry_days' => null,
                'updates_until' => 'invalid-date-format',
                'support_until' => '32/13/2025',
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        // Invalid dates should be converted to null
        $this->assertNull($mapped['expires_at']);
        $this->assertNull($mapped['updates_until']);
        $this->assertNull($mapped['support_until']);
    }

    public function testImportHandlesEmptyDatesAsNull(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-EMPTY-DATES',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => '',
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => '',
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertNull($mapped['expires_at']);
        $this->assertNull($mapped['support_until']);
    }

    public function testImportHandlesEmptyStringsAsNull(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-WITH-EMPTY-STRINGS',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => '',
                'ips' => '',
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        // Empty strings for array fields should become null
        $this->assertNull($mapped['domains']);
        $this->assertNull($mapped['ips']);
    }

    public function testImportHandlesBooleanStringValues(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-BOOL-1',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => 'Yes',
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => 'No',
            ],
        ];

        $mapped = $importer->map($data[0]);

        // ImportColumn with boolean() preserves the input for later processing
        $this->assertNotNull($mapped['is_envato']);
        $this->assertNotNull($mapped['is_valid']);
    }

    public function testImportBatchesAreProcessedSuccessfully(): void
    {
        $this->actingAs($this->user);

        $importer = LicenseImporter::make();

        $data = collect(range(1, 250))
            ->map(fn ($i) => [
                'license_code' => "LICENSE-BATCH-{$i}",
                'product_reference_id' => $this->product->reference_id,
                'type' => 'regular',
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ])
            ->all();

        $count = $importer->handle($data);

        $this->assertEquals(250, $count);
        $this->assertEquals(250, ProductLicense::query()->count());
    }

    public function testImportWithProductNameAndReferenceId(): void
    {
        $importer = LicenseImporter::make();

        // When both product_name and product_reference_id are provided, reference_id should be used
        $data = [
            [
                'license_code' => 'LICENSE-BOTH-PRODUCT',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => 'Different Product',
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);

        $this->assertEquals($this->product->reference_id, $mapped['product_reference_id']);
    }

    public function testImportCreatesLicenseWithAllFields(): void
    {
        $importer = LicenseImporter::make();

        $data = [
            [
                'license_code' => 'LICENSE-COMPLETE',
                'product_reference_id' => $this->product->reference_id,
                'type' => 'premium',
                'invoice' => 'INV-COMPLETE',
                'is_envato' => true,
                'customer_id' => $this->customer->client_id,
                'email' => 'premium@example.com',
                'uses' => 10,
                'parallel_uses' => 3,
                'expires_at' => '2025-12-31',
                'expiry_days' => 365,
                'updates_until' => '2025-12-31',
                'support_until' => '2025-06-30',
                'domains' => 'example.com,premium.example.com',
                'ips' => '192.168.1.1,10.0.0.1',
                'comments' => 'Complete license import',
                'is_valid' => true,
            ],
        ];

        $mapped = $importer->map($data[0]);
        $count = $importer->handle([$mapped]);

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('lm_licenses', [
            'license_code' => 'LICENSE-COMPLETE',
            'product_reference_id' => $this->product->reference_id,
            'type' => 'premium',
            'invoice' => 'INV-COMPLETE',
            'is_envato' => true,
            'customer_id' => $this->customer->client_id,
            'email' => 'premium@example.com',
            'uses' => 10,
            'parallel_uses' => 3,
            'expiry_days' => 365,
            'comments' => 'Complete license import',
            'is_valid' => true,
        ]);
    }

    public function testImportCachesProductLookup(): void
    {
        // Create multiple licenses with the same product name
        $importer = LicenseImporter::make();

        $rows = [
            [
                'license_code' => 'LICENSE-CACHE-1',
                'product_name' => 'Test Product',
                'product_reference_id' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
            [
                'license_code' => 'LICENSE-CACHE-2',
                'product_name' => 'Test Product',
                'product_reference_id' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        // Map both rows - should use cached product lookup on second row
        $mapped1 = $importer->map($rows[0]);
        $mapped2 = $importer->map($rows[1]);

        $this->assertEquals($this->product->reference_id, $mapped1['product_reference_id']);
        $this->assertEquals($this->product->reference_id, $mapped2['product_reference_id']);
    }

    public function testImportCachesCustomerLookup(): void
    {
        // Create multiple licenses with the same customer email
        $importer = LicenseImporter::make();

        $rows = [
            [
                'license_code' => 'LICENSE-CUST-1',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_email' => 'customer@example.com',
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
            [
                'license_code' => 'LICENSE-CUST-2',
                'product_reference_id' => $this->product->reference_id,
                'product_name' => null,
                'type' => null,
                'invoice' => null,
                'is_envato' => false,
                'customer_email' => 'customer@example.com',
                'customer_id' => null,
                'email' => null,
                'uses' => null,
                'parallel_uses' => null,
                'expires_at' => null,
                'expiry_days' => null,
                'updates_until' => null,
                'support_until' => null,
                'domains' => null,
                'ips' => null,
                'comments' => null,
                'is_valid' => true,
            ],
        ];

        // Map both rows - should use cached customer lookup on second row
        $mapped1 = $importer->map($rows[0]);
        $mapped2 = $importer->map($rows[1]);

        $this->assertEquals($this->customer->client_id, $mapped1['customer_id']);
        $this->assertEquals($this->customer->client_id, $mapped2['customer_id']);
    }
}
