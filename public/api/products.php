<?php

require_once __DIR__ . '/../../src/Controller/ProductController.php';
require_once __DIR__ . '/../../src/authentication.php';

// Create the controller and JWT middleware.
$productController = new ProductController();
$authMiddleware = createAuthMiddleware($config);

// List all products.
$group->get(
    '/products',
    [$productController, 'listProducts']
)->add($authMiddleware);

// Get one product.
$group->get(
    '/product/{product_id}',
    [$productController, 'getProduct']
)->add($authMiddleware);

// Create a product.
$group->post(
    '/product',
    [$productController, 'createProduct']
)->add($authMiddleware);

// Replace all editable product fields.
$group->put(
    '/product/{product_id}',
    [$productController, 'updateProduct']
)->add($authMiddleware);

// Delete a product.
$group->delete(
    '/product/{product_id}',
    [$productController, 'deleteProduct']
)->add($authMiddleware);