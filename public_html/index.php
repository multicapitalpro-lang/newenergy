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

use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\HomeController;
use App\Controllers\LeadController;
use App\Controllers\LeadExtensionController;
use App\Controllers\LeadRoutingController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Controllers\PublicLeadController;
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
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/minha-equipe', [TeamController::class, 'index'], auth: true);
$router->post('/minha-equipe', [TeamController::class, 'store'], auth: true);
$router->post('/minha-equipe/supervisores', [TeamController::class, 'storeSupervisor'], auth: true);
$router->post('/minha-equipe/atribuir-supervisor', [TeamController::class, 'assignSupervisor'], auth: true);

$router->get('/pedidos', [OrderController::class, 'index'], auth: true);
$router->get('/pedidos/{id}', [OrderController::class, 'show'], auth: true);
$router->post('/pedidos', [OrderController::class, 'store'], auth: true);

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

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
