<?php

namespace App\Models;

use App\Core\Database;

class Order
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT o.*, c.name AS client_name, u.name AS seller_name
             FROM orders o
             JOIN clients c ON c.id = o.client_id
             JOIN users u ON u.id = o.seller_id
             WHERE o.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function items(int $orderId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT oi.*, p.name AS product_name, p.sku
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             WHERE oi.order_id = ?'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /** Escopa por hierarquia: passe User::downlineIds($user['id']) do controller. */
    public static function forSellerIds(array $sellerIds): array
    {
        $placeholders = implode(',', array_fill(0, count($sellerIds), '?'));
        $stmt = Database::connection()->prepare(
            "SELECT o.*, c.name AS client_name, u.name AS seller_name
             FROM orders o
             JOIN clients c ON c.id = o.client_id
             JOIN users u ON u.id = o.seller_id
             WHERE o.seller_id IN ($placeholders)
             ORDER BY o.created_at DESC"
        );
        $stmt->execute($sellerIds);
        return $stmt->fetchAll();
    }

    /**
     * Cria o pedido com os itens dentro de uma transação e já congela o
     * unit_price_cents de cada item (preço da New Energy no momento da
     * venda) -- mesmo cuidado do EcoDiffusore de nunca recalcular depois.
     */
    public static function create(int $clientId, int $sellerId, array $items): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $total = 0;
            foreach ($items as $item) {
                $total += $item['unit_price_cents'] * $item['quantity'];
            }

            $stmt = $pdo->prepare(
                'INSERT INTO orders (client_id, seller_id, status, total_cents, created_at)
                 VALUES (:client_id, :seller_id, :status, :total_cents, :created_at)'
            );
            $stmt->execute([
                'client_id' => $clientId,
                'seller_id' => $sellerId,
                'status' => 'pendente',
                'total_cents' => $total,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, quantity, unit_price_cents, subtotal_cents)
                 VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['unit_price_cents'],
                    $item['unit_price_cents'] * $item['quantity'],
                ]);
            }

            $pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::connection()
            ->prepare('UPDATE orders SET status = ? WHERE id = ?')
            ->execute([$status, $id]);
    }
}
