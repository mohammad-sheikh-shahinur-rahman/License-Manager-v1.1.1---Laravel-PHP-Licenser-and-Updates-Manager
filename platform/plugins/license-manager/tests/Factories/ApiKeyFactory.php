<?php

namespace Botble\LicenseManager\Tests\Factories;

use Botble\LicenseManager\Enums\ApiKeyType;
use Botble\LicenseManager\Models\ApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        return [
            'key' => Str::random(32),
            'type' => ApiKeyType::External,
            'scopes' => null,
            'revoked' => false,
            'special' => false,
            'expires_at' => null,
        ];
    }

    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ApiKeyType::Internal,
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'revoked' => true,
        ]);
    }

    public function special(): static
    {
        return $this->state(fn (array $attributes) => [
            'special' => true,
        ]);
    }
}
