<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../core/Router.php';
require_once '../../resources/v2/UserResource.php';
require_once '../../resources/v2/ProductoResource.php';
require_once '../../resources/v2/AuthResource.php';

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName;

$router = new Router('v2', $basePath);

$userResource = new UserResource();
$productoResource = new ProductoResource();
$authResource = new AuthResource();

// --- Rutas de autenticación (públicas, sin token) ---
$router->addRoute('POST', '/login', [$authResource, 'login']);
$router->addRoute('POST', '/logout', [$authResource, 'logout']);
$router->addRoute('GET', '/me', [$authResource, 'me']);

// --- Rutas de usuarios (protegidas) ---
$router->addRoute('GET', '/users', [$userResource, 'index'], true);
$router->addRoute('GET', '/users/{id}', [$userResource, 'show'], true);
$router->addRoute('POST', '/users', [$userResource, 'store'], true);
$router->addRoute('PUT', '/users/{id}', [$userResource, 'update'], true);
$router->addRoute('DELETE', '/users/{id}', [$userResource, 'destroy'], true);

// --- Rutas de productos (protegidas) ---
$router->addRoute('GET', '/productos', [$productoResource, 'index'], true);
$router->addRoute('GET', '/productos/{id}', [$productoResource, 'show'], true);
$router->addRoute('POST', '/productos', [$productoResource, 'store'], true);
$router->addRoute('PUT', '/productos/{id}', [$productoResource, 'update'], true);
$router->addRoute('DELETE', '/productos/{id}', [$productoResource, 'destroy'], true);

$router->dispatch();
?>