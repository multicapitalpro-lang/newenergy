<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Approval;
use App\Models\Quote;
use App\Models\User;

class ApprovalController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('approvals/index', [
            'user' => $user,
            'approvals' => Approval::forUserIds(User::scopedIds($user)),
        ], 'loja');
    }

    public function decide(string $id): void
    {
        $user = Auth::requireRole(Roles::MANAGEMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/aprovacoes');
        }

        $approval = Approval::find((int) $id);
        if (!$approval) {
            Router::redirect('/aprovacoes');
        }

        $decision = $_POST['decision'] ?? '';
        if (in_array($decision, ['aprovado', 'recusado'], true)) {
            Approval::decide((int) $id, $decision, (int) $user['id']);

            if ($approval['approvable_type'] === 'quote') {
                Quote::updateStatus((int) $approval['approvable_id'], $decision === 'aprovado' ? 'aprovado' : 'recusado');
            }
        }

        Router::redirect('/aprovacoes');
    }
}
