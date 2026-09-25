<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (name, email, password_hash, role, manager_id, company_name, document, phone, created_at)
             VALUES (:name, :email, :password_hash, :role, :manager_id, :company_name, :document, :phone, :created_at)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'licenciado',
            'manager_id' => $data['manager_id'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'document' => $data['document'] ?? null,
            'phone' => $data['phone'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    /** Vendedores cadastrados sob um licenciado. */
    public static function teamOf(int $managerId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM users WHERE manager_id = ? ORDER BY name ASC'
        );
        $stmt->execute([$managerId]);
        return $stmt->fetchAll();
    }

    /**
     * Todos os ids "abaixo" de um usuário na hierarquia (ele mesmo + sua
     * equipe), pra escopar pedidos/relatórios. Só 1 nível hoje (licenciado ->
     * vendedor), mas escrito como um loop igual ao downlineIds() do
     * EcoDiffusore pra já ficar pronto se a hierarquia crescer depois.
     */
    public static function downlineIds(int $userId): array
    {
        $ids = [$userId];
        $frontier = [$userId];

        for ($depth = 0; $depth < 5 && !empty($frontier); $depth++) {
            $placeholders = implode(',', array_fill(0, count($frontier), '?'));
            $stmt = Database::connection()->prepare(
                "SELECT id FROM users WHERE manager_id IN ($placeholders)"
            );
            $stmt->execute($frontier);
            $frontier = array_column($stmt->fetchAll(), 'id');
            $ids = array_merge($ids, $frontier);
        }

        return array_values(array_unique($ids));
    }
}
