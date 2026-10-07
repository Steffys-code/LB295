<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../validation.php';

class ProductController
{
    /**
     * Return all products.
     */
    public function listProducts(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $database = createDatabaseConnection();

        $result = $database->query(
            'SELECT product_id, sku, active, id_category, name,
                    image, description, price, stock
             FROM product
             ORDER BY product_id'
        );

        $products = [];

        while ($product = $result->fetch_assoc()) {
            $products[] = $this->formatProduct($product);
        }

        $result->free();
        $database->close();

        return $this->jsonResponse($response, $products, 200);
    }

    /**
     * Return one product.
     */
    public function getProduct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = $args['product_id'] ?? null;

        if (!isValidId($id)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Product ID must be a positive integer.'],
                400
            );
        }

        $database = createDatabaseConnection();

        try {
            $product = $this->findProduct($database, (int) $id);

            if ($product === null) {
                return $this->jsonResponse(
                    $response,
                    ['error' => 'Product not found.'],
                    404
                );
            }

            return $this->jsonResponse($response, $product, 200);
        } finally {
            $database->close();
        }
    }

    /**
     * Create a product.
     */
    public function createProduct(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $this->saveProduct($request, $response, null);
    }

    /**
     * Replace all editable product fields.
     */
    public function updateProduct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = $args['product_id'] ?? null;

        if (!isValidId($id)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Product ID must be a positive integer.'],
                400
            );
        }

        return $this->saveProduct($request, $response, (int) $id);
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = $args['product_id'] ?? null;

        if (!isValidId($id)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Product ID must be a positive integer.'],
                400
            );
        }

        $productId = (int) $id;
        $database = createDatabaseConnection();

        try {
            $statement = $database->prepare(
                'DELETE FROM product WHERE product_id = ?'
            );

            $statement->bind_param('i', $productId);
            $statement->execute();

            $deletedRows = $statement->affected_rows;
            $statement->close();

            if ($deletedRows === 0) {
                return $this->jsonResponse(
                    $response,
                    ['error' => 'Product not found.'],
                    404
                );
            }

            return $response->withStatus(204);
        } finally {
            $database->close();
        }
    }

    /**
     * Validate and save a complete product.
     */
    private function saveProduct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ?int $productId
    ): ResponseInterface {
        $data = $request->getParsedBody();

        if (!is_array($data)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'A JSON object is required.'],
                400
            );
        }

        $errors = $this->validateProduct($data);

        if ($errors !== []) {
            return $this->jsonResponse(
                $response,
                ['errors' => $errors],
                400
            );
        }

        $sku = $data['sku'];
        $active = $data['active'];
        $categoryId = $data['id_category'] === null
            ? null
            : (int) $data['id_category'];
        $name = $data['name'];
        $image = $data['image'];
        $description = $data['description'];

        // Bind the price as text to avoid additional floating-point rounding.
        $price = (string) $data['price'];
        $stock = $data['stock'];

        $database = createDatabaseConnection();
        $database->begin_transaction();

        try {
            if ($productId !== null) {
                // Lock the product until the update is complete.
                $existing = $this->findProduct(
                    $database,
                    $productId,
                    true
                );

                if ($existing === null) {
                    $database->rollback();

                    return $this->jsonResponse(
                        $response,
                        ['error' => 'Product not found.'],
                        404
                    );
                }
            }

            if ($categoryId !== null) {
                // Check and lock the referenced category during saving.
                $statement = $database->prepare(
                    'SELECT category_id FROM category
                     WHERE category_id = ?
                     LOCK IN SHARE MODE'
                );

                $statement->bind_param('i', $categoryId);
                $statement->execute();

                $result = $statement->get_result();
                $categoryExists = $result->fetch_assoc() !== null;

                $result->free();
                $statement->close();

                if (!$categoryExists) {
                    $database->rollback();

                    return $this->jsonResponse(
                        $response,
                        ['error' => 'The selected category does not exist.'],
                        400
                    );
                }
            }

            if ($productId === null) {
                $statement = $database->prepare(
                    'INSERT INTO product
                     (sku, active, id_category, name, image,
                      description, price, stock)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $statement->bind_param(
                    'siissssi',
                    $sku,
                    $active,
                    $categoryId,
                    $name,
                    $image,
                    $description,
                    $price,
                    $stock
                );
            } else {
                $statement = $database->prepare(
                    'UPDATE product
                     SET sku = ?, active = ?, id_category = ?,
                         name = ?, image = ?, description = ?,
                         price = ?, stock = ?
                     WHERE product_id = ?'
                );

                $statement->bind_param(
                    'siissssii',
                    $sku,
                    $active,
                    $categoryId,
                    $name,
                    $image,
                    $description,
                    $price,
                    $stock,
                    $productId
                );
            }

            $statement->execute();

            $isNew = $productId === null;

            if ($isNew) {
                $productId = (int) $database->insert_id;
            }

            $statement->close();

            // Read the saved values, including the database price format.
            $product = $this->findProduct($database, $productId);

            $database->commit();

            return $this->jsonResponse(
                $response,
                $product,
                $isNew ? 201 : 200
            );
        } catch (\Throwable $exception) {
            $database->rollback();
            throw $exception;
        } finally {
            $database->close();
        }
    }

    /**
     * Validate all editable product fields.
     */
    private function validateProduct(array $data): array
    {
        $fields = [
            'sku',
            'active',
            'id_category',
            'name',
            'image',
            'description',
            'price',
            'stock',
        ];

        $errors = validateRequiredFields($data, $fields);

        if ($errors !== []) {
            return $errors;
        }

        foreach (array_keys($data) as $field) {
            if (!in_array($field, $fields, true)) {
                $errors[] = "Unknown field: {$field}.";
            }
        }

        if (!isValidText($data['sku'], 100)) {
            $errors[] = 'SKU must contain between 1 and 100 characters.';
        }

        if (!isValidActive($data['active'])) {
            $errors[] = 'Active must be the integer 0 or 1.';
        }

        if (!isValidCategoryId($data['id_category'])) {
            $errors[] = 'Category ID must be a positive integer or null.';
        }

        if (!isValidText($data['name'], 500)) {
            $errors[] = 'Name must contain between 1 and 500 characters.';
        }

        if (!isValidText($data['image'], 1000, true)) {
            $errors[] = 'Image must be text with at most 1000 characters.';
        }

        // MySQL TEXT has a byte limit, not a character limit.
        if (
            !is_string($data['description'])
            || strlen($data['description']) > 65535
        ) {
            $errors[] = 'Description must be text with at most 65535 bytes.';
        }

        if (!isValidPrice($data['price'])) {
            $errors[] = 'Price must be non-negative with at most two decimals.';
        }

        if (!isValidStock($data['stock'])) {
            $errors[] = 'Stock must be a non-negative INTEGER value.';
        }

        return $errors;
    }

    /**
     * Find a product, optionally locking it inside a transaction.
     */
    private function findProduct(
        mysqli $database,
        int $productId,
        bool $lock = false
    ): ?array {
        $sql = 'SELECT product_id, sku, active, id_category, name,
                       image, description, price, stock
                FROM product
                WHERE product_id = ?';

        if ($lock) {
            $sql .= ' FOR UPDATE';
        }

        $statement = $database->prepare($sql);
        $statement->bind_param('i', $productId);
        $statement->execute();

        $result = $statement->get_result();
        $product = $result->fetch_assoc();

        $result->free();
        $statement->close();

        return $product === null ? null : $this->formatProduct($product);
    }

    /**
     * Format database values for JSON.
     */
    private function formatProduct(array $product): array
    {
        $product['product_id'] = (int) $product['product_id'];
        $product['active'] = (int) $product['active'];
        $product['stock'] = (int) $product['stock'];

        $product['id_category'] = $product['id_category'] === null
            ? null
            : (int) $product['id_category'];

        // Preserve the exact DECIMAL value instead of converting to float.
        $product['price'] = (string) $product['price'];

        return $product;
    }

    /**
     * Create a JSON response.
     */
    private function jsonResponse(
        ResponseInterface $response,
        array $data,
        int $status
    ): ResponseInterface {
        $response->getBody()->write(
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}