<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

class UpdateDownloadSizeTest extends ExternalApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestFile($this->version->main_file, str_repeat('x', 1024));
    }

    public function test_returns_headers_for_main_file(): void
    {
        $response = $this->getWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main/size"
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
        $response->assertHeader('Content-Length', '1024');
        $response->assertHeader('Content-Disposition');
    }

    public function test_returns_404_for_invalid_version(): void
    {
        $response = $this->getWithHeaders('/api/external/update/invalid-vid/download/main/size');

        $response->assertStatus(404);
    }

    public function test_returns_404_for_invalid_type(): void
    {
        $response = $this->getWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/invalid/size"
        );

        $response->assertStatus(404);
    }

    public function test_returns_404_for_inactive_product(): void
    {
        $this->product->update(['is_active' => 0]);

        $response = $this->getWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main/size"
        );

        $response->assertStatus(404);
    }

    public function test_returns_404_when_file_missing(): void
    {
        unlink(storage_path('app/version-files/' . $this->product->reference_id . '/' . $this->version->main_file));

        $response = $this->getWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main/size"
        );

        $response->assertStatus(404);
    }

    public function test_returns_correct_headers_for_sql_file(): void
    {
        $sqlFile = 'update.sql';
        $this->version->update(['sql_file' => $sqlFile]);
        $this->createTestFile($sqlFile, 'SELECT 1;');

        $response = $this->getWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/sql/size"
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/sql');
    }
}
