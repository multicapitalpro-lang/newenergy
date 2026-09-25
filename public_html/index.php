<?php

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Controllers\ApprovalController;
use App\Controllers\AuthController;
use App\Controllers\CalculatorController;
use App\Controllers\ClientController;
use App\Controllers\ContractController;
use App\Controllers\HomeController;
use App\Controllers\LeadController;
use App\Controllers\LeadExtensionController;
use App\Controllers\LeadRoutingController;
use App\Controllers\LicenciadoApprovalController;
use App\Controllers\LicenciadoNetworkController;
use App\Controllers\GoalController;
use App\Controllers\OrderController;
use App\Controllers\PaymentMethodController;
use App\Controllers\PerformanceController;
use App\Controllers\PricingTableController;
use App\Controllers\ProductController;
use App\Controllers\PublicLeadController;
use App\Controllers\QuoteController;
use App\Controllers\SalesMaterialController;
use App\Controllers\TeamController;
use App\Core\Config;
use App\Core\Router;

session_name(Config::get('session_name', 'newenergy_session'));
session_start();

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/catalogo', [HomeController::class, 'catalog']);
$router->get('/produtos/{id}', [ProductController::class, 'show']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/cadastro', [AuthController::class, 'showRegister']);
$router->post('/cadastro', [AuthController::class, 'register']);
$router->get('/cadastro/pendente', [AuthController::class, 'pending'], auth: true);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/minha-equipe', [TeamController::class, 'index'], auth: true);
$router->post('/minha-equipe', [TeamController::class, 'store'], auth: true);
$router->post('/minha-equipe/supervisores', [TeamController::class, 'storeSupervisor'], auth: true);
$router->post('/minha-equipe/atribuir-supervisor', [TeamController::class, 'assignSupervisor'], auth: true);

$router->get('/pedidos', [OrderController::class, 'index'], auth: true);
$router->post('/pedidos', [OrderController::class, 'store'], auth: true);
$router->post('/pedidos/{id}/entrega', [OrderController::class, 'updateDelivery'], auth: true);
$router->post('/pedidos/{id}/instalacao', [OrderController::class, 'updateInstallation'], auth: true);
$router->post('/pedidos/{id}/documentos', [OrderController::class, 'uploadDocument'], auth: true);
$router->post('/pedidos/{orderId}/documentos/{docId}/decidir', [OrderController::class, 'decideDocument'], auth: true);
$router->get('/pedidos/{id}', [OrderController::class, 'show'], auth: true);

$router->get('/contato', [PublicLeadController::class, 'show']);
$router->post('/contato', [PublicLeadController::class, 'store']);

$router->get('/leads', [LeadController::class, 'index'], auth: true);
$router->get('/leads/extensoes', [LeadExtensionController::class, 'index'], auth: true);
$router->post('/leads/extensoes', [LeadExtensionController::class, 'store'], auth: true);
$router->post('/leads/extensoes/{id}/decidir', [LeadExtensionController::class, 'decide'], auth: true);
$router->get('/leads/roteamento', [LeadRoutingController::class, 'edit'], auth: true);
$router->post('/leads/roteamento', [LeadRoutingController::class, 'update'], auth: true);
$router->get('/leads/{id}', [LeadController::class, 'show'], auth: true);
$router->post('/leads', [LeadController::class, 'store'], auth: true);
$router->post('/leads/{id}/nota', [LeadController::class, 'addNote'], auth: true);
$router->post('/leads/{id}/status', [LeadController::class, 'updateStatus'], auth: true);
$router->post('/leads/{id}/converter', [LeadController::class, 'convert'], auth: true);

$router->get('/clientes', [ClientController::class, 'index'], auth: true);
$router->get('/clientes/{id}', [ClientController::class, 'show'], auth: true);
$router->post('/clientes/{id}/nota', [ClientController::class, 'addNote'], auth: true);

$router->get('/calculadora', [CalculatorController::class, 'show']);
$router->post('/calculadora', [CalculatorController::class, 'calculate']);

$router->get('/orcamentos', [QuoteController::class, 'index'], auth: true);
$router->get('/orcamentos/novo', [QuoteController::class, 'create'], auth: true);
$router->post('/orcamentos', [QuoteController::class, 'store'], auth: true);
$router->get('/orcamentos/{id}/proposta', [QuoteController::class, 'proposal'], auth: true);
$router->post('/orcamentos/{id}/status', [QuoteController::class, 'updateStatus'], auth: true);
$router->post('/orcamentos/{id}/converter', [QuoteController::class, 'convert'], auth: true);
$router->get('/orcamentos/{id}', [QuoteController::class, 'show'], auth: true);

$router->get('/aprovacoes', [ApprovalController::class, 'index'], auth: true);
$router->post('/aprovacoes/{id}/decidir', [ApprovalController::class, 'decide'], auth: true);

$router->get('/tabela-precos', [PricingTableController::class, 'index'], auth: true);
$router->post('/tabela-precos/{id}', [PricingTableController::class, 'update'], auth: true);

$router->get('/material-de-venda', [SalesMaterialController::class, 'index'], auth: true);
$router->post('/material-de-venda', [SalesMaterialController::class, 'store'], auth: true);
$router->post('/material-de-venda/{id}/excluir', [SalesMaterialController::class, 'destroy'], auth: true);

$router->get('/config-pagamentos', [PaymentMethodController::class, 'index'], auth: true);
$router->post('/config-pagamentos', [PaymentMethodController::class, 'store'], auth: true);
$router->post('/config-pagamentos/{id}/excluir', [PaymentMethodController::class, 'destroy'], auth: true);

$router->get('/vendedores', [PerformanceController::class, 'ranking'], auth: true);
$router->get('/funil', [PerformanceController::class, 'funnel'], auth: true);

$router->get('/metas', [GoalController::class, 'index'], auth: true);
$router->post('/metas', [GoalController::class, 'store'], auth: true);
$router->post('/metas/{id}/excluir', [GoalController::class, 'destroy'], auth: true);

$router->get('/meu-contrato', [ContractController::class, 'show'], auth: true);
$router->post('/meu-contrato', [ContractController::class, 'upload'], auth: true);
$router->get('/vendedores/aprovar', [ContractController::class, 'pendingApprovals'], auth: true);
$router->post('/vendedores/{id}/aprovar', [ContractController::class, 'approve'], auth: true);
$router->post('/vendedores/{id}/reprovar', [ContractController::class, 'reject'], auth: true);

$router->get('/licenciados', [LicenciadoNetworkController::class, 'index'], auth: true);
$router->get('/licenciados/aprovacoes', [LicenciadoApprovalController::class, 'index'], auth: true);
$router->post('/licenciados/{id}/aprovar', [LicenciadoApprovalController::class, 'approve'], auth: true);
$router->post('/licenciados/{id}/reprovar', [LicenciadoApprovalController::class, 'reject'], auth: true);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
