<?php

namespace App\Models;

use App\Core\Database;

class PaymentMethod
{
    public static function all(bool $onlyActive = false): array
    {
        $sql = 'SELECT * FROM payment_methods';
        if ($onlyActive) {
            $sql .= ' WHERE active = 1';
        }
        $sql .= ' ORDER BY name ASC';

        return Database::connection()->query($sql)->fetchAll();
    }

    public static function create(array $data): int
    {
        Database::connection()->prepare(
            'INSERT INTO payment_methods (name, description, max_installments, interest_rate_percent, created_at)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['max_installments'] ?? 1,
            $data['interest_rate_percent'] ?? 0,
            date('Y-m-d H:i:s'),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM payment_methods WHERE id = ?')->execute([$id]);
    }
}
