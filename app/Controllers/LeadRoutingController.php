<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Lead;
use App\Models\User;

class LeadRoutingController
{
    public function edit(): void
    {
        Auth::requireRole(Roles::SUPERVISOR_ASSIGNMENT);

        View::render('leads/routing', [
            'user' => Auth::user(),
            'licenciados' => User::allByRole(Roles::LICENCIADO),
            'fallbackUserId' => Lead::getFallbackUserId(),
        ], 'loja');
    }

    public function update(): void
    {
        Auth::requireRole(Roles::SUPERVISOR_ASSIGNMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/leads/roteamento');
        }

        $fallback = !empty($_POST['fallback_user_id']) ? (int) $_POST['fallback_user_id'] : null;
        Lead::setFallbackUserId($fallback);

        Router::redirect('/leads/roteamento?salvo=1');
    }
}
