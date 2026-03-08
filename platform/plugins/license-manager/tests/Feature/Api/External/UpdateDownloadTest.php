<?php

namespace Botble\LicenseManager\Tests\Feature\Api\External;

use Botble\LicenseManager\LicenseManager;
use Botble\LicenseManager\Models\ActivityLog;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UpdateDownloadTest extends ExternalApiTestCase
{
    protected string $legacyKey = '1af0f4bdeb9ac1ed8360';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestFile($this->version->main_file, 'test-content');
    }

    public function test_downloads_file_without_license_when_not_required(): void
    {
        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/zip');
    }

    public function test_logs_successful_download(): void
    {
        $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $this->assertDatabaseHas('lm_update_downloads', [
            'version_id' => $this->version->version_id,
            'is_valid' => 1,
        ]);
    }

    public function test_requires_license_when_product_requires_it(): void
    {
        $this->product->update(['license_update' => 1]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $response->assertStatus(401);
    }

    public function test_accepts_license_code_and_client_name(): void
    {
        $this->product->update(['license_update' => 1]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            [
                'license_code' => $this->license->license_code,
                'client_name' => $this->license->customer_id,
            ]
        );

        $response->assertStatus(200);
    }

    public function test_accepts_encrypted_license_file(): void
    {
        $this->product->update(['license_update' => 1]);

        // Set encryption key for the encrypter
        setting()->set([
            'lm_license_encryption_key' => 'base64:' . base64_encode(random_bytes(16)),
            'lm_license_encryption_cipher' => 'aes-128-cbc',
        ])->save();

        $encrypter = app(LicenseManager::class)->createEncrypter();
        $licenseFile = $encrypter->encrypt([
            'license' => $this->license->license_code,
            'customer_id' => $this->license->customer_id,
        ]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            ['license_file' => $licenseFile]
        );

        $response->assertStatus(200);
    }

    public function test_rejects_invalid_license(): void
    {
        $this->product->update(['license_update' => 1]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            [
                'license_code' => 'invalid-license',
                'client_name' => 'invalid-client',
            ]
        );

        $response->assertStatus(401);
    }

    public function test_rejects_expired_updates_till(): void
    {
        $this->product->update(['license_update' => 1]);
        $this->license->update(['updates_until' => Carbon::now()->subDay()]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            [
                'license_code' => $this->license->license_code,
                'client_name' => $this->license->customer_id,
            ]
        );

        $response->assertStatus(401);
    }

    public function test_logs_failed_download_attempt(): void
    {
        $this->product->update(['license_update' => 1]);

        $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $this->assertDatabaseHas('lm_update_downloads', [
            'version_id' => $this->version->version_id,
            'is_valid' => 0,
        ]);
    }

    public function test_logs_failed_license_to_activity_log(): void
    {
        $this->product->update(['license_update' => 1]);

        $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $log = ActivityLog::query()->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('blocked', $log->message);
        $this->assertStringContainsString('invalid', $log->message);
    }

    public function test_returns_404_for_missing_file(): void
    {
        unlink(storage_path('app/version-files/' . $this->product->reference_id . '/' . $this->version->main_file));

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $response->assertStatus(404);
    }

    public function test_downloads_sql_file(): void
    {
        $sqlFile = 'update.sql';
        $this->version->update(['sql_file' => $sqlFile]);
        $this->createTestFile($sqlFile, 'SELECT 1;');

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/sql",
            []
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/sql');
    }

    public function test_returns_404_for_invalid_version(): void
    {
        $response = $this->postWithHeaders(
            '/api/external/update/invalid-vid/download/main',
            []
        );

        $response->assertStatus(404);
    }

    public function test_returns_404_for_invalid_type(): void
    {
        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/invalid",
            []
        );

        $response->assertStatus(404);
    }

    public function test_accepts_case_insensitive_license_code(): void
    {
        $this->product->update(['license_update' => 1]);
        $this->license->update(['license_code' => 'ABCD-1234-EFGH-5678']);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            [
                'license_code' => 'abcd-1234-efgh-5678',
                'client_name' => $this->license->customer_id,
            ]
        );

        $response->assertStatus(200);
    }

    public function test_accepts_case_insensitive_client_name(): void
    {
        $this->product->update(['license_update' => 1]);
        $this->license->update(['customer_id' => 'AnmolSethi']);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            [
                'license_code' => $this->license->license_code,
                'client_name' => 'anmolsethi',
            ]
        );

        $response->assertStatus(200);
    }

    public function test_rejects_blocked_license(): void
    {
        $this->product->update(['license_update' => 1]);
        $this->license->update(['is_valid' => false]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            [
                'license_code' => $this->license->license_code,
                'client_name' => $this->license->customer_id,
            ]
        );

        $response->assertStatus(401);
    }

    public function test_accepts_legacy_encrypted_license_file(): void
    {
        $this->product->update(['license_update' => 1]);

        // Set legacy encryption key
        setting()->set([
            'lm_legacy_encryption_key' => $this->legacyKey,
        ])->save();

        // Create legacy encrypted license file (CodeIgniter format)
        $licenseFile = $this->encryptWithLegacyFormat(json_encode([
            'license' => $this->license->license_code,
            'customer_id' => $this->license->customer_id,
        ]));

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            ['license_file' => $licenseFile]
        );

        $response->assertStatus(200);
    }

    public function test_rejects_invalid_legacy_encrypted_license_file(): void
    {
        $this->product->update(['license_update' => 1]);

        // Set legacy encryption key
        setting()->set([
            'lm_legacy_encryption_key' => $this->legacyKey,
        ])->save();

        // Create legacy encrypted license file with wrong license
        $licenseFile = $this->encryptWithLegacyFormat(json_encode([
            'license' => 'invalid-license',
            'customer_id' => 'invalid-client',
        ]));

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            ['license_file' => $licenseFile]
        );

        $response->assertStatus(401);
    }

    public function test_response_includes_content_length_header(): void
    {
        $content = 'test-zip-content-here';
        $filePath = $this->createTestFile($this->version->main_file, $content);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Length', (string) filesize($filePath));
    }

    public function test_response_includes_content_disposition_header(): void
    {
        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $response->assertStatus(200);
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('.zip', $disposition);
    }

    public function test_response_is_binary_file_response(): void
    {
        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $response->assertStatus(200);
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
    }

    public function test_sql_response_has_correct_content_type(): void
    {
        $sqlFile = 'update.sql';
        $this->version->update(['sql_file' => $sqlFile]);
        $this->createTestFile($sqlFile, 'SELECT 1;');

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/sql",
            []
        );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/sql');
        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('.sql', $disposition);
    }

    public function test_returns_404_for_inactive_product(): void
    {
        $this->product->update(['is_active' => 0]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            []
        );

        $response->assertStatus(404);
    }

    public function test_rejects_expired_license(): void
    {
        $this->product->update(['license_update' => 1]);
        $this->license->update(['expires_at' => Carbon::now()->subDay()]);

        $response = $this->postWithHeaders(
            "/api/external/update/{$this->version->version_id}/download/main",
            [
                'license_code' => $this->license->license_code,
                'client_name' => $this->license->customer_id,
            ]
        );

        $response->assertStatus(401);
    }

    protected function encryptWithLegacyFormat(string $data): string
    {
        $cipher = 'aes-128-cbc';
        $ivSize = openssl_cipher_iv_length($cipher);

        // HKDF for encryption key
        $encryptionKey = $this->hkdf($this->legacyKey, 'sha512', null, strlen($this->legacyKey), 'encryption');

        // HKDF for HMAC key
        $hmacKey = $this->hkdf($this->legacyKey, 'sha512', null, null, 'authentication');

        // Generate IV
        $iv = random_bytes($ivSize);

        // Encrypt
        $ciphertext = openssl_encrypt($data, $cipher, $encryptionKey, OPENSSL_RAW_DATA, $iv);

        // Combine IV + ciphertext and base64 encode
        $encoded = base64_encode($iv . $ciphertext);

        // Calculate HMAC (hex format)
        $hmac = hash_hmac('sha512', $encoded, $hmacKey, false);

        return $hmac . $encoded;
    }

    protected function hkdf(string $key, string $digest, ?string $salt, ?int $length, string $info): string
    {
        $digestSizes = [
            'sha224' => 28,
            'sha256' => 32,
            'sha384' => 48,
            'sha512' => 64,
        ];

        $digestSize = $digestSizes[$digest];

        if (empty($length)) {
            $length = $digestSize;
        }

        if (empty($salt)) {
            $salt = str_repeat("\0", $digestSize);
        }

        $prk = hash_hmac($digest, $key, $salt, true);

        $derivedKey = '';
        $keyBlock = '';

        for ($blockIndex = 1; strlen($derivedKey) < $length; $blockIndex++) {
            $keyBlock = hash_hmac($digest, $keyBlock . $info . chr($blockIndex), $prk, true);
            $derivedKey .= $keyBlock;
        }

        return substr($derivedKey, 0, $length);
    }
}
