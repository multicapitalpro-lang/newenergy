<?php

namespace App\Models;

use App\Core\Database;

class SalesMaterial
{
    public static function all(): array
    {
        return Database::connection()->query('SELECT * FROM sales_materials ORDER BY created_at DESC')->fetchAll();
    }

    public static function create(string $title, ?string $description, ?string $filePath, ?string $link): int
    {
        Database::connection()->prepare(
            'INSERT INTO sales_materials (title, description, file_path, link, created_at) VALUES (?, ?, ?, ?, ?)'
        )->execute([$title, $description, $filePath, $link, date('Y-m-d H:i:s')]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM sales_materials WHERE id = ?')->execute([$id]);
    }
}
