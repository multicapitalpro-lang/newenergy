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

    /**
     * Escopa por hierarquia: passe User::scopedIds($user) do controller.
     * $sellerIds === null (caso do admin) = sem filtro, retorna tudo.
     */
    public static function forSellerIds(?array $sellerIds): array
    {
        $sql = "SELECT o.*, c.name AS client_name, u.name AS seller_name
                FROM orders o
                JOIN clients c ON c.id = o.client_id
                JOIN users u ON u.id = o.seller_id";
        $params = [];

        if ($sellerIds !== null) {
            $placeholders = implode(',', array_fill(0, count($sellerIds), '?'));
            $sql .= " WHERE o.seller_id IN ($placeholders)";
            $params = $sellerIds;
        }

        $sql .= ' ORDER BY o.created_at DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
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

    /** "Acompanhar a entrega" */
    public static function updateDelivery(int $id, string $trackingCode, string $deliveryStatus): void
    {
        $deliveredAt = $deliveryStatus === 'entregue' ? date('Y-m-d H:i:s') : null;

        Database::connection()->prepare(
            'UPDATE orders SET tracking_code = ?, delivery_status = ?, delivered_at = COALESCE(?, delivered_at) WHERE id = ?'
        )->execute([$trackingCode, $deliveryStatus, $deliveredAt, $id]);

        if ($deliveryStatus === 'entregue') {
            self::updateStatus($id, 'entregue');
        }
    }

    /** "Pós-venda de instalação" */
    public static function updateInstallation(int $id, string $status, ?string $date, ?string $notes): void
    {
        Database::connection()->prepare(
            'UPDATE orders SET installation_status = ?, installation_date = ?, installation_notes = ? WHERE id = ?'
        )->execute([$status, $date ?: null, $notes, $id]);
    }

    public static function documents(int $orderId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT d.*, u.name AS uploaded_by_name
             FROM order_documents d JOIN users u ON u.id = d.uploaded_by
             WHERE d.order_id = ? ORDER BY d.created_at DESC'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function addDocument(int $orderId, int $uploadedBy, string $name, ?string $filePath): int
    {
        Database::connection()->prepare(
            'INSERT INTO order_documents (order_id, uploaded_by, name, file_path, created_at) VALUES (?, ?, ?, ?, ?)'
        )->execute([$orderId, $uploadedBy, $name, $filePath, date('Y-m-d H:i:s')]);

        return (int) Database::connection()->lastInsertId();
    }

    public static function decideDocument(int $documentId, string $status, int $decidedBy): void
    {
        Database::connection()->prepare(
            'UPDATE order_documents SET status = ?, decided_by = ?, decided_at = ? WHERE id = ?'
        )->execute([$status, $decidedBy, date('Y-m-d H:i:s'), $documentId]);
    }
}
