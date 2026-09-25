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

    /** $sellerIds === null (admin) = sem filtro. */
    public static function forSellerIds(?array $sellerIds): array
    {
        $sql = 'SELECT c.*, u.name AS seller_name FROM clients c JOIN users u ON u.id = c.seller_id';
        $params = [];

        if ($sellerIds !== null) {
            $placeholders = implode(',', array_fill(0, count($sellerIds), '?'));
            $sql .= " WHERE c.seller_id IN ($placeholders)";
            $params = $sellerIds;
        }

        $sql .= ' ORDER BY c.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(int $sellerId, array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO clients (seller_id, name, document, email, phone, city, state, address, converted_from_lead_id, created_at)
             VALUES (:seller_id, :name, :document, :email, :phone, :city, :state, :address, :converted_from_lead_id, :created_at)'
        );

        $stmt->execute([
            'seller_id' => $sellerId,
            'name' => $data['name'],
            'document' => $data['document'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'address' => $data['address'] ?? null,
            'converted_from_lead_id' => $data['converted_from_lead_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function notes(int $clientId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT n.*, u.name AS user_name
             FROM client_notes n JOIN users u ON u.id = n.user_id
             WHERE n.client_id = ? ORDER BY n.created_at ASC'
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public static function addNote(int $clientId, int $userId, string $note): void
    {
        Database::connection()->prepare(
            'INSERT INTO client_notes (client_id, user_id, note, created_at) VALUES (?, ?, ?, ?)'
        )->execute([$clientId, $userId, $note, date('Y-m-d H:i:s')]);
    }

    public static function ordersOf(int $clientId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM orders WHERE client_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }
}
