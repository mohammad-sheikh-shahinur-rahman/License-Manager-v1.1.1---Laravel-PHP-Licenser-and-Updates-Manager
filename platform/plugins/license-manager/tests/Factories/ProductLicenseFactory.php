<?php

namespace Botble\LicenseManager\Tests\Factories;

use Botble\LicenseManager\Models\ProductLicense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductLicenseFactory extends Factory
{
    protected $model = ProductLicense::class;

    public function definition(): array
    {
        return [
            'product_reference_id' => Str::random(15),
            'license_code' => strtoupper(Str::random(32)),
            'type' => 'Regular License',
            'invoice' => fake()->uuid(),
            'customer_id' => fake()->userName(),
            'email' => fake()->email(),
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
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_valid' => false,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->subDay(),
        ]);
    }

    public function updatesExpired(): static
    {
        return $this->state(fn (array $attributes) => [
            'updates_until' => Carbon::now()->subDay(),
        ]);
    }
}
