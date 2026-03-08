<?php

namespace Botble\LicenseManager\Services;

class MigrationResult
{
    public function __construct(
        public bool $success,
        public int $productsCreated = 0,
        public int $customersCreated = 0,
        public int $licensesCreated = 0,
        public int $activationsCreated = 0,
        public int $versionsCreated = 0,
        public array $errors = [],
        public ?string $message = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'products_created' => $this->productsCreated,
            'customers_created' => $this->customersCreated,
            'licenses_created' => $this->licensesCreated,
            'activations_created' => $this->activationsCreated,
            'versions_created' => $this->versionsCreated,
            'errors' => $this->errors,
            'message' => $this->message,
        ];
    }
}
