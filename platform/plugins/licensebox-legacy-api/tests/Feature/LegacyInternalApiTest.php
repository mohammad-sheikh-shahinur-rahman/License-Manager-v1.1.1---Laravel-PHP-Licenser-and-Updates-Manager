<?php

namespace Botble\LegacyApi\Tests\Feature;

use Botble\LicenseManager\Models\ProductActivation;
use Carbon\Carbon;

class LegacyInternalApiTest extends LegacyApiTestCase
{
    public function test_check_connection_int(): void
    {
        $response = $this->postInternal('/api/check_connection_int');

        $response->assertOk()->assertJson(['status' => true]);
    }

    public function test_check_connection_int_via_api_internal_prefix(): void
    {
        $response = $this->postInternal('/api_internal/check_connection_int');

        $response->assertOk()->assertJson(['status' => true]);
    }

    public function test_check_connection_int_rejects_missing_key(): void
    {
        $response = $this->post('/api/check_connection_int');

        $response->assertStatus(400);
    }

    public function test_check_connection_int_rejects_invalid_key(): void
    {
        $response = $this->postInternal('/api/check_connection_int', [], [
            'LB-API-KEY' => 'bad-key',
        ]);

        $response->assertStatus(401);
    }

    // --- Products ---

    public function test_add_product(): void
    {
        $response = $this->postInternal('/api/add_product', [
            'product_name' => 'New Product',
            'product_id' => 'NEWPROD1',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_products', ['reference_id' => 'NEWPROD1']);
    }

    public function test_add_product_generates_id_when_missing(): void
    {
        $response = $this->postInternal('/api/add_product', [
            'product_name' => 'Auto ID Product',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $response->assertJsonStructure(['product_id']);
    }

    public function test_add_product_rejects_missing_name(): void
    {
        $response = $this->postInternal('/api/add_product', []);

        $response->assertStatus(400)->assertJson(['status' => false]);
    }

    public function test_add_product_rejects_duplicate_id(): void
    {
        $response = $this->postInternal('/api/add_product', [
            'product_name' => 'Duplicate',
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertOk()->assertJson(['status' => false]);
    }

    public function test_get_product(): void
    {
        $response = $this->postInternal('/api/get_product', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertOk()->assertJson([
            'status' => true,
            'product_id' => $this->product->reference_id,
            'product_name' => $this->product->name,
        ]);
    }

    public function test_get_product_not_found(): void
    {
        $response = $this->postInternal('/api/get_product', [
            'product_id' => 'NONEXIST',
        ]);

        $response->assertOk()->assertJson(['status' => false]);
    }

    public function test_get_products(): void
    {
        $response = $this->postInternal('/api/get_products');

        $response->assertOk()->assertJson(['status' => true]);
        $response->assertJsonStructure(['products']);
    }

    public function test_mark_product_active(): void
    {
        $this->product->update(['is_active' => 0]);

        $response = $this->postInternal('/api/mark_product_active', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_products', ['reference_id' => $this->product->reference_id, 'is_active' => 1]);
    }

    public function test_mark_product_inactive(): void
    {
        $response = $this->postInternal('/api/mark_product_inactive', [
            'product_id' => $this->product->reference_id,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_products', ['reference_id' => $this->product->reference_id, 'is_active' => 0]);
    }

    // --- Licenses ---

    public function test_create_license(): void
    {
        $response = $this->postInternal('/api/create_license', [
            'product_id' => $this->product->reference_id,
            'license_code' => 'TEST-NEW-LICENSE',
            'client_name' => 'Test Client',
            'client_email' => 'test@example.com',
            'license_uses' => 10,
            'license_parallel_uses' => 1,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_product_licenses', ['license_code' => 'TEST-NEW-LICENSE']);
    }

    public function test_create_license_generates_code_when_missing(): void
    {
        $response = $this->postInternal('/api/create_license', [
            'product_id' => $this->product->reference_id,
            'license_uses' => 10,
            'license_parallel_uses' => 1,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $response->assertJsonStructure(['license_code']);
    }

    public function test_create_license_rejects_invalid_product(): void
    {
        $response = $this->postInternal('/api/create_license', [
            'product_id' => 'INVALID',
        ]);

        $response->assertOk()->assertJson(['status' => false]);
    }

    public function test_get_license(): void
    {
        $response = $this->postInternal('/api/get_license', [
            'license_code' => $this->license->license_code,
        ]);

        $response->assertOk()->assertJson([
            'status' => true,
            'license_code' => $this->license->license_code,
            'product_id' => $this->product->reference_id,
        ]);
    }

    public function test_get_license_not_found(): void
    {
        $response = $this->postInternal('/api/get_license', [
            'license_code' => 'NONEXIST',
        ]);

        $response->assertOk()->assertJson(['status' => false]);
    }

    public function test_edit_license(): void
    {
        $response = $this->postInternal('/api/edit_license', [
            'license_code' => $this->license->license_code,
            'client_name' => 'Updated Client',
            'client_email' => 'updated@example.com',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_product_licenses', [
            'license_code' => $this->license->license_code,
            'customer_id' => 'Updated Client',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_search_license(): void
    {
        $response = $this->postInternal('/api/search_license', [
            'keyword' => substr($this->license->license_code, 0, 8),
        ]);

        $response->assertOk()->assertJson(['status' => true]);
    }

    public function test_search_license_escapes_wildcards(): void
    {
        $response = $this->postInternal('/api/search_license', [
            'keyword' => '%_test',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
    }

    public function test_delete_license(): void
    {
        $licenseCode = $this->license->license_code;

        $response = $this->postInternal('/api/delete_license', [
            'license_code' => $licenseCode,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseMissing('lm_product_licenses', ['license_code' => $licenseCode]);
    }

    public function test_delete_license_cascades_activations(): void
    {
        ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'customer_id' => 'Test Client',
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'activated_at' => Carbon::now(),
            'is_active' => 1,
            'is_valid' => 1,
        ]);

        $this->postInternal('/api/delete_license', [
            'license_code' => $this->license->license_code,
        ]);

        $this->assertDatabaseMissing('lm_product_activations', [
            'license_code' => $this->license->license_code,
        ]);
    }

    public function test_block_license(): void
    {
        $response = $this->postInternal('/api/block_license', [
            'license_code' => $this->license->license_code,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_product_licenses', [
            'license_code' => $this->license->license_code,
            'is_valid' => 0,
        ]);
    }

    public function test_unblock_license(): void
    {
        $this->license->update(['is_valid' => 0]);

        $response = $this->postInternal('/api/unblock_license', [
            'license_code' => $this->license->license_code,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_product_licenses', [
            'license_code' => $this->license->license_code,
            'is_valid' => 1,
        ]);
    }

    public function test_deactivate_license_activations(): void
    {
        ProductActivation::query()->create([
            'product_reference_id' => $this->product->reference_id,
            'license_code' => $this->license->license_code,
            'customer_id' => 'Test Client',
            'url' => 'https://example.com',
            'ip_address' => '127.0.0.1',
            'activated_at' => Carbon::now(),
            'is_active' => 1,
            'is_valid' => 1,
        ]);

        $response = $this->postInternal('/api/deactivate_license_activations', [
            'license_code' => $this->license->license_code,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('lm_product_activations', [
            'license_code' => $this->license->license_code,
            'is_active' => 0,
        ]);
    }
}
