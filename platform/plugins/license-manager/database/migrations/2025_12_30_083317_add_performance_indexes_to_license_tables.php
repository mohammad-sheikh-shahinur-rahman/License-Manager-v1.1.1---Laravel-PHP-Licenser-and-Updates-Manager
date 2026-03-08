<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        // No-op: All performance indexes are now included in base table migrations.
        // This migration is kept for backwards compatibility with existing installs
        // that may have run this migration before the schema modernization.
    }

    public function down(): void
    {
        // No-op
    }
};
