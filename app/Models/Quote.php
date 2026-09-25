<?php

namespace App\Models;

use App\Core\Database;

class Quote
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT q.*, c.name AS client_name, u.name AS seller_name
             FROM quotes q
             JOIN clients c ON c.id = q.client_id
             JOIN users u ON u.id = q.seller_id
             WHERE q.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** $sellerIds === null (admin) = sem filtro. */
    public static function forSellerIds(?array $sellerIds): array
    {
        $sql = 'SELECT q.*, c.name AS client_name, u.name AS seller_name
                FROM quotes q
                JOIN clients c ON c.id = q.client_id
                JOIN users u ON u.id = q.seller_id';
        $params = [];

        if ($sellerIds !== null) {
            $placeholders = implode(',', array_fill(0, count($sellerIds), '?'));
            $sql .= " WHERE q.seller_id IN ($placeholders)";
            $params = $sellerIds;
        }

        $sql .= ' ORDER BY q.created_at DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function items(int $quoteId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT qi.*, p.name AS product_name, p.sku
             FROM quote_items qi JOIN products p ON p.id = qi.product_id
             WHERE qi.quote_id = ?'
        );
        $stmt->execute([$quoteId]);
        return $stmt->fetchAll();
    }

    /**
     * Cria o orçamento com os itens. Se o desconto passar do limite do papel
     * de quem está criando, o status já nasce 'aguardando_aprovacao' e uma
     * Approval pendente é criada junto -- ver App\Core\DiscountLimits.
     */
    public static function create(int $clientId, int $sellerId, array $items, float $discountPercent, bool $needsApproval): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['unit_price_cents'] * $item['quantity'];
            }
            $total = (int) round($subtotal * (1 - $discountPercent / 100));

            $status = $needsApproval ? 'aguardando_aprovacao' : 'aberto';

            $stmt = $pdo->prepare(
                'INSERT INTO quotes (client_id, seller_id, status, discount_percent, total_cents, created_at)
                 VALUES (:client_id, :seller_id, :status, :discount_percent, :total_cents, :created_at)'
            );
            $stmt->execute([
                'client_id' => $clientId,
                'seller_id' => $sellerId,
                'status' => $status,
                'discount_percent' => $discountPercent,
                'total_cents' => $total,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $quoteId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO quote_items (quote_id, product_id, quantity, unit_price_cents, subtotal_cents)
                 VALUES (?, ?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $itemStmt->execute([
                    $quoteId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['unit_price_cents'],
                    $item['unit_price_cents'] * $item['quantity'],
                ]);
            }

            if ($needsApproval) {
                $pdo->prepare(
                    "INSERT INTO approvals (approvable_type, approvable_id, requested_discount_pct, requested_by, created_at)
                     VALUES ('quote', ?, ?, ?, ?)"
                )->execute([$quoteId, $discountPercent, $sellerId, date('Y-m-d H:i:s')]);
            }

            $pdo->commit();
            return $quoteId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::connection()
            ->prepare('UPDATE quotes SET status = ? WHERE id = ?')
            ->execute([$status, $id]);
    }

    public static function markConverted(int $id, int $orderId): void
    {
        Database::connection()
            ->prepare("UPDATE quotes SET status = 'convertido', converted_order_id = ? WHERE id = ?")
            ->execute([$orderId, $id]);
    }
}
