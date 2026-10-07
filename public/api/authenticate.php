<?php

require_once __DIR__ . '/../../src/Controller/AuthController.php';

// Create the controller using the application settings.
$authController = new AuthController($config);

// Login does not require a JWT.
$group->post('/authenticate', [$authController, 'authenticate']);