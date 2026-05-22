<?php

require_once __DIR__ . '/config/cors.php';

// Config & DB
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';

// Core
require_once __DIR__ . '/app/Core/Response.php';
require_once __DIR__ . '/app/Core/Router.php';

require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/CategoryController.php';
require_once __DIR__ . '/app/controllers/SubCategoryController.php';

// Instantiate controllers with database
$authController = new AuthController($pdo);
$categoryController = new CategoryController($pdo);
$subCategoryController = new SubCategoryController($pdo);

$router = new Router();

$router->post('/login', [$authController, 'login']);
$router->get('/me', [$authController, 'me']);
$router->post('/logout', [$authController, 'logout']);

$router->post('/categories', [$categoryController, 'create']);
$router->get('/categories', [$categoryController, 'index']);
$router->get('/categories/{id}', [$categoryController, 'show']);
$router->put('/categories/{id}', [$categoryController, 'update']);
$router->delete('/categories/{id}', [$categoryController, 'delete']);

// GET
$router->get('/sub-categories', [$subCategoryController, 'index']);
$router->get('/sub-categories/{id}', [$subCategoryController, 'show']);
$router->get('/categories/{id}/sub-categories', [$subCategoryController, 'byCategory']);

// POST
$router->post('/sub-categories', [$subCategoryController, 'create']);

// PUT
$router->put('/sub-categories/{id}', [$subCategoryController, 'update']);

// DELETE
$router->delete('/sub-categories/{id}', [$subCategoryController, 'delete']);
$router->dispatch();