<?php

namespace Botble\LicenseManager\Commands;

use Botble\LicenseManager\Models\Product;
use Botble\LicenseManager\Services\BulkLicenseGenerationService;
use Illuminate\Console\Command;

use function Laravel\Prompts\search;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('cms:license-manager:generate', 'Generate license keys for a product.')]
class GenerateLicensesCommand extends Command
{
    protected $signature = 'cms:license-manager:generate
        {product? : Product reference ID}
        {quantity? : Number of licenses to generate (2-500)}
        {--type= : License type}
        {--uses= : Number of uses}
        {--parallel-uses= : Parallel uses limit}
        {--expiry-days= : Days until expiration}
        {--expires-at= : Expiration date (Y-m-d)}
        {--updates-until= : Updates valid until (Y-m-d)}
        {--support-until= : Support valid until (Y-m-d)}
        {--customer-id= : Customer ID}
        {--email= : Customer email}
        {--invoice= : Invoice number}
        {--comments= : Comments}';

    public function handle(BulkLicenseGenerationService $service): int
    {
        $product = $this->resolveProduct();

        if (! $product) {
            return self::FAILURE;
        }

        $quantity = $this->resolveQuantity();

        if (! $quantity) {
            return self::FAILURE;
        }

        $overrides = $this->resolveOverrides($product);

        if ($overrides === null) {
            return self::FAILURE;
        }

        $this->components->info("Generating {$quantity} licenses for {$product->name}...");

        $licenses = $service->generate($product, $quantity, $overrides);

        $codes = $licenses->pluck('license_code');

        $first = $licenses->first();

        $this->components->info("Generated {$codes->count()} licenses");

        $this->newLine();
        $this->components->twoColumnDetail('<fg=gray>Product</>');
        $this->components->twoColumnDetail('Name', $product->name);
        $this->components->twoColumnDetail('Reference ID', $product->reference_id);

        $this->newLine();
        $this->components->twoColumnDetail('<fg=gray>License Info</>');
        $this->components->twoColumnDetail('Type', $first->type ?: '-');
        $this->components->twoColumnDetail('Uses', (string) ($first->uses ?? 0));
        $this->components->twoColumnDetail('Parallel Uses', $first->parallel_uses !== null ? (string) $first->parallel_uses : '-');
        $this->components->twoColumnDetail('Expires At', $first->expires_at?->format('Y-m-d') ?: '-');
        $this->components->twoColumnDetail('Updates Until', $first->updates_until?->format('Y-m-d') ?: '-');
        $this->components->twoColumnDetail('Support Until', $first->support_until?->format('Y-m-d') ?: '-');

        if ($first->customer_id || $first->email || $first->invoice) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=gray>Customer</>');

            if ($first->customer_id) {
                $this->components->twoColumnDetail('Customer ID', $first->customer_id);
            }

            if ($first->email) {
                $this->components->twoColumnDetail('Email', $first->email);
            }

            if ($first->invoice) {
                $this->components->twoColumnDetail('Invoice', $first->invoice);
            }
        }

        $this->newLine();
        $this->components->twoColumnDetail('<fg=gray>License Codes</>');
        $codes->each(fn ($code, $index) => $this->components->twoColumnDetail((string) ($index + 1), $code));

        return self::SUCCESS;
    }

    protected function resolveProduct(): ?Product
    {
        $referenceId = $this->argument('product');

        if (! $referenceId) {
            $products = Product::query()
                ->select(['reference_id', 'name', 'is_active'])
                ->orderBy('name')
                ->get();

            if ($products->isEmpty()) {
                $this->components->error('No products found.');

                return null;
            }

            $referenceId = search(
                label: 'Select a product',
                options: fn (string $search) => $products
                    ->filter(fn (Product $p) => ! $search || str_contains(strtolower($p->name), strtolower($search)))
                    ->mapWithKeys(fn (Product $p) => [
                        $p->reference_id => "{$p->name} ({$p->reference_id})" . ($p->is_active ? '' : ' [inactive]'),
                    ])
                    ->all(),
                placeholder: 'Search by product name...',
                required: true,
            );
        }

        $product = Product::query()
            ->where('reference_id', $referenceId)
            ->first();

        if (! $product) {
            $this->components->error("Product not found: {$referenceId}");

            return null;
        }

        return $product;
    }

    protected function resolveQuantity(): ?int
    {
        $quantity = $this->argument('quantity');

        if (! $quantity) {
            $quantity = text(
                label: 'How many licenses to generate?',
                placeholder: '10',
                default: '10',
                required: true,
                validate: fn (string $value) => match (true) {
                    ! is_numeric($value) => 'Must be a number.',
                    (int) $value < 2 => 'Minimum is 2.',
                    (int) $value > 500 => 'Maximum is 500.',
                    default => null,
                },
            );
        }

        $quantity = (int) $quantity;

        if ($quantity < 2 || $quantity > 500) {
            $this->components->error('Quantity must be between 2 and 500.');

            return null;
        }

        return $quantity;
    }

    protected function resolveOverrides(Product $product): ?array
    {
        $overrides = array_filter([
            'type' => $this->option('type'),
            'uses' => $this->option('uses'),
            'parallel_uses' => $this->option('parallel-uses'),
            'expiry_days' => $this->option('expiry-days'),
            'expires_at' => $this->option('expires-at'),
            'updates_until' => $this->option('updates-until'),
            'support_until' => $this->option('support-until'),
            'customer_id' => $this->option('customer-id'),
            'email' => $this->option('email'),
            'invoice' => $this->option('invoice'),
            'comments' => $this->option('comments'),
        ], fn ($value) => $value !== null);

        if (! empty($overrides) || $this->option('no-interaction')) {
            return $overrides;
        }

        $customize = select(
            label: 'Do you want to customize license options?',
            options: [
                'defaults' => "Use product defaults ({$product->name})",
                'customize' => 'Customize options',
            ],
            default: 'defaults',
        );

        if ($customize === 'defaults') {
            return $overrides;
        }

        $overrides['customer_id'] = text('Customer ID (leave empty to skip)', placeholder: 'CUST-001') ?: null;
        $overrides['email'] = text('Customer email (leave empty to skip)', placeholder: 'customer@example.com') ?: null;
        $overrides['invoice'] = text('Invoice number (leave empty to skip)', placeholder: 'INV-2026-001') ?: null;
        $overrides['type'] = text('License type (leave empty for product default)', placeholder: 'standard') ?: null;
        $overrides['expiry_days'] = text('Expiry days (leave empty for product default)', placeholder: '365') ?: null;
        $overrides['comments'] = text('Comments (leave empty to skip)') ?: null;

        return array_filter($overrides, fn ($value) => $value !== null);
    }
}
