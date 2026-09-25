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
        $user = Auth::requireRole(Roles::ALL);

        View::render('orders/index', [
            'user' => $user,
            'orders' => Order::forSellerIds(User::scopedIds($user)),
        ], 'loja');
    }

    public function show(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);
        $order = Order::find((int) $id);

        if (!$order) {
            http_response_code(404);
            require BASE_PATH . '/app/Views/errors/404.php';
            return;
        }

        $scoped = User::scopedIds($user);
        if ($scoped !== null && !in_array((int) $order['seller_id'], $scoped, true)) {
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            return;
        }

        View::render('orders/show', [
            'user' => $user,
            'order' => $order,
            'items' => Order::items((int) $id),
            'documents' => Order::documents((int) $id),
        ], 'loja');
    }

    /** "Acompanhar a entrega" */
    public function updateDelivery(string $id): void
    {
        Auth::requireRole(Roles::MANAGEMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/pedidos/' . $id);
        }

        $status = $_POST['delivery_status'] ?? 'aguardando';
        if (in_array($status, ['aguardando', 'em_transito', 'entregue'], true)) {
            Order::updateDelivery((int) $id, trim($_POST['tracking_code'] ?? ''), $status);
        }

        Router::redirect('/pedidos/' . $id);
    }

    /** "Pós-venda de instalação" */
    public function updateInstallation(string $id): void
    {
        Auth::requireRole(Roles::MANAGEMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/pedidos/' . $id);
        }

        $status = $_POST['installation_status'] ?? 'nao_iniciada';
        if (in_array($status, ['nao_iniciada', 'agendada', 'concluida'], true)) {
            Order::updateInstallation((int) $id, $status, trim($_POST['installation_date'] ?? ''), trim($_POST['installation_notes'] ?? ''));
        }

        Router::redirect('/pedidos/' . $id);
    }

    /** "Aprovar documentos" */
    public function uploadDocument(string $id): void
    {
        $user = Auth::requireRole(Roles::ALL);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/pedidos/' . $id);
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Router::redirect('/pedidos/' . $id);
        }

        $filePath = null;
        if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $dir = BASE_PATH . '/public_html/uploads/documents';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $filename = uniqid('doc_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['file']['name']);
            move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . $filename);
            $filePath = '/uploads/documents/' . $filename;
        }

        Order::addDocument((int) $id, (int) $user['id'], $name, $filePath);

        Router::redirect('/pedidos/' . $id);
    }

    public function decideDocument(string $orderId, string $docId): void
    {
        $user = Auth::requireRole(Roles::MANAGEMENT);

        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            Router::redirect('/pedidos/' . $orderId);
        }

        $decision = $_POST['decision'] ?? '';
        if (in_array($decision, ['aprovado', 'recusado'], true)) {
            Order::decideDocument((int) $docId, $decision, (int) $user['id']);
        }

        Router::redirect('/pedidos/' . $orderId);
    }

    public function store(): void
    {
        $user = Auth::requireRole(Roles::SELLER_ROLES);

        // Gestor/Vendedor só fecha pedido depois que o contrato assinado dele
        // for aprovado -- ver ContractController / "Aprovar vendedores".
        if (in_array($user['role'], Roles::STAFF, true) && $user['contract_status'] !== 'aprovado') {
            Router::redirect('/meu-contrato?bloqueado=1');
        }

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
