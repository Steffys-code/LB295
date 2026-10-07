<?php

/**
 * Create a database connection using the application settings.
 */
function createDatabaseConnection(): mysqli
{
    // Load configuration relative to this file.
    $config = require __DIR__ . '/../config/config.json';
    $settings = $config['database'];

    // Throw an exception when a database operation fails.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $database = new mysqli(
        $settings['host'],
        $settings['username'],
        $settings['password'],
        $settings['name']
    );

    // Support Unicode characters, including emojis.
    $database->set_charset('utf8mb4');

    return $database;
}