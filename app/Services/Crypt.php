<?php

namespace App\Services;

/**
 * Keeps a secret (the SMTP password) unreadable in the database.
 * AES-256-GCM with a key made from APP_KEY in .env. Without APP_KEY a key made from the database name is used,
 * so set APP_KEY in .env for a real server (any long random text).
 */
class Crypt
{
    private static function key(): string
    {
        return hash('sha256', (string) (env('APP_KEY') ?: 'uptms-' . env('DB_NAME', 'uptms_db')), true);
    }

    public static function encrypt(string $plain): string
    {
        if ($plain === '') {
            return '';
        }

        $iv = random_bytes(12);
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        return base64_encode($iv . $tag . $cipher);
    }

    // Returns '' when the text is empty or was made with another key.
    public static function decrypt(string $stored): string
    {
        $raw = base64_decode($stored, true);
        if ($stored === '' || $raw === false || strlen($raw) < 29) {
            return '';
        }

        $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));

        return $plain === false ? '' : $plain;
    }
}
