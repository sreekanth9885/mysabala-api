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
require_once __DIR__ . '/app/controllers/FoodItemController.php';
require_once __DIR__ . '/app/controllers/UploadController.php';
require_once __DIR__ . '/app/controllers/OrderController.php';
// Instantiate controllers with database
$authController = new AuthController($pdo);
$categoryController = new CategoryController($pdo);
$subCategoryController = new SubCategoryController($pdo);
$foodItemController = new FoodItemController($pdo);
$uploadController = new UploadController($pdo);
$orderController = new OrderController($pdo);

$router = new Router();

$router->post('/login', [$authController, 'login']);
$router->get('/me', [$authController, 'me']);
$router->post('/logout', [$authController, 'logout']);
$router->post('/register', [$authController, 'register']);

$router->post('/categories', [$categoryController, 'create']);
$router->get('/categories', [$categoryController, 'index']);
$router->get('/categories/{id}', [$categoryController, 'show']);
$router->put('/categories/{id}', [$categoryController, 'update']);
$router->delete('/categories/{id}', [$categoryController, 'delete']);

// GET
$router->get('/sub-categories', [$subCategoryController, 'index']);
$router->get('/sub-categories/{id}', [$subCategoryController, 'show']);
$router->get('/categories/{id}/sub-categories', [$subCategoryController, 'byCategory']);
$router->post('/sub-categories', [$subCategoryController, 'create']);
$router->put('/sub-categories/{id}', [$subCategoryController, 'update']);
$router->delete('/sub-categories/{id}', [$subCategoryController, 'delete']);

// FOOD ITEMS ROUTES
$router->post('/food-items', [$foodItemController, 'create']);
$router->get('/food-items', [$foodItemController, 'index']);
$router->get('/food-items/{id}', [$foodItemController, 'show']);
$router->get('/categories/{id}/food-items', [$foodItemController, 'byCategory']);
$router->get('/sub-categories/{id}/food-items', [$foodItemController, 'bySubCategory']);
$router->put('/food-items/{id}', [$foodItemController, 'update']);
$router->delete('/food-items/{id}', [$foodItemController, 'delete']);
$router->post('/upload/food-image', [$uploadController, 'uploadFoodImage']);
$router->post('/orders/create', [$orderController, 'createOrder']);
$router->post('/orders/verify-payment', [$orderController, 'verifyPayment']);

$router->dispatch();