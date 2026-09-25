<?php

namespace App\Models;

use App\Core\Database;

class LeadExtensionRequest
{
    public static function forUserIds(?array $userIds): array
    {
        $sql = 'SELECT r.*, l.name AS lead_name, u.name AS requested_by_name
                FROM lead_extension_requests r
                JOIN leads l ON l.id = r.lead_id
                JOIN users u ON u.id = r.requested_by';
        $params = [];

        if ($userIds !== null) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $sql .= " WHERE r.requested_by IN ($placeholders)";
            $params = $userIds;
        }

        $sql .= " ORDER BY r.status = 'pendente' DESC, r.created_at DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(int $leadId, int $requestedBy, string $reason): int
    {
        Database::connection()->prepare(
            'INSERT INTO lead_extension_requests (lead_id, requested_by, reason, created_at)
             VALUES (?, ?, ?, ?)'
        )->execute([$leadId, $requestedBy, $reason, date('Y-m-d H:i:s')]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function decide(int $id, string $status, int $decidedBy): void
    {
        Database::connection()->prepare(
            'UPDATE lead_extension_requests SET status = ?, decided_by = ?, decided_at = ? WHERE id = ?'
        )->execute([$status, $decidedBy, date('Y-m-d H:i:s'), $id]);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM lead_extension_requests WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
