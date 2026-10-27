<?php

namespace App\Models;

use App\Config\Database;
use PDO;

/**
 * The system settings (table settings: name = value). The whole table is read once per request.
 * If the table or a row is missing, the default given by the caller is used, so the system never breaks.
 */
class Setting
{
    private static ?array $all = null;

    public static function all(): array
    {
        if (self::$all === null) {
            try {
                self::$all = Database::connect()->query("SELECT name, value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
            } catch (\Throwable $e) {
                self::$all = [];
            }
        }

        return self::$all;
    }

    public static function get(string $name, string $default = ''): string
    {
        return self::all()[$name] ?? $default;
    }

    public static function int(string $name, int $default): int
    {
        $value = self::all()[$name] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }

    public static function bool(string $name): bool
    {
        return self::get($name, '0') === '1';
    }

    // Saves several settings at once (name => value).
    public static function saveMany(array $values): void
    {
        $stmt = Database::connect()->prepare(
            "INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)"
        );

        foreach ($values as $name => $value) {
            $stmt->execute([$name, (string) $value]);
        }

        self::$all = null;   // read again next time
    }
}
