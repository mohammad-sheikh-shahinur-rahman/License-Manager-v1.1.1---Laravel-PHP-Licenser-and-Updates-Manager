<?php

namespace Botble\LicenseManager\Encryption;

use RuntimeException;

/**
 * Provides compatibility with CodeIgniter's CI_Encryption library.
 *
 * This class can decrypt data encrypted by legacy system (CodeIgniter 3.x)
 * which uses HKDF key derivation + HMAC-SHA512 authentication.
 *
 * Default legacy encryption settings:
 * - Cipher: aes-128-cbc
 * - HMAC: sha512
 * - Base64: true
 */
class LegacyEncrypter
{
    protected string $cipher = 'aes-128-cbc';

    protected string $hmacDigest = 'sha512';

    protected int $hmacSize = 128; // SHA-512 hex = 64 bytes * 2 = 128 chars

    public function __construct(
        protected string $key
    ) {
        if (empty($this->key)) {
            throw new RuntimeException('Encryption key is required.');
        }
    }

    public function decrypt(string $data): string|false
    {
        if (empty($data)) {
            return false;
        }

        // Extract HMAC (first 128 chars for sha512 hex)
        if (strlen($data) <= $this->hmacSize) {
            return false;
        }

        $hmacInput = substr($data, 0, $this->hmacSize);
        $encryptedData = substr($data, $this->hmacSize);

        // Derive HMAC key using HKDF
        $hmacKey = $this->hkdf($this->key, $this->hmacDigest, null, null, 'authentication');

        // Verify HMAC (hex format since base64 is true)
        $hmacCheck = hash_hmac($this->hmacDigest, $encryptedData, $hmacKey, false);

        if (! $this->hashEquals($hmacInput, $hmacCheck)) {
            return false;
        }

        // Base64 decode the payload
        $decoded = base64_decode($encryptedData, true);

        if ($decoded === false) {
            return false;
        }

        // Derive encryption key using HKDF
        $encryptionKey = $this->hkdf($this->key, 'sha512', null, strlen($this->key), 'encryption');

        // Get IV size for the cipher
        $ivSize = openssl_cipher_iv_length($this->cipher);

        if ($ivSize === false || strlen($decoded) <= $ivSize) {
            return false;
        }

        // Extract IV and ciphertext
        $iv = substr($decoded, 0, $ivSize);
        $ciphertext = substr($decoded, $ivSize);

        // Decrypt
        $decrypted = openssl_decrypt(
            $ciphertext,
            $this->cipher,
            $encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        return $decrypted;
    }

    /**
     * HKDF key derivation function (RFC 5869).
     * Compatible with CodeIgniter's implementation.
     */
    protected function hkdf(
        string $key,
        string $digest = 'sha512',
        ?string $salt = null,
        ?int $length = null,
        string $info = ''
    ): string {
        $digestSizes = [
            'sha224' => 28,
            'sha256' => 32,
            'sha384' => 48,
            'sha512' => 64,
        ];

        if (! isset($digestSizes[$digest])) {
            throw new RuntimeException("Unsupported digest: {$digest}");
        }

        $digestSize = $digestSizes[$digest];

        if (empty($length) || ! is_int($length)) {
            $length = $digestSize;
        } elseif ($length > (255 * $digestSize)) {
            throw new RuntimeException('HKDF length too large.');
        }

        // Salt defaults to a string of zeroes
        if (empty($salt)) {
            $salt = str_repeat("\0", $digestSize);
        }

        // Extract
        $prk = hash_hmac($digest, $key, $salt, true);

        // Expand
        $derivedKey = '';
        $keyBlock = '';

        for ($blockIndex = 1; strlen($derivedKey) < $length; $blockIndex++) {
            $keyBlock = hash_hmac($digest, $keyBlock . $info . chr($blockIndex), $prk, true);
            $derivedKey .= $keyBlock;
        }

        return substr($derivedKey, 0, $length);
    }

    /**
     * Timing-safe string comparison.
     */
    protected function hashEquals(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }
}
