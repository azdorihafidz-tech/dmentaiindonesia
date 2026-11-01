<?php

namespace App\Services;

class WhatsAppLink
{
    public function url(?string $phone, ?string $message = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        $url = 'https://wa.me/'.$digits;

        return filled($message) ? $url.'?text='.rawurlencode($message) : $url;
    }
}
