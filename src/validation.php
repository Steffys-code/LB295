<?php

/**
 * Find missing fields. Null is allowed for optional values.
 */
function validateRequiredFields(array $data, array $fields): array
{
    $errors = [];

    foreach ($fields as $field) {
        if (!array_key_exists($field, $data)) {
            $errors[] = "Missing field: {$field}.";
        }
    }

    return $errors;
}

/**
 * Check an ID from a URL or request body.
 */
function isValidId(mixed $value): bool
{
    // Reject booleans, floats, arrays and null.
    if (!is_int($value) && !is_string($value)) {
        return false;
    }

    return filter_var($value, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1,
            'max_range' => 2147483647,
        ],
    ]) !== false;
}

/**
 * Check text length and optionally allow empty text.
 */
function isValidText(
    mixed $value,
    int $maxLength,
    bool $allowEmpty = false
): bool {
    if (!is_string($value)) {
        return false;
    }

    if (!$allowEmpty && trim($value) === '') {
        return false;
    }

    return mb_strlen($value, 'UTF-8') <= $maxLength;
}

/**
 * Accept only integer values 0 and 1.
 */
function isValidActive(mixed $value): bool
{
    return $value === 0 || $value === 1;
}

/**
 * Check stock against the database INTEGER range.
 */
function isValidStock(mixed $value): bool
{
    return is_int($value)
        && $value >= 0
        && $value <= 2147483647;
}

/**
 * Check a non-negative price with at most two decimal places.
 */
function isValidPrice(mixed $value): bool
{
    if (!is_int($value) && !is_float($value) && !is_string($value)) {
        return false;
    }

    // DECIMAL(65,2) allows up to 63 digits before the decimal point.
    return preg_match(
        '/^[0-9]{1,63}(?:\.[0-9]{1,2})?$/D',
        (string) $value
    ) === 1;
}

/**
 * A product may have no category.
 */
function isValidCategoryId(mixed $value): bool
{
    return $value === null || isValidId($value);
}