<?php

namespace Botble\LicenseManager\Tests\Factories;

use Botble\LicenseManager\Models\ProductVersion;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVersionFactory extends Factory
{
    protected $model = ProductVersion::class;

    public function definition(): array
    {
        return [
            'is_active' => 1,
            'version_id' => Str::random(15),
            'product_reference_id' => Str::random(15),
            'version' => fake()->semver(),
            'released_at' => Carbon::now()->format('Y-m-d'),
            'summary' => fake()->sentence(),
            'changelog' => fake()->paragraphs(3, true),
            'main_file' => Str::random(32) . '.zip',
            'sql_file' => null,
        ];
    }

    public function withSqlFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'sql_file' => Str::random(32) . '.sql',
        ]);
    }
}
