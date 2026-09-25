<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\User;

class TeamController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::MANAGEMENT);

        View::render('team/index', [
            'user' => $user,
            'team' => User::teamOf((int) $user['id']),
        ], 'loja');
    }

    public function store(): void
    {
        $user = Auth::requireRole(Roles::USER_MANAGEMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/minha-equipe');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name !== '' && $email !== '' && strlen($password) >= 6 && !User::findByEmail($email)) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'vendedor',
                'manager_id' => $user['id'],
                'phone' => trim($_POST['phone'] ?? '') ?: null,
            ]);
        }

        Router::redirect('/minha-equipe');
    }
}
