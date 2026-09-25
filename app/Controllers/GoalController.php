<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Goal;
use App\Models\User;

class GoalController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);
        $period = $_GET['periodo'] ?? date('Y-m');

        $goals = Goal::forUserIds(User::scopedIds($user), $period);
        foreach ($goals as &$goal) {
            $goal['actual'] = $goal['target_type'] === 'revenue'
                ? Goal::actualRevenue((int) $goal['user_id'], $period)
                : Goal::actualOrdersCount((int) $goal['user_id'], $period);
        }
        unset($goal);

        // Quem gerencia gente pode atribuir meta pra qualquer um do seu escopo;
        // quem não gerencia (gestor/vendedor) só pode definir a própria meta.
        $team = [];
        if (in_array($user['role'], Roles::USER_MANAGEMENT, true) || in_array($user['role'], [Roles::GERENTE, Roles::SUPERVISOR], true)) {
            $ids = User::scopedIds($user) ?? [];
            foreach ($ids as $id) {
                $candidate = User::find($id);
                if ($candidate && in_array($candidate['role'], Roles::SELLER_ROLES, true)) {
                    $team[] = $candidate;
                }
            }
        } elseif (in_array($user['role'], Roles::SELLER_ROLES, true)) {
            $team = [$user];
        }

        View::render('performance/goals', [
            'user' => $user,
            'goals' => $goals,
            'period' => $period,
            'team' => $team,
        ], 'loja');
    }

    public function store(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/metas');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $period = trim($_POST['period'] ?? date('Y-m'));
        $targetType = in_array($_POST['target_type'] ?? '', ['revenue', 'orders_count'], true) ? $_POST['target_type'] : 'revenue';
        $targetValue = (float) str_replace(',', '.', $_POST['target_value'] ?? '0');

        // target_value é comparado com Goal::actualRevenue()/actualOrdersCount(): a de
        // faturamento vem de orders.total_cents (centavos), então a meta de faturamento
        // também precisa ser guardada em centavos pra progresso/exibição baterem.
        if ($targetType === 'revenue') {
            $targetValue = round($targetValue * 100);
        }

        if ($userId > 0 && $targetValue > 0) {
            Goal::create($userId, $period, $targetType, $targetValue, (int) $user['id']);
        }

        Router::redirect('/metas?periodo=' . urlencode($period));
    }

    public function destroy(string $id): void
    {
        Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/metas');
        }

        Goal::delete((int) $id);
        Router::redirect('/metas');
    }
}
