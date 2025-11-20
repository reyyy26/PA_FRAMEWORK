<?php

namespace App\Support;

use Illuminate\Support\Str;

class Totp
{
    public static function generateSecret(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        return collect(range(1, $length))
            ->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])
            ->implode('');
    }

    public static function generateCode(string $secret, int $period = 30, int $digits = 6, ?int $timestamp = null): string
    {
        $counter = intdiv(($timestamp ?? time()), $period);
        $key = self::base32Decode($secret);
        $binaryCounter = pack('N*', 0, $counter);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord(substr($hash, -1)) & 0x0F;

        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        $mod = 10 ** $digits;

        return str_pad((string) ($value % $mod), $digits, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code, int $window = 1, int $period = 30, int $digits = 6): bool
    {
        $normalized = preg_replace('/\s+/', '', $code);

        if (!ctype_digit($normalized)) {
            return false;
        }

        $timeSlice = intdiv(time(), $period);

        for ($i = -$window; $i <= $window; $i++) {
            $candidate = self::generateCode($secret, $period, $digits, ($timeSlice + $i) * $period);
            if (hash_equals($candidate, $normalized)) {
                return true;
            }
        }

        return false;
    }

    public static function qrUri(string $label, string $issuer, string $secret): string
    {
        $encodedLabel = rawurlencode($label);
        $encodedIssuer = rawurlencode($issuer);

        return "otpauth://totp/{$encodedIssuer}:{$encodedLabel}?secret={$secret}&issuer={$encodedIssuer}";
    }

    private static function base32Decode(string $value): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $value = strtoupper(preg_replace('/[^A-Z2-7]/', '', $value));
        $binaryString = '';

        foreach (str_split($value) as $char) {
            $position = strpos($alphabet, $char);
            if ($position === false) {
                continue;
            }

            $binaryString .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binaryString, 8) as $chunk) {
            if (strlen($chunk) < 8) {
                continue;
            }
            $bytes .= chr(bindec($chunk));
        }

        return $bytes;
    }
}
