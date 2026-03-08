<?php

namespace Botble\LicenseManager\Tests\Feature\Api\Internal;

use Botble\LicenseManager\Models\Customer;

class CustomerControllerTest extends InternalApiTestCase
{
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::query()->create([
            'name' => 'Test Customer',
            'email' => 'test-customer@example.com',
            'password' => 'password',
            'client_id' => 'test-client-id',
        ]);
    }

    public function test_index_returns_customers_with_correct_structure(): void
    {
        $response = $this->getWithHeaders('/api/internal/customers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'client_id',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_show_returns_customer_with_correct_structure(): void
    {
        $response = $this->getWithHeaders('/api/internal/customers/' . $this->customer->getKey());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'client_id',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonFragment([
                'name' => 'Test Customer',
                'email' => 'test-customer@example.com',
                'client_id' => 'test-client-id',
            ]);
    }

    public function test_show_returns_error_for_nonexistent_customer(): void
    {
        $response = $this->getWithHeaders('/api/internal/customers/99999');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }
}
