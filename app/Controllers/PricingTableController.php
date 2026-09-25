<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Product;

class PricingTableController
{
    public function index(): void
    {
        Auth::requireRole([Roles::ADMIN, Roles::GERENTE]);

        View::render('pricing/index', [
            'user' => Auth::user(),
            'products' => Product::all(),
        ], 'loja');
    }

    public function update(string $id): void
    {
        Auth::requireRole([Roles::ADMIN, Roles::GERENTE]);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/tabela-precos');
        }

        $costCents = (int) round((float) str_replace(',', '.', $_POST['cost_price'] ?? '0') * 100);
        $markup = $_POST['markup_percent'] !== '' ? (float) str_replace(',', '.', $_POST['markup_percent']) : null;
        $capacity = $_POST['capacity_kwh'] !== '' ? (float) str_replace(',', '.', $_POST['capacity_kwh']) : null;

        Product::updatePricing((int) $id, $costCents, $markup, $capacity);

        Router::redirect('/tabela-precos?salvo=1');
    }
}
