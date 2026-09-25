<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Roles;
use App\Core\View;
use App\Models\Performance;
use App\Models\User;

class PerformanceController
{
    public function ranking(): void
    {
        $user = Auth::requireRole(Roles::ALL);
        $period = $_GET['periodo'] ?? null;

        View::render('performance/ranking', [
            'user' => $user,
            'ranking' => Performance::ranking(User::scopedIds($user), $period),
            'period' => $period,
        ], 'loja');
    }

    public function funnel(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('performance/funnel', [
            'user' => $user,
            'funnel' => Performance::funnel(User::scopedIds($user)),
        ], 'loja');
    }
}
