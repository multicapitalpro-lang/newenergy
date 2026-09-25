<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Roles;

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

    public static function allByRole(string $role): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE role = ? ORDER BY name ASC');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (name, email, password_hash, role, manager_id, supervisor_id, company_name, document, phone, onboarding_status, contract_status, created_at)
             VALUES (:name, :email, :password_hash, :role, :manager_id, :supervisor_id, :company_name, :document, :phone, :onboarding_status, :contract_status, :created_at)'
        );

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'licenciado',
            'manager_id' => $data['manager_id'] ?? null,
            'supervisor_id' => $data['supervisor_id'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'document' => $data['document'] ?? null,
            'phone' => $data['phone'] ?? null,
            // licenciado se auto-cadastrando começa aguardando aprovação; gestor/vendedor
            // cadastrado pelo licenciado começa com contrato pendente de envio -- ver
            // AuthController::register() e TeamController::store().
            'onboarding_status' => $data['onboarding_status'] ?? 'ativo',
            'contract_status' => $data['contract_status'] ?? 'aprovado',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    /** Gestor/Vendedor cadastrados sob um licenciado (mesmo manager_id). */
    public static function teamOf(int $managerId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM users WHERE manager_id = ? ORDER BY name ASC'
        );
        $stmt->execute([$managerId]);
        return $stmt->fetchAll();
    }

    /** Licenciados atribuídos a um Supervisor (users.supervisor_id). */
    public static function licenciadosOf(int $supervisorId): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM users WHERE supervisor_id = ? AND role = 'licenciado' ORDER BY name ASC"
        );
        $stmt->execute([$supervisorId]);
        return $stmt->fetchAll();
    }

    /**
     * Todos os ids "abaixo" de um usuário na árvore de equipe (manager_id),
     * ele mesmo incluído -- pra escopar pedidos/leads/orçamentos. Percorrido
     * em BFS igual ao downlineIds() do EcoDiffusore.
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

    /**
     * Ids de todos os licenciados atribuídos a um Supervisor + as equipes
     * deles (downlineIds de cada um) -- escopo de um Supervisor.
     */
    public static function supervisedIds(int $supervisorId): array
    {
        $ids = [$supervisorId];
        foreach (self::licenciadosOf($supervisorId) as $licenciado) {
            $ids = array_merge($ids, self::downlineIds((int) $licenciado['id']));
        }
        return array_values(array_unique($ids));
    }

    /**
     * Escopo nacional de um Gerente: todo mundo que vende, na rede inteira.
     * Gerente é suporte nacional (topo abaixo do admin), então não fica
     * restrito a uma sub-árvore -- diferente do Supervisor, que só vê os
     * licenciados atribuídos a ele.
     */
    public static function nationalIds(int $gerenteId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT id FROM users WHERE role IN (?, ?, ?, ?)'
        );
        $stmt->execute([Roles::GERENTE, Roles::LICENCIADO, Roles::GESTOR, Roles::VENDEDOR]);
        $ids = array_column($stmt->fetchAll(), 'id');
        $ids[] = $gerenteId;
        return array_values(array_unique($ids));
    }

    /**
     * Resolve o conjunto de user ids que um usuário pode ver, de acordo com
     * o papel dele -- ponto único usado por Order/Lead/Quote pra escopar
     * consultas, no mesmo espírito do padrão de escopo do EcoDiffusore
     * (Order/Quote/ClientController todos repetem essa mesma lógica lá).
     */
    public static function scopedIds(array $user): ?array
    {
        return match ($user['role']) {
            'admin' => null, // null = sem filtro, vê tudo
            'gerente' => self::nationalIds((int) $user['id']),
            'supervisor' => self::supervisedIds((int) $user['id']),
            'licenciado', 'gestor' => self::downlineIds((int) $user['id']),
            default => [(int) $user['id']], // vendedor: só o próprio
        };
    }

    public static function assignSupervisor(int $licenciadoId, ?int $supervisorId): void
    {
        Database::connection()
            ->prepare("UPDATE users SET supervisor_id = ? WHERE id = ? AND role = 'licenciado'")
            ->execute([$supervisorId, $licenciadoId]);
    }

    /** "Aprovação de cadastros": licenciados que se auto-cadastraram e aguardam liberação. */
    public static function pendingOnboarding(): array
    {
        $stmt = Database::connection()->prepare(
            "SELECT * FROM users WHERE role = 'licenciado' AND onboarding_status = 'aguardando_aprovacao' ORDER BY created_at ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function decideOnboarding(int $userId, string $decision, ?string $reason): void
    {
        if ($decision === 'aprovado') {
            Database::connection()
                ->prepare("UPDATE users SET onboarding_status = 'ativo', onboarding_rejection_reason = NULL WHERE id = ?")
                ->execute([$userId]);
        } else {
            Database::connection()
                ->prepare("UPDATE users SET onboarding_status = 'reprovado', onboarding_rejection_reason = ? WHERE id = ?")
                ->execute([$reason, $userId]);
        }
    }

    /**
     * "Aprovar vendedores": Gestor/Vendedor cadastrado por um Licenciado sobe um
     * contrato assinado (sem ClickSign aqui, upload manual) pra liberar o acesso.
     * $licenciadoId null (admin) = sem filtro; senão só a equipe direta dele
     * (manager_id aponta direto pro licenciado, TeamController::store() nunca
     * encadeia via gestor).
     */
    public static function pendingContracts(?int $licenciadoId): array
    {
        $sql = "SELECT * FROM users WHERE contract_status = 'aguardando_aprovacao' AND role IN ('gestor', 'vendedor')";
        $params = [];

        if ($licenciadoId !== null) {
            $sql .= ' AND manager_id = ?';
            $params[] = $licenciadoId;
        }

        $sql .= ' ORDER BY created_at ASC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function updateContract(int $userId, string $path): void
    {
        Database::connection()
            ->prepare("UPDATE users SET contract_path = ?, contract_status = 'aguardando_aprovacao', contract_rejection_reason = NULL WHERE id = ?")
            ->execute([$path, $userId]);
    }

    public static function decideContract(int $userId, string $decision, ?string $reason): void
    {
        if ($decision === 'aprovado') {
            Database::connection()
                ->prepare("UPDATE users SET contract_status = 'aprovado', contract_rejection_reason = NULL WHERE id = ?")
                ->execute([$userId]);
        } else {
            Database::connection()
                ->prepare("UPDATE users SET contract_status = 'reprovado', contract_rejection_reason = ? WHERE id = ?")
                ->execute([$reason, $userId]);
        }
    }
}
