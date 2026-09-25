<?php

namespace App\Models;

use App\Core\Database;

class Goal
{
    /** $userIds === null (admin) = sem filtro. */
    public static function forUserIds(?array $userIds, ?string $period = null): array
    {
        $sql = 'SELECT g.*, u.name AS user_name FROM goals g JOIN users u ON u.id = g.user_id';
        $where = [];
        $params = [];

        if ($userIds !== null) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $where[] = "g.user_id IN ($placeholders)";
            $params = array_merge($params, $userIds);
        }

        if ($period) {
            $where[] = 'g.period = ?';
            $params[] = $period;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY g.period DESC, u.name ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(int $userId, string $period, string $targetType, float $targetValue, int $createdBy): int
    {
        Database::connection()->prepare(
            'INSERT INTO goals (user_id, period, target_type, target_value, created_by, created_at)
             VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$userId, $period, $targetType, $targetValue, $createdBy, date('Y-m-d H:i:s')]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM goals WHERE id = ?')->execute([$id]);
    }

    /** Receita realizada de um usuário num período (pra comparar com a meta). */
    public static function actualRevenue(int $userId, string $period): int
    {
        $driver = \App\Core\Config::get('db')['driver'] ?? 'sqlite';
        $dateExpr = $driver === 'mysql' ? "DATE_FORMAT(created_at, '%Y-%m')" : "strftime('%Y-%m', created_at)";

        $stmt = Database::connection()->prepare(
            "SELECT COALESCE(SUM(total_cents), 0) FROM orders
             WHERE seller_id = ? AND status != 'cancelado' AND $dateExpr = ?"
        );
        $stmt->execute([$userId, $period]);
        return (int) $stmt->fetchColumn();
    }

    public static function actualOrdersCount(int $userId, string $period): int
    {
        $driver = \App\Core\Config::get('db')['driver'] ?? 'sqlite';
        $dateExpr = $driver === 'mysql' ? "DATE_FORMAT(created_at, '%Y-%m')" : "strftime('%Y-%m', created_at)";

        $stmt = Database::connection()->prepare(
            "SELECT COUNT(*) FROM orders
             WHERE seller_id = ? AND status != 'cancelado' AND $dateExpr = ?"
        );
        $stmt->execute([$userId, $period]);
        return (int) $stmt->fetchColumn();
    }
}
