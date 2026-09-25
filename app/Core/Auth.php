<?php

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];

        return true;
    }

    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        return User::find((int) $_SESSION['user_id']);
    }

    /**
     * Ponto único de checagem de papel, no mesmo espírito do
     * Auth::requireRole() do EcoDiffusore: todo controller que precisa
     * restringir por papel chama isso primeiro, em vez de checar
     * $user['role'] espalhado pelo código.
     */
    public static function requireRole(array $roles): array
    {
        $user = self::user();
        if (!$user) {
            Router::redirect('/login');
        }

        if (!in_array($user['role'], $roles, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            exit;
        }

        return $user;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }
}
