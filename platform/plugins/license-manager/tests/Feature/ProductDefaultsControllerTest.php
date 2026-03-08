<?php

namespace Botble\LicenseManager\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\LicenseManager\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductDefaultsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
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

    public function testReturnsProductDefaults(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->withLicenseDefaults()->create();

        $response = $this->getJson(route('lm.products.defaults', ['product' => $product->reference_id]));

        $response->assertSuccessful();
        $response->assertJson([
            'default_license_type' => 'regular',
            'default_uses' => 0,
            'default_parallel_uses' => 3,
            'default_expiry_days' => 365,
            'default_updates_until_days' => 180,
            'default_support_until_days' => 90,
            'default_comments' => 'Auto-generated license',
        ]);
    }

    public function testReturnsNullsForProductWithoutDefaults(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create();

        $response = $this->getJson(route('lm.products.defaults', ['product' => $product->reference_id]));

        $response->assertSuccessful();
        $response->assertJson([
            'default_license_type' => null,
            'default_uses' => null,
            'default_parallel_uses' => null,
            'default_expiry_days' => null,
            'default_updates_until_days' => null,
            'default_support_until_days' => null,
            'default_comments' => null,
        ]);
    }

    public function testReturns404ForInvalidProduct(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('lm.products.defaults', ['product' => 'non-existent-id']));

        $response->assertNotFound();
    }

    public function testRequiresAuthentication(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson(route('lm.products.defaults', ['product' => $product->reference_id]));

        $response->assertUnauthorized();
    }
}
