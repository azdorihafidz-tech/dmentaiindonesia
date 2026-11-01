<?php

namespace App\Services;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;

class GeoIpCountryLookup
{
    private ?Reader $reader = null;

    private bool $readerInitialized = false;

    /** @return array{country_code: string, country_name: string}|null */
    public function resolve(?string $ipAddress): ?array
    {
        if (! is_string($ipAddress)
            || filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        $reader = $this->reader();
        if (! $reader) {
            return null;
        }

        try {
            $country = $reader->country($ipAddress)->country;
        } catch (AddressNotFoundException) {
            return null;
        } catch (\Throwable) {
            return null;
        }

        $code = strtoupper((string) $country->isoCode);
        if (preg_match('/^[A-Z]{2}$/', $code) !== 1) {
            return null;
        }

        return [
            'country_code' => $code,
            'country_name' => (string) ($country->names['id'] ?? $country->name ?? $code),
        ];
    }

    private function reader(): ?Reader
    {
        if ($this->readerInitialized) {
            return $this->reader;
        }

        $this->readerInitialized = true;
        $path = (string) config('geoip.database_path');

        if ($path === '') {
            return null;
        }

        if (! str_starts_with($path, '/')
            && ! str_starts_with($path, '\\')
            && preg_match('/^[A-Za-z]:[\\\\\/]/', $path) !== 1) {
            $path = base_path($path);
        }

        if (! is_file($path) || ! is_readable($path)) {
            return null;
        }

        try {
            $this->reader = new Reader($path, ['id', 'en']);
        } catch (\Throwable) {
            $this->reader = null;
        }

        return $this->reader;
    }
}
