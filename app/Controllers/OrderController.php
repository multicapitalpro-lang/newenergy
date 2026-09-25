<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class OrderController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::SELLER_ROLES);
        $sellerIds = $user['role'] === 'vendedor' ? [$user['id']] : User::downlineIds((int) $user['id']);

        View::render('orders/index', [
            'user' => $user,
            'orders' => Order::forSellerIds($sellerIds),
        ], 'loja');
    }

    public function show(string $id): void
    {
        $user = Auth::requireRole(Roles::SELLER_ROLES);
        $order = Order::find((int) $id);

        if (!$order) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/errors/404.php';
            return;
        }

        $sellerIds = $user['role'] === 'vendedor' ? [$user['id']] : User::downlineIds((int) $user['id']);
        if (!in_array((int) $order['seller_id'], $sellerIds, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            return;
        }

        View::render('orders/show', [
            'user' => $user,
            'order' => $order,
            'items' => Order::items((int) $id),
        ], 'loja');
    }

    public function store(): void
    {
        $user = Auth::requireRole(Roles::SELLER_ROLES);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/produtos/' . ($_POST['product_id'] ?? ''));
        }

        $product = Product::find((int) ($_POST['product_id'] ?? 0));
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $clientName = trim($_POST['client_name'] ?? '');

        if (!$product || $clientName === '') {
            Router::redirect('/produtos/' . ($_POST['product_id'] ?? ''));
        }

        $clientId = Client::create((int) $user['id'], [
            'name' => $clientName,
            'document' => trim($_POST['client_document'] ?? ''),
            'email' => trim($_POST['client_email'] ?? ''),
            'phone' => trim($_POST['client_phone'] ?? ''),
        ]);

        $orderId = Order::create($clientId, (int) $user['id'], [
            [
                'product_id' => $product['id'],
                'quantity' => $quantity,
                'unit_price_cents' => $product['sell_price_cents'],
            ],
        ]);

        Router::redirect('/pedidos/' . $orderId . '?criado=1');
    }
}
