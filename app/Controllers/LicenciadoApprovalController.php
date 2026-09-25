<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\User;

/** "Aprovação de cadastros": libera (ou recusa) licenciados que se auto-cadastraram. */
class LicenciadoApprovalController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::SUPERVISOR_ASSIGNMENT);

        View::render('network/aprovacoes', [
            'user' => $user,
            'pending' => User::pendingOnboarding(),
        ], 'loja');
    }

    public function approve(string $id): void
    {
        $this->decide($id, 'aprovado');
    }

    public function reject(string $id): void
    {
        $this->decide($id, 'reprovado', trim($_POST['reason'] ?? '') ?: 'Não especificado');
    }

    private function decide(string $id, string $decision, ?string $reason = null): void
    {
        Auth::requireRole(Roles::SUPERVISOR_ASSIGNMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/licenciados/aprovacoes');
        }

        $target = User::find((int) $id);
        if ($target && $target['role'] === Roles::LICENCIADO) {
            User::decideOnboarding((int) $id, $decision, $reason);
        }

        Router::redirect('/licenciados/aprovacoes');
    }
}
