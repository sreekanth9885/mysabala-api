<?php

require_once __DIR__ . '/config/cors.php';

// Config & DB
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

// Core
require_once __DIR__ . '/app/Core/Response.php';
require_once __DIR__ . '/app/Core/Router.php';

require_once __DIR__ . '/app/controllers/AuthController.php';

$router = new Router();

$router->post('/login', [AuthController::class, 'login']);
$router->get('/me', [AuthController::class, 'me']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->dispatch();