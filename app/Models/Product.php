<?php

namespace App\Models;

use App\Core\Config;
use App\Core\Database;

class Product
{
    public static function all(bool $onlyActive = false): array
    {
        $sql = 'SELECT * FROM products';
        if ($onlyActive) {
            $sql .= ' WHERE active = 1';
        }
        $sql .= ' ORDER BY category ASC, name ASC';

        return array_map([self::class, 'withSellPrice'], Database::connection()->query($sql)->fetchAll());
    }

    public static function search(array $filters = []): array
    {
        $where = ['active = 1'];
        $params = [];

        if (!empty($filters['category'])) {
            $where[] = 'category = :category';
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(name LIKE :q OR short_description LIKE :q)';
            $params['q'] = '%' . $filters['q'] . '%';
        }

        $sql = 'SELECT * FROM products WHERE ' . implode(' AND ', $where) . ' ORDER BY category ASC, name ASC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        $products = array_map([self::class, 'withSellPrice'], $stmt->fetchAll());

        if (($filters['sort'] ?? null) === 'menor-preco') {
            usort($products, fn ($a, $b) => $a['sell_price_cents'] <=> $b['sell_price_cents']);
        } elseif (($filters['sort'] ?? null) === 'maior-preco') {
            usort($products, fn ($a, $b) => $b['sell_price_cents'] <=> $a['sell_price_cents']);
        } elseif (($filters['sort'] ?? null) === 'nome') {
            usort($products, fn ($a, $b) => strcmp($a['name'], $b['name']));
        }

        return $products;
    }

    public static function categories(): array
    {
        return Database::connection()
            ->query("SELECT DISTINCT category FROM products WHERE active = 1 AND category IS NOT NULL ORDER BY category ASC")
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        return $product ? self::withSellPrice($product) : null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO products
                (sku, viva_bess_sku, name, category, short_description, description, cost_price_cents, markup_percent, datasheet_path, image_path, active, created_at)
             VALUES
                (:sku, :viva_bess_sku, :name, :category, :short_description, :description, :cost_price_cents, :markup_percent, :datasheet_path, :image_path, :active, :created_at)'
        );

        $stmt->execute([
            'sku' => $data['sku'],
            'viva_bess_sku' => $data['viva_bess_sku'] ?? null,
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'cost_price_cents' => $data['cost_price_cents'],
            'markup_percent' => $data['markup_percent'] ?? null,
            'datasheet_path' => $data['datasheet_path'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'active' => $data['active'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    /**
     * Calcula o preço de venda da New Energy em cima do preço de distribuidor
     * da Viva Bess (cost_price_cents). É esse markup que é o ganho da SB New
     * Energy -- ver regra de negócio confirmada pelo usuário em 2026-09-14.
     */
    private static function withSellPrice(array $product): array
    {
        $markup = $product['markup_percent'] !== null
            ? (float) $product['markup_percent']
            : (float) Config::get('default_markup_percent', 30);

        $product['markup_percent_effective'] = $markup;
        $product['sell_price_cents'] = (int) round($product['cost_price_cents'] * (1 + $markup / 100));

        return $product;
    }
}
