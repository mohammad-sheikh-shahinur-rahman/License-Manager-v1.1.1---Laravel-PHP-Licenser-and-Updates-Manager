<?php

namespace Botble\LicenseManager\Tests\Factories;

use Botble\LicenseManager\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'reference_id' => Str::random(15),
            'envato_id' => (string) fake()->unique()->numberBetween(10000000, 99999999),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'license_update' => 0,
            'serve_latest_updates' => 1,
            'is_active' => 1,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => 0,
        ]);
    }

    public function requiresLicense(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_update' => 1,
        ]);
    }

    public function withLicenseDefaults(): static
    {
        return $this->state(fn (array $attributes) => [
            'default_license_type' => 'regular',
            'default_uses' => 0,
            'default_parallel_uses' => 3,
            'default_expiry_days' => 365,
            'default_updates_until_days' => 180,
            'default_support_until_days' => 90,
            'default_comments' => 'Auto-generated license',
        ]);
    }
}
