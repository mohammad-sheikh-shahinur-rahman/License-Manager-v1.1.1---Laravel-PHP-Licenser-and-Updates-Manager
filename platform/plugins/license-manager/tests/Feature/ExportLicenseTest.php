<?php

namespace Botble\LicenseManager\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\LicenseManager\Exporters\LicenseExporter;
use Botble\LicenseManager\Models\Customer;
use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExportLicenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
        $this->product = Product::factory()->create();
        $this->customer = Customer::factory()->create();
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

    public function testExportPageIsAccessibleWhenAuthenticated(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('tools.data-synchronize.export.licenses.index'));

        $response->assertStatus(200);
    }

    public function testExportPageRedirectsWhenNotAuthenticated(): void
    {
        $response = $this->get(route('tools.data-synchronize.export.licenses.index'));

        $response->assertRedirect();
    }

    public function testExportCsvDownloadReturnsSuccessful(): void
    {
        $this->actingAs($this->user);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'license_code' => 'TEST-LICENSE-001',
            'customer_id' => $this->customer->client_id,
        ]);

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function testExportWithProductFilter(): void
    {
        $this->actingAs($this->user);

        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        ProductLicense::factory(2)->create([
            'product_reference_id' => $product1->reference_id,
        ]);

        ProductLicense::factory(3)->create([
            'product_reference_id' => $product2->reference_id,
        ]);

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'product_id' => $product1->id,
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
    }

    public function testExportWithStatusFilter(): void
    {
        $this->actingAs($this->user);

        ProductLicense::factory(3)->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => true,
        ]);

        ProductLicense::factory(2)->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => false,
        ]);

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'is_valid' => '1',
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
    }

    public function testExportWithInvalidStatusFilter(): void
    {
        $this->actingAs($this->user);

        ProductLicense::factory(3)->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => true,
        ]);

        ProductLicense::factory(2)->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => false,
        ]);

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'is_valid' => '0',
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
    }

    public function testExportWithDateRangeFilter(): void
    {
        $this->actingAs($this->user);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'created_at' => Carbon::now()->subDays(10),
        ]);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'created_at' => Carbon::now(),
        ]);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'created_at' => Carbon::now()->addDays(10),
        ]);

        $startDate = Carbon::now()->subDays(5)->toDateString();
        $endDate = Carbon::now()->addDays(5)->toDateString();

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
    }

    public function testExportWithoutNullRelations(): void
    {
        $this->actingAs($this->user);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => null,
        ]);

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
    }

    public function testExportEmptyDatabaseReturnsOnlyHeaders(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function testExportWithMultipleFilters(): void
    {
        $this->actingAs($this->user);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        ProductLicense::factory(2)->create([
            'product_reference_id' => $product1->reference_id,
            'is_valid' => true,
        ]);

        ProductLicense::factory(1)->create([
            'product_reference_id' => $product1->reference_id,
            'is_valid' => false,
        ]);

        ProductLicense::factory(3)->create([
            'product_reference_id' => $product2->reference_id,
            'is_valid' => true,
        ]);

        $response = $this->post(route('tools.data-synchronize.export.licenses.store'), [
            'product_id' => $product1->id,
            'is_valid' => '1',
            'format' => 'csv',
        ]);

        $response->assertSuccessful();
    }

    public function testExportTestsExporterWithProductFilter(): void
    {
        // Test the exporter directly
        $exporter = new LicenseExporter();
        $exporter->setProductId($this->product->id);

        ProductLicense::factory(5)->create([
            'product_reference_id' => $this->product->reference_id,
        ]);

        $product2 = Product::factory()->create();
        ProductLicense::factory(3)->create([
            'product_reference_id' => $product2->reference_id,
        ]);

        $collection = $exporter->collection();

        // Should only include licenses from the filtered product
        $this->assertCount(5, $collection);
    }

    public function testExportTestsExporterWithStatusFilter(): void
    {
        $exporter = new LicenseExporter();
        $exporter->setIsValid(true);

        ProductLicense::factory(4)->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => true,
        ]);

        ProductLicense::factory(2)->create([
            'product_reference_id' => $this->product->reference_id,
            'is_valid' => false,
        ]);

        $collection = $exporter->collection();

        // Should only include valid licenses
        $this->assertCount(4, $collection);
    }

    public function testExportTestsExporterWithDateRange(): void
    {
        $startDate = Carbon::now()->subDays(5)->toDateString();
        $endDate = Carbon::now()->addDays(5)->toDateString();

        $exporter = new LicenseExporter();
        $exporter->setDateRange($startDate, $endDate);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'created_at' => Carbon::now()->subDays(10),
        ]);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'created_at' => Carbon::now(),
        ]);

        ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'created_at' => Carbon::now()->addDays(10),
        ]);

        $collection = $exporter->collection();

        // Should only include licenses created in the date range
        $this->assertCount(1, $collection);
    }

    public function testExportTestsExporterIncludesProductName(): void
    {
        $product = Product::factory()->create(['name' => 'My Awesome Product']);

        $license = ProductLicense::factory()->create([
            'product_reference_id' => $product->reference_id,
        ]);

        $exporter = new LicenseExporter();
        $collection = $exporter->collection();

        $row = $collection->first();
        $this->assertEquals('My Awesome Product', $row['product_name']);
    }

    public function testExportTestsExporterIncludesCustomerEmail(): void
    {
        $customer = Customer::factory()->create(['email' => 'customer@example.com']);

        $license = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'customer_id' => $customer->client_id,
        ]);

        $exporter = new LicenseExporter();
        $collection = $exporter->collection();

        $row = $collection->first();
        $this->assertEquals('customer@example.com', $row['customer_email']);
    }

    public function testExportConvertsDomainsArrayToCommaString(): void
    {
        $license = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'domains' => ['example.com', 'test.example.com', 'app.example.com'],
        ]);

        $exporter = new LicenseExporter();
        $collection = $exporter->collection();

        $row = $collection->first();
        $this->assertEquals('example.com,test.example.com,app.example.com', $row['domains']);
    }

    public function testExportConvertsDatesString(): void
    {
        $expiryDate = Carbon::create(2025, 12, 31);

        $license = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'expires_at' => $expiryDate,
            'updates_until' => $expiryDate,
            'support_until' => $expiryDate,
        ]);

        $exporter = new LicenseExporter();
        $collection = $exporter->collection();

        $row = $collection->first();
        $this->assertEquals('2025-12-31', $row['expires_at']);
        $this->assertEquals('2025-12-31', $row['updates_until']);
        $this->assertEquals('2025-12-31', $row['support_until']);
    }

    public function testExportConvertsBooleanValues(): void
    {
        $license1 = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'is_envato' => true,
            'is_valid' => true,
        ]);

        $license2 = ProductLicense::factory()->create([
            'product_reference_id' => $this->product->reference_id,
            'is_envato' => false,
            'is_valid' => false,
        ]);

        $exporter = new LicenseExporter();
        $collection = $exporter->collection();

        $rows = $collection->all();
        $this->assertEquals(1, $rows[0]['is_envato']);
        $this->assertEquals(1, $rows[0]['is_valid']);
        $this->assertEquals(0, $rows[1]['is_envato']);
        $this->assertEquals(0, $rows[1]['is_valid']);
    }

    public function testExportCountersReturnsTotal(): void
    {
        ProductLicense::factory(5)->create([
            'product_reference_id' => $this->product->reference_id,
        ]);

        $exporter = new LicenseExporter();
        $counters = $exporter->counters();

        $this->assertNotEmpty($counters);
        $this->assertEquals(5, $counters[0]->getValue());
    }
}
