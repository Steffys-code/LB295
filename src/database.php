<?php

/**
 * Create a database connection using the application settings.
 */
function createDatabaseConnection(): mysqli
{
    // Read the JSON configuration.
    $content = file_get_contents(
        __DIR__ . '/../config/config.json'
    );

    if ($content === false) {
        throw new RuntimeException('Cannot read configuration file.');
    }

    // Convert JSON into an associative array.
    $config = json_decode(
        $content,
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($config)) {
        throw new RuntimeException(
            'Configuration must be a JSON object.'
        );
    }

    // Validate the database configuration.
    $settings = $config['database'] ?? null;

    if (!is_array($settings)) {
        throw new RuntimeException(
            'Database configuration is missing or invalid.'
        );
    }

    foreach (['host', 'username', 'password', 'name'] as $field) {
        if (
            !isset($settings[$field])
            || !is_string($settings[$field])
        ) {
            throw new RuntimeException(
                'Invalid database configuration field: ' . $field
            );
        }
    }

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