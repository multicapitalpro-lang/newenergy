<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $db = Config::get('db');
            $driver = $db['driver'] ?? 'sqlite';

            $dsn = $driver === 'mysql'
                ? sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset'] ?? 'utf8mb4')
                : 'sqlite:' . $db['sqlite_path'];

            try {
                self::$instance = new PDO(
                    $dsn,
                    $driver === 'mysql' ? $db['user'] : null,
                    $driver === 'mysql' ? $db['pass'] : null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );

                if ($driver === 'sqlite') {
                    self::$instance->exec('PRAGMA foreign_keys = ON');
                }
            } catch (PDOException $e) {
                error_log('DB connection error: ' . $e->getMessage());
                throw new PDOException('Não foi possível conectar ao banco de dados.');
            }
        }

        return self::$instance;
    }
}
