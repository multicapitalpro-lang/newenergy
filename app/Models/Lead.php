<?php

namespace App\Models;

use App\Core\Database;
use App\Core\Roles;

class Lead
{
    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT l.*, u.name AS assigned_to_name
             FROM leads l
             LEFT JOIN users u ON u.id = l.assigned_to_user_id
             WHERE l.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** $userIds === null (admin) = sem filtro. */
    public static function forUserIds(?array $userIds): array
    {
        $sql = 'SELECT l.*, u.name AS assigned_to_name
                FROM leads l
                LEFT JOIN users u ON u.id = l.assigned_to_user_id';
        $params = [];

        if ($userIds !== null) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $sql .= " WHERE l.assigned_to_user_id IN ($placeholders)";
            $params = $userIds;
        }

        $sql .= ' ORDER BY l.created_at DESC';

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function notes(int $leadId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT n.*, u.name AS user_name
             FROM lead_notes n JOIN users u ON u.id = n.user_id
             WHERE n.lead_id = ? ORDER BY n.created_at ASC'
        );
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }

    public static function addNote(int $leadId, int $userId, string $note): void
    {
        Database::connection()->prepare(
            'INSERT INTO lead_notes (lead_id, user_id, note, created_at) VALUES (?, ?, ?, ?)'
        )->execute([$leadId, $userId, $note, date('Y-m-d H:i:s')]);
    }

    public static function updateStatus(int $id, string $status): void
    {
        Database::connection()
            ->prepare('UPDATE leads SET status = ? WHERE id = ?')
            ->execute([$status, $id]);
    }

    /**
     * Cria o lead e já roteia pro vendedor certo (Lead::routeTo()). Retorna
     * o id do lead criado.
     */
    public static function create(array $data): int
    {
        $pdo = Database::connection();

        $assignedTo = self::pickAssignee();

        $stmt = $pdo->prepare(
            'INSERT INTO leads (name, phone, email, city, state, message, source, assigned_to_user_id, assigned_at, created_at)
             VALUES (:name, :phone, :email, :city, :state, :message, :source, :assigned_to_user_id, :assigned_at, :created_at)'
        );

        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'message' => $data['message'] ?? null,
            'source' => $data['source'] ?? 'site',
            'assigned_to_user_id' => $assignedTo,
            'assigned_at' => $assignedTo ? $now : null,
            'created_at' => $now,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Roteamento simplificado (sem geolocalização ainda) do GeoMatch do
     * EcoDiffusore: round robin em duas camadas.
     * 1) escolhe o Licenciado ativo que ficou mais tempo sem receber lead
     *    (MAX(created_at) dos leads da downline dele, mais antigo primeiro;
     *    nunca recebeu = prioridade máxima).
     * 2) dentro da downline desse licenciado (ele mesmo + gestor + vendedor),
     *    escolhe quem ficou mais tempo sem receber lead.
     * Sem licenciado ativo elegível -> usa o fallback de lead_routing_settings.
     */
    public static function pickAssignee(): ?int
    {
        $pdo = Database::connection();

        $licenciados = $pdo->query("SELECT id FROM users WHERE role = 'licenciado' AND status = 'active'")->fetchAll(\PDO::FETCH_COLUMN);
        if (empty($licenciados)) {
            return self::fallbackUserId();
        }

        $bestLicenciado = null;
        $bestLicenciadoTime = null;
        foreach ($licenciados as $licenciadoId) {
            $downline = User::downlineIds((int) $licenciadoId);
            $lastAssigned = self::lastAssignedAt($downline);
            if ($bestLicenciadoTime === null || $lastAssigned < $bestLicenciadoTime) {
                $bestLicenciadoTime = $lastAssigned;
                $bestLicenciado = (int) $licenciadoId;
            }
        }

        if ($bestLicenciado === null) {
            return self::fallbackUserId();
        }

        $candidates = array_values(array_filter(
            User::downlineIds($bestLicenciado),
            function (int $id) use ($pdo) {
                $u = User::find($id);
                return $u && $u['status'] === 'active' && in_array($u['role'], Roles::SELLER_ROLES, true);
            }
        ));

        if (empty($candidates)) {
            return self::fallbackUserId();
        }

        $bestUser = null;
        $bestUserTime = null;
        foreach ($candidates as $candidateId) {
            $lastAssigned = self::lastAssignedAt([$candidateId]);
            if ($bestUserTime === null || $lastAssigned < $bestUserTime) {
                $bestUserTime = $lastAssigned;
                $bestUser = $candidateId;
            }
        }

        return $bestUser;
    }

    private static function lastAssignedAt(array $userIds): string
    {
        if (empty($userIds)) {
            return '0000-00-00 00:00:00';
        }

        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = Database::connection()->prepare(
            "SELECT MAX(assigned_at) FROM leads WHERE assigned_to_user_id IN ($placeholders)"
        );
        $stmt->execute($userIds);
        return $stmt->fetchColumn() ?: '0000-00-00 00:00:00';
    }

    private static function fallbackUserId(): ?int
    {
        $stmt = Database::connection()->query('SELECT fallback_user_id FROM lead_routing_settings WHERE id = 1');
        $value = $stmt->fetchColumn();
        return $value !== false && $value !== null ? (int) $value : null;
    }

    public static function getFallbackUserId(): ?int
    {
        return self::fallbackUserId();
    }

    public static function setFallbackUserId(?int $userId): void
    {
        $pdo = Database::connection();
        $pdo->exec('INSERT OR IGNORE INTO lead_routing_settings (id, fallback_user_id) VALUES (1, NULL)');
        $pdo->prepare('UPDATE lead_routing_settings SET fallback_user_id = ? WHERE id = 1')->execute([$userId]);
    }
}
