<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\LeadExtensionRequest;
use App\Models\User;

class LeadExtensionController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('leads/extensions', [
            'user' => $user,
            'requests' => LeadExtensionRequest::forUserIds(User::scopedIds($user)),
        ], 'loja');
    }

    /** Vendedor/gestor pede mais prazo pra trabalhar um lead. */
    public function store(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/leads/' . ($_POST['lead_id'] ?? ''));
        }

        $leadId = (int) ($_POST['lead_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($leadId > 0 && $reason !== '') {
            LeadExtensionRequest::create($leadId, (int) $user['id'], $reason);
        }

        Router::redirect('/leads/' . $leadId . '?extensao_solicitada=1');
    }

    /** Licenciado/admin aprova ou recusa. */
    public function decide(string $id): void
    {
        $user = Auth::requireRole(Roles::MANAGEMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/leads/extensoes');
        }

        $status = $_POST['decision'] ?? '';
        if (in_array($status, ['aprovado', 'recusado'], true)) {
            LeadExtensionRequest::decide((int) $id, $status, (int) $user['id']);
        }

        Router::redirect('/leads/extensoes');
    }
}
