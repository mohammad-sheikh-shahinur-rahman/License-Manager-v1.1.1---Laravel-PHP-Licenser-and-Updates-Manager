<?php

namespace Botble\LicenseManager\Tests\Unit\Encryption;

use Botble\LicenseManager\Encryption\LegacyEncrypter;
use PHPUnit\Framework\TestCase;

class LegacyEncrypterTest extends TestCase
{
    protected string $testKey = '1af0f4bdeb9ac1ed8360';

    public function test_can_decrypt_codeigniter_encrypted_data(): void
    {
        // This is the CodeIgniter encryption format:
        // HMAC (128 chars hex) + base64(IV + ciphertext)

        $originalData = json_encode(['license' => 'test-license', 'customer_id' => 'test-client']);
        $encrypted = $this->encryptWithCodeIgniterFormat($originalData, $this->testKey);

        $encrypter = new LegacyEncrypter($this->testKey);
        $decrypted = $encrypter->decrypt($encrypted);

        $this->assertNotFalse($decrypted);
        $this->assertEquals($originalData, $decrypted);
    }

    public function test_returns_false_for_invalid_hmac(): void
    {
        $originalData = json_encode(['license' => 'test-license', 'customer_id' => 'test-client']);
        $encrypted = $this->encryptWithCodeIgniterFormat($originalData, $this->testKey);

        // Corrupt the HMAC
        $encrypted = 'x' . substr($encrypted, 1);

        $encrypter = new LegacyEncrypter($this->testKey);
        $decrypted = $encrypter->decrypt($encrypted);

        $this->assertFalse($decrypted);
    }

    public function test_returns_false_for_wrong_key(): void
    {
        $originalData = json_encode(['license' => 'test-license', 'customer_id' => 'test-client']);
        $encrypted = $this->encryptWithCodeIgniterFormat($originalData, $this->testKey);

        $encrypter = new LegacyEncrypter('wrong-key-here-12345');
        $decrypted = $encrypter->decrypt($encrypted);

        $this->assertFalse($decrypted);
    }

    public function test_returns_false_for_empty_data(): void
    {
        $encrypter = new LegacyEncrypter($this->testKey);
        $decrypted = $encrypter->decrypt('');

        $this->assertFalse($decrypted);
    }

    public function test_returns_false_for_too_short_data(): void
    {
        $encrypter = new LegacyEncrypter($this->testKey);
        $decrypted = $encrypter->decrypt('short');

        $this->assertFalse($decrypted);
    }

    protected function encryptWithCodeIgniterFormat(string $data, string $key): string
    {
        $cipher = 'aes-128-cbc';
        $ivSize = openssl_cipher_iv_length($cipher);

        // HKDF for encryption key
        $encryptionKey = $this->hkdf($key, 'sha512', null, strlen($key), 'encryption');

        // HKDF for HMAC key
        $hmacKey = $this->hkdf($key, 'sha512', null, null, 'authentication');

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
