<?php

namespace App\Core;

class Config
{
    private static ?array $data = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        if (self::$data === null) {
            self::$data = require BASE_PATH . '/config/app.php';
        }

        return self::$data[$key] ?? $default;
    }
}
