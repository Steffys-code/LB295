<?php

require_once __DIR__ . '/../../src/Controller/CategoryController.php';
require_once __DIR__ . '/../../src/authentication.php';

// Create the controller and JWT middleware.
$categoryController = new CategoryController();
$authMiddleware = createAuthMiddleware($config);

// List all categories.
$group->get(
    '/categories',
    [$categoryController, 'listCategories']
)->add($authMiddleware);

// Get one category.
$group->get(
    '/category/{category_id}',
    [$categoryController, 'getCategory']
)->add($authMiddleware);

// Create a category.
$group->post(
    '/category',
    [$categoryController, 'createCategory']
)->add($authMiddleware);

// Update selected category fields.
$group->patch(
    '/category/{category_id}',
    [$categoryController, 'updateCategory']
)->add($authMiddleware);

// Delete a category.
$group->delete(
    '/category/{category_id}',
    [$categoryController, 'deleteCategory']
)->add($authMiddleware);