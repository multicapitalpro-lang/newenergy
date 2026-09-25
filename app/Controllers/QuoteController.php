<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\DiscountLimits;
use App\Core\Roles;
use App\Core\Router;
use App\Core\View;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;

class QuoteController
{
    public function index(): void
    {
        $user = Auth::requireRole(Roles::ALL);

        View::render('quotes/index', [
            'user' => $user,
            'quotes' => Quote::forSellerIds(User::scopedIds($user)),
        ], 'loja');
    }

    public function show(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);
        $quote = Quote::find((int) $id);

        if (!$quote) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/errors/404.php';
            return;
        }

        $scoped = User::scopedIds($user);
        if ($scoped !== null && !in_array((int) $quote['seller_id'], $scoped, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            return;
        }

        View::render('quotes/show', [
            'user' => $user,
            'quote' => $quote,
            'items' => Quote::items((int) $id),
        ], 'loja');
    }

    /** Formulário: escolhe cliente (ou cria um novo) + produtos. */
    public function create(): void
    {
        $user = Auth::requireRole(Roles::SELLER_ROLES);

        View::render('quotes/create', [
            'user' => $user,
            'clients' => Client::forSellerIds(User::scopedIds($user)),
            'products' => Product::all(onlyActive: true),
            'discountLimit' => DiscountLimits::forRole($user['role']),
            'preselectedClientId' => $_GET['cliente_id'] ?? null,
        ], 'loja');
    }

    public function store(): void
    {
        $user = Auth::requireRole(Roles::SELLER_ROLES);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/orcamentos/novo');
        }

        $clientId = (int) ($_POST['client_id'] ?? 0);
        $discount = max(0.0, (float) str_replace(',', '.', $_POST['discount_percent'] ?? '0'));
        $productIds = $_POST['product_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];

        $items = [];
        foreach ($productIds as $i => $productId) {
            $qty = (int) ($quantities[$i] ?? 0);
            if ($qty < 1 || empty($productId)) {
                continue;
            }
            $product = Product::find((int) $productId);
            if (!$product) {
                continue;
            }
            $items[] = [
                'product_id' => $product['id'],
                'quantity' => $qty,
                'unit_price_cents' => $product['sell_price_cents'],
            ];
        }

        if ($clientId < 1 || empty($items)) {
            Router::redirect('/orcamentos/novo');
        }

        $needsApproval = DiscountLimits::exceeds($user['role'], $discount);

        $quoteId = Quote::create($clientId, (int) $user['id'], $items, $discount, $needsApproval);

        Router::redirect('/orcamentos/' . $quoteId . '?criado=1');
    }

    /** Proposta Comercial: versão formatada/imprimível do orçamento pro cliente. */
    public function proposal(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);
        $quote = Quote::find((int) $id);

        if (!$quote) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/errors/404.php';
            return;
        }

        $scoped = User::scopedIds($user);
        if ($scoped !== null && !in_array((int) $quote['seller_id'], $scoped, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            return;
        }

        View::render('quotes/proposal', [
            'user' => $user,
            'quote' => $quote,
            'items' => Quote::items((int) $id),
        ], null);
    }

    public function updateStatus(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/orcamentos/' . $id);
        }

        $status = $_POST['status'] ?? '';
        if (in_array($status, ['aprovado', 'recusado'], true)) {
            Quote::updateStatus((int) $id, $status);
        }

        Router::redirect('/orcamentos/' . $id);
    }

    /** Orçamento aprovado -> vira Pedido de verdade. */
    public function convert(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/orcamentos/' . $id);
        }

        $quote = Quote::find((int) $id);
        if (!$quote || $quote['status'] !== 'aprovado') {
            Router::redirect('/orcamentos/' . $id);
        }

        $items = array_map(fn ($item) => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price_cents' => $item['unit_price_cents'],
        ], Quote::items((int) $id));

        $orderId = Order::create((int) $quote['client_id'], (int) $quote['seller_id'], $items);
        Quote::markConverted((int) $id, $orderId);

        Router::redirect('/pedidos/' . $orderId . '?criado=1');
    }
}
