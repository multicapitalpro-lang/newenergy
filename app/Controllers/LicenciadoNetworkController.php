<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Roles;
use App\Core\View;
use App\Models\Order;
use App\Models\User;

/** "Expansão do Licenciado": roster completo da rede, com estatísticas e atribuição de supervisor. */
class LicenciadoNetworkController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::SUPERVISOR_ASSIGNMENT);

        $licenciados = User::allByRole(Roles::LICENCIADO);
        $supervisores = User::allByRole(Roles::SUPERVISOR);

        $rows = [];
        foreach ($licenciados as $l) {
            $downline = User::downlineIds((int) $l['id']);
            $orders = Order::forSellerIds($downline);

            $supervisorName = null;
            foreach ($supervisores as $s) {
                if ((int) $s['id'] === (int) $l['supervisor_id']) {
                    $supervisorName = $s['name'];
                    break;
                }
            }

            $rows[] = $l + [
                'team_size' => count($downline) - 1, // exclui ele mesmo
                'orders_count' => count($orders),
                'revenue_cents' => array_sum(array_column($orders, 'total_cents')),
                'supervisor_name' => $supervisorName,
            ];
        }

        $stats = [
            'total' => count($licenciados),
            'ativos' => count(array_filter($licenciados, fn ($l) => $l['onboarding_status'] === 'ativo')),
            'pendentes' => count(array_filter($licenciados, fn ($l) => $l['onboarding_status'] === 'aguardando_aprovacao')),
            'sem_supervisor' => count(array_filter($licenciados, fn ($l) => empty($l['supervisor_id']))),
        ];

        View::render('network/licenciados', [
            'user' => $user,
            'rows' => $rows,
            'supervisores' => $supervisores,
            'stats' => $stats,
        ], 'loja');
    }
}
