<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Client;
use App\Models\User;

class ClientController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('clients/index', [
            'user' => $user,
            'clients' => Client::forSellerIds(User::scopedIds($user)),
        ], 'loja');
    }

    public function show(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);
        $client = Client::find((int) $id);

        if (!$client) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/errors/404.php';
            return;
        }

        $scoped = User::scopedIds($user);
        if ($scoped !== null && !in_array((int) $client['seller_id'], $scoped, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            return;
        }

        View::render('clients/show', [
            'user' => $user,
            'client' => $client,
            'notes' => Client::notes((int) $id),
            'orders' => Client::ordersOf((int) $id),
        ], 'loja');
    }

    public function addNote(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/clientes/' . $id);
        }

        $note = trim($_POST['note'] ?? '');
        if ($note !== '') {
            Client::addNote((int) $id, (int) $user['id'], $note);
        }

        Router::redirect('/clientes/' . $id);
    }
}
