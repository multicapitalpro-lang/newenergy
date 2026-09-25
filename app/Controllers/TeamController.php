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
    /**
     * Uma única tela que muda de conteúdo conforme o papel de quem está
     * logado -- mesma ideia do painel "Usuários" do EcoDiffusore, que
     * também adapta o que mostra/permite por role em vez de ter uma tela
     * por papel.
     */
    public function index(): void
    {
        $user = Auth::requireRole([Roles::ADMIN, Roles::GERENTE, Roles::SUPERVISOR, Roles::LICENCIADO, Roles::GESTOR]);

        $data = ['user' => $user];

        if (in_array($user['role'], [Roles::ADMIN, Roles::GERENTE], true)) {
            // Gerente/admin: cadastra Supervisores e atribui Licenciados a eles.
            $data['view'] = 'gerente';
            $data['supervisores'] = User::allByRole(Roles::SUPERVISOR);
            $data['licenciados'] = User::allByRole(Roles::LICENCIADO);
        } elseif ($user['role'] === Roles::SUPERVISOR) {
            // Supervisor: só visualiza os licenciados atribuídos a ele (e a equipe deles).
            $data['view'] = 'supervisor';
            $data['licenciados'] = User::licenciadosOf((int) $user['id']);
            $data['teams'] = [];
            foreach ($data['licenciados'] as $licenciado) {
                $data['teams'][$licenciado['id']] = User::teamOf((int) $licenciado['id']);
            }
        } else {
            // Licenciado: cadastra Gestor/Vendedor sob ele. Gestor: só visualiza a mesma equipe.
            $data['view'] = 'licenciado';
            $managerId = $user['role'] === Roles::GESTOR ? (int) $user['manager_id'] : (int) $user['id'];
            $data['team'] = User::teamOf($managerId);
            $data['canManage'] = $user['role'] === Roles::LICENCIADO || $user['role'] === Roles::ADMIN;
        }

        View::render('team/index', $data, 'loja');
    }

    /** Licenciado cadastra Gestor ou Vendedor sob ele. */
    public function store(): void
    {
        $user = Auth::requireRole(Roles::USER_MANAGEMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/minha-equipe');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = in_array($_POST['role'] ?? '', [Roles::GESTOR, Roles::VENDEDOR], true) ? $_POST['role'] : Roles::VENDEDOR;

        if ($name !== '' && $email !== '' && strlen($password) >= 6 && !User::findByEmail($email)) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'manager_id' => $user['id'],
                'phone' => trim($_POST['phone'] ?? '') ?: null,
            ]);
        }

        Router::redirect('/minha-equipe');
    }

    /** Gerente/admin cadastra um Supervisor (suporte nacional). */
    public function storeSupervisor(): void
    {
        $user = Auth::requireRole(Roles::SUPERVISOR_ASSIGNMENT);

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
                'role' => Roles::SUPERVISOR,
                'manager_id' => $user['id'],
                'phone' => trim($_POST['phone'] ?? '') ?: null,
            ]);
        }

        Router::redirect('/minha-equipe');
    }

    /** Gerente/admin atribui (ou remove) um Supervisor responsável por um Licenciado. */
    public function assignSupervisor(): void
    {
        Auth::requireRole(Roles::SUPERVISOR_ASSIGNMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/minha-equipe');
        }

        $licenciadoId = (int) ($_POST['licenciado_id'] ?? 0);
        $supervisorId = !empty($_POST['supervisor_id']) ? (int) $_POST['supervisor_id'] : null;

        if ($licenciadoId > 0) {
            User::assignSupervisor($licenciadoId, $supervisorId);
        }

        Router::redirect('/minha-equipe');
    }
}
