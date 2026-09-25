<?php

namespace App\Models;

use App\Core\Database;

class Client
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM clients WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function forSellerIds(array $sellerIds): array
    {
        $placeholders = implode(',', array_fill(0, count($sellerIds), '?'));
        $stmt = Database::connection()->prepare(
            "SELECT * FROM clients WHERE seller_id IN ($placeholders) ORDER BY name ASC"
        );
        $stmt->execute($sellerIds);
        return $stmt->fetchAll();
    }

    public static function create(int $sellerId, array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO clients (seller_id, name, document, email, phone, created_at)
             VALUES (:seller_id, :name, :document, :email, :phone, :created_at)'
        );

        $stmt->execute([
            'seller_id' => $sellerId,
            'name' => $data['name'],
            'document' => $data['document'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) Database::connection()->lastInsertId();
    }
}
