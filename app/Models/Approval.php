<?php

namespace App\Models;

use App\Core\Database;

/**
 * "Liberação de preço" -- polimórfico (approvable_type/approvable_id) igual
 * ao Approval do EcoDiffusore, só que por enquanto só 'quote' usa isso.
 */
class Approval
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM approvals WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** $userIds === null (admin) = sem filtro. */
    public static function forUserIds(?array $userIds): array
    {
        $sql = 'SELECT a.*, u.name AS requested_by_name
                FROM approvals a JOIN users u ON u.id = a.requested_by';
        $params = [];

        if ($userIds !== null) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $sql .= " WHERE a.requested_by IN ($placeholders)";
            $params = $userIds;
        }

        $sql .= " ORDER BY a.status = 'pendente' DESC, a.created_at DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function decide(int $id, string $status, int $decidedBy): void
    {
        Database::connection()->prepare(
            'UPDATE approvals SET status = ?, decided_by = ?, decided_at = ? WHERE id = ?'
        )->execute([$status, $decidedBy, date('Y-m-d H:i:s'), $id]);
    }
}
