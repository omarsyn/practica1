<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../core/Router.php';

// Cargar Recursos V1
require_once __DIR__ . '/../resources/v1/UserResource.php';
require_once __DIR__ . '/../resources/v1/ProductResource.php';

// Cargar Recursos V2, Modelos y Middleware
require_once __DIR__ . '/../resources/v2/AuthResource.php';
require_once __DIR__ . '/../resources/v2/ProductResource.php';

$basePath = '/22031401/public/api';
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

if (strpos($requestUri, '/v2') !== false) {
    // --- RUTAS VERSIÓN 2 (Protegidas) ---
    $routerV2 = new Router('v2', $basePath);
    $authResource = new AuthResource();
    $productV2Resource = new ProductResourceV2();

    // Endpoints de Autenticación
    $routerV2->addRoute('POST', '/login', [$authResource, 'login']);
    $routerV2->addRoute('POST', '/logout', [$authResource, 'logout']);
    $routerV2->addRoute('GET', '/me', [$authResource, 'me']);

    // Endpoints de Productos
    $routerV2->addRoute('GET', '/productos', [$productV2Resource, 'index']);
    $routerV2->addRoute('GET', '/productos/{id}', [$productV2Resource, 'show']);
    $routerV2->addRoute('POST', '/productos', [$productV2Resource, 'store']);
    $routerV2->addRoute('PUT', '/productos/{id}', [$productV2Resource, 'update']);
    $routerV2->addRoute('DELETE', '/productos/{id}', [$productV2Resource, 'destroy']);

    $routerV2->dispatch();
} else {
    // --- RUTAS VERSIÓN 1 (Públicas) ---
    $routerV1 = new Router('v1', $basePath);
    $userResource = new UserResource();
    $productResource = new ProductResource();

    $routerV1->addRoute('GET', '/users', [$userResource, 'index']);
    $routerV1->addRoute('GET', '/productos', [$productResource, 'index']);
    $routerV1->addRoute('GET', '/productos/{id}', [$productResource, 'show']);
    $routerV1->addRoute('POST', '/productos', [$productResource, 'store']);
    $routerV1->addRoute('PUT', '/productos/{id}', [$productResource, 'update']);
    $routerV1->addRoute('DELETE', '/productos/{id}', [$productResource, 'destroy']);

    $routerV1->dispatch();
}
?>
