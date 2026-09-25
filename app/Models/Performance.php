<?php

namespace App\Models;

use App\Core\Database;

/**
 * Consultas de desempenho (ranking de vendedores, funil de conversão).
 * Sempre escopado pelos ids que App\Models\User::scopedIds() devolve.
 */
class Performance
{
    /** $userIds === null (admin) = sem filtro. */
    public static function ranking(?array $userIds, ?string $period = null): array
    {
        $params = [];
        $dateFilter = '';
        if ($period) {
            $dateFilter = 'AND strftime(\'%Y-%m\', o.created_at) = :period';
            $params['period'] = $period;
        }

        $where = '';
        if ($userIds !== null) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $where = "WHERE u.id IN ($placeholders)";
            $params = array_merge($params, $userIds);
        }

        // SQLite usa strftime; troca pra DATE_FORMAT quando for MySQL.
        $driver = \App\Core\Config::get('db')['driver'] ?? 'sqlite';
        if ($driver === 'mysql') {
            $dateFilter = $period ? "AND DATE_FORMAT(o.created_at, '%Y-%m') = :period" : '';
        }

        $sql = "SELECT u.id, u.name, u.role,
                    COUNT(DISTINCT o.id) AS orders_count,
                    COALESCE(SUM(o.total_cents), 0) AS revenue_cents,
                    (SELECT COUNT(*) FROM clients c WHERE c.seller_id = u.id) AS clients_count,
                    (SELECT COUNT(*) FROM leads l WHERE l.assigned_to_user_id = u.id) AS leads_count
                FROM users u
                LEFT JOIN orders o ON o.seller_id = u.id AND o.status != 'cancelado' $dateFilter
                $where
                GROUP BY u.id, u.name, u.role
                HAVING u.role IN ('licenciado', 'gestor', 'vendedor')
                ORDER BY revenue_cents DESC";

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Funil de conversão: Lead -> Contatado -> Cliente -> Orçamento -> Pedido. */
    public static function funnel(?array $userIds): array
    {
        $pdo = Database::connection();

        $leadWhere = '';
        $clientWhere = '';
        $quoteWhere = '';
        $orderWhere = '';
        $params = [];

        if ($userIds !== null) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $leadWhere = "AND assigned_to_user_id IN ($placeholders)";
            $clientWhere = "AND seller_id IN ($placeholders)";
            $quoteWhere = "AND seller_id IN ($placeholders)";
            $orderWhere = "AND seller_id IN ($placeholders)";
        }

        $count = function (string $sql, array $ids) use ($pdo) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($ids);
            return (int) $stmt->fetchColumn();
        };

        $ids = $userIds ?? [];

        return [
            'leads' => $count("SELECT COUNT(*) FROM leads WHERE 1=1 $leadWhere", $ids),
            'leads_contatados' => $count("SELECT COUNT(*) FROM leads WHERE status != 'novo' $leadWhere", $ids),
            'clientes' => $count("SELECT COUNT(*) FROM clients WHERE 1=1 $clientWhere", $ids),
            'orcamentos' => $count("SELECT COUNT(*) FROM quotes WHERE 1=1 $quoteWhere", $ids),
            'orcamentos_aprovados' => $count("SELECT COUNT(*) FROM quotes WHERE status IN ('aprovado', 'convertido') $quoteWhere", $ids),
            'pedidos' => $count("SELECT COUNT(*) FROM orders WHERE 1=1 $orderWhere", $ids),
        ];
    }
}
