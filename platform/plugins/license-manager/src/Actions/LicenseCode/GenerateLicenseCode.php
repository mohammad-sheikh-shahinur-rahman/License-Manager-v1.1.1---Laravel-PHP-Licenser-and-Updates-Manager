<?php

namespace Botble\LicenseManager\Actions\LicenseCode;

use Illuminate\Support\Str;

class GenerateLicenseCode
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function handle(): ?string
    {
        $format = setting('lm_license_code_format', 'uuid');
        $caseInsensitive = setting('lm_license_code_case_insensitive', 'uppercase');
        $licenseCode = '';

        switch ($format) {
            case 'uuid':
                $licenseCode = (string) Str::orderedUuid();

                break;
            case 'ulid':
                $licenseCode = (string) Str::ulid();

                break;
            case 'random':
                $length = min(setting('lm_license_code_random_length', 20), 100);
                $licenseCode = Str::random($length);

                break;
            case 'custom':
                $customFormat = setting('lm_license_code_custom_format', '%Z%Z%Z%Z-%Z%Z%Z%Z-%Z%Z%Z%Z-%Z%Z%Z%Z');
                $licenseCode = $this->generateCustomFormatLicenseCode($customFormat);

                break;
        }

        return match ($caseInsensitive) {
            'uppercase' => strtoupper($licenseCode),
            'lowercase' => strtolower($licenseCode),
            default => $licenseCode,
        };
    }

    protected function generateCustomFormatLicenseCode(string $format): string
    {
        $licenseCode = '';

        for ($i = 0; $i < strlen($format); $i++) {
            $char = $format[$i];

            if ($char === '%') {
                $i++;
                $char = $format[$i];

                $licenseCode .= match ($char) {
                    'X' => $this->randomNumber(),
                    'Y' => $this->randomLetter(),
                    'Z' => [$this->randomNumber(), $this->randomLetter()][random_int(0, 1)],
                    default => $char,
                };
            } else {
                $licenseCode .= $char;
            }
        }

        return $licenseCode;
    }

    protected function randomNumber(): int
    {
        return random_int(0, 9);
    }

    protected function randomLetter(): string
    {
        $letters = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k',
            'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v',
            'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
            'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        ];

        return $letters[random_int(0, count($letters) - 1)];
    }
}
