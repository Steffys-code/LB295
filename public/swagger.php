<?php

// Load installed Composer packages.
require_once __DIR__ . '/../vendor/autoload.php';

// Load all controller classes before reading their OAT attributes.
require_once __DIR__ . '/../src/Controller/AuthController.php';
require_once __DIR__ . '/../src/Controller/CategoryController.php';
require_once __DIR__ . '/../src/Controller/ProductController.php';

// Build the documentation from all three controller files.
$result = (new \OpenApi\Builder())
    ->addSource(__DIR__ . '/../src/Controller/AuthController.php')
    ->addSource(__DIR__ . '/../src/Controller/CategoryController.php')
    ->addSource(__DIR__ . '/../src/Controller/ProductController.php')
    ->build();

// Prevent an old documentation response from being reused.
header('Cache-Control: no-store');

// Return the documentation as YAML.
header('Content-Type: application/x-yaml; charset=utf-8');

echo $result->toYaml();
