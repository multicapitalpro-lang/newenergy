<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;

class LeadController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('leads/index', [
            'user' => $user,
            'leads' => Lead::forUserIds(User::scopedIds($user)),
        ], 'loja');
    }

    public function show(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);
        $lead = Lead::find((int) $id);

        if (!$lead) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/errors/404.php';
            return;
        }

        $scoped = User::scopedIds($user);
        if ($scoped !== null && $lead['assigned_to_user_id'] !== null && !in_array((int) $lead['assigned_to_user_id'], $scoped, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            return;
        }

        View::render('leads/show', [
            'user' => $user,
            'lead' => $lead,
            'notes' => Lead::notes((int) $id),
        ], 'loja');
    }

    /** Cadastro manual de lead (ex: vendedor recebeu contato por telefone). */
    public function store(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/leads');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $id = Lead::create([
                'name' => $name,
                'phone' => trim($_POST['phone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'message' => trim($_POST['message'] ?? ''),
                'source' => 'manual',
            ]);
            Router::redirect('/leads/' . $id);
        }

        Router::redirect('/leads');
    }

    public function addNote(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/leads/' . $id);
        }

        $note = trim($_POST['note'] ?? '');
        if ($note !== '') {
            Lead::addNote((int) $id, (int) $user['id'], $note);
        }

        Router::redirect('/leads/' . $id);
    }

    public function updateStatus(string $id): void
    {
        Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/leads/' . $id);
        }

        $status = $_POST['status'] ?? '';
        if (in_array($status, ['novo', 'contatado', 'convertido', 'descartado'], true)) {
            Lead::updateStatus((int) $id, $status);
        }

        Router::redirect('/leads/' . $id);
    }

    /** Converte o lead num Client, pra depois virar orçamento/pedido. */
    public function convert(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/leads/' . $id);
        }

        $lead = Lead::find((int) $id);
        if (!$lead) {
            Router::redirect('/leads');
        }

        $clientId = Client::create((int) ($lead['assigned_to_user_id'] ?? $user['id']), [
            'name' => $lead['name'],
            'email' => $lead['email'],
            'phone' => $lead['phone'],
            'city' => $lead['city'],
            'state' => $lead['state'],
            'converted_from_lead_id' => $lead['id'],
        ]);

        Lead::updateStatus((int) $id, 'convertido');

        Router::redirect('/clientes/' . $clientId . '?convertido=1');
    }
}
