<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\User;

class ContractController
{
    /** "Meu Contrato": auto-atendimento do Gestor/Vendedor pra subir o contrato assinado. */
    public function show(): void
    {
        $user = Auth::requireRole(Roles::STAFF);

        View::render('contract/show', [
            'user' => $user,
            'blocked' => isset($_GET['bloqueado']),
        ], 'loja');
    }

    public function upload(): void
    {
        $user = Auth::requireRole(Roles::STAFF);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/meu-contrato');
        }

        if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $dir = BASE_PATH . '/public_html/uploads/contracts';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = uniqid('contrato_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . $filename);
            User::updateContract((int) $user['id'], '/uploads/contracts/' . $filename);
        }

        Router::redirect('/meu-contrato');
    }

    /** "Aprovar vendedores": fila de contratos aguardando aprovação. */
    public function pendingApprovals(): void
    {
        $user = Auth::requireRole([Roles::ADMIN, Roles::LICENCIADO]);
        $licenciadoId = $user['role'] === Roles::LICENCIADO ? (int) $user['id'] : null;

        View::render('contract/pending', [
            'user' => $user,
            'pending' => User::pendingContracts($licenciadoId),
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
        $user = Auth::requireRole([Roles::ADMIN, Roles::LICENCIADO]);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/vendedores/aprovar');
        }

        $target = User::find((int) $id);
        if ($target && in_array($target['role'], [Roles::GESTOR, Roles::VENDEDOR], true)) {
            // Licenciado só decide sobre a própria equipe -- admin decide sobre qualquer um.
            if ($user['role'] !== Roles::LICENCIADO || (int) $target['manager_id'] === (int) $user['id']) {
                User::decideContract((int) $id, $decision, $reason);
            }
        }

        Router::redirect('/vendedores/aprovar');
    }
}
