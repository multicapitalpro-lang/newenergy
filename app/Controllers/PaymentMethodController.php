<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\PaymentMethod;

class PaymentMethodController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('payment_methods/index', [
            'user' => $user,
            'methods' => PaymentMethod::all(),
        ], 'loja');
    }

    public function store(): void
    {
        Auth::requireRole([Roles::ADMIN, Roles::GERENTE]);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/config-pagamentos');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            PaymentMethod::create([
                'name' => $name,
                'description' => trim($_POST['description'] ?? '') ?: null,
                'max_installments' => (int) ($_POST['max_installments'] ?? 1),
                'interest_rate_percent' => (float) str_replace(',', '.', $_POST['interest_rate_percent'] ?? '0'),
            ]);
        }

        Router::redirect('/config-pagamentos');
    }

    public function destroy(string $id): void
    {
        Auth::requireRole([Roles::ADMIN, Roles::GERENTE]);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/config-pagamentos');
        }

        PaymentMethod::delete((int) $id);
        Router::redirect('/config-pagamentos');
    }
}
