<?php

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

// Load installed Composer packages.
require_once __DIR__ . '/../vendor/autoload.php';

// Read the JSON configuration.
$content = file_get_contents(__DIR__ . '/../config/config.json');

if ($content === false) {
    throw new RuntimeException('Cannot read configuration file.');
}

// Convert JSON into an associative array.
$config = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

if (!is_array($config)) {
    throw new RuntimeException('Configuration must be a JSON object.');
}

// Load database functions.
require_once __DIR__ . '/../src/database.php';

// Create the Slim application.
$app = AppFactory::create();

// Read JSON request bodies and find matching routes.
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Show error details during local development only.
$app->addErrorMiddleware(true, true, true);

// Add the API prefix to all routes in this group.
$app->group('/api/v1', function (RouteCollectorProxy $group) use ($config) {
    require __DIR__ . '/api/authenticate.php';
    require __DIR__ . '/api/categories.php';
    require __DIR__ . '/api/products.php';
});

// Handle the incoming request.
$app->run();