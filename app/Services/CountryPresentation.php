<?php

namespace App\Services;

class CountryPresentation
{
    public static function flag(?string $countryCode): string
    {
        $countryCode = strtoupper((string) $countryCode);

        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1) {
            return '🌐';
        }

        return mb_chr(127397 + ord($countryCode[0]), 'UTF-8')
            .mb_chr(127397 + ord($countryCode[1]), 'UTF-8');
    }

    public static function label(?string $countryCode, ?string $countryName): string
    {
        if (! $countryCode || preg_match('/^[A-Z]{2}$/', strtoupper($countryCode)) !== 1) {
            return '🌐 Belum diketahui';
        }

        return self::flag($countryCode).' '.($countryName ?: strtoupper($countryCode));
    }
}
