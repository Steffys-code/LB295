<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../validation.php';

class CategoryController
{
    /**
     * Return all categories.
     */
    public function listCategories(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $database = createDatabaseConnection();

        $result = $database->query(
            'SELECT category_id, active, name
             FROM category
             ORDER BY category_id'
        );

        $categories = [];

        while ($category = $result->fetch_assoc()) {
            // Return numeric fields as JSON numbers.
            $category['category_id'] = (int) $category['category_id'];
            $category['active'] = (int) $category['active'];

            $categories[] = $category;
        }

        $result->free();
        $database->close();

        return $this->jsonResponse($response, $categories, 200);
    }

    /**
     * Return one category by its ID.
     */
    public function getCategory(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = $args['category_id'] ?? null;

        if (!isValidId($id)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Category ID must be a positive integer.'],
                400
            );
        }

        $categoryId = (int) $id;
        $database = createDatabaseConnection();

        $statement = $database->prepare(
            'SELECT category_id, active, name
             FROM category
             WHERE category_id = ?'
        );

        $statement->bind_param('i', $categoryId);
        $statement->execute();

        $result = $statement->get_result();
        $category = $result->fetch_assoc();

        $result->free();
        $statement->close();
        $database->close();

        if ($category === null) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Category not found.'],
                404
            );
        }

        $category['category_id'] = (int) $category['category_id'];
        $category['active'] = (int) $category['active'];

        return $this->jsonResponse($response, $category, 200);
    }

    /**
     * Validate input and create a category.
     */
    public function createCategory(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = $request->getParsedBody();

        if (!is_array($data)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'A JSON object is required.'],
                400
            );
        }

        $errors = validateRequiredFields($data, ['active', 'name']);

        if ($errors !== []) {
            return $this->jsonResponse(
                $response,
                ['errors' => $errors],
                400
            );
        }

        if (!isValidActive($data['active'])) {
            $errors[] = 'Active must be the integer 0 or 1.';
        }

        if (!isValidText($data['name'], 500)) {
            $errors[] = 'Name must contain between 1 and 500 characters.';
        }

        if ($errors !== []) {
            return $this->jsonResponse(
                $response,
                ['errors' => $errors],
                400
            );
        }

        $active = $data['active'];
        $name = $data['name'];

        $database = createDatabaseConnection();

        $statement = $database->prepare(
            'INSERT INTO category (active, name) VALUES (?, ?)'
        );

        // "i" means integer and "s" means string.
        $statement->bind_param('is', $active, $name);
        $statement->execute();

        $category = [
            'category_id' => (int) $database->insert_id,
            'active' => $active,
            'name' => $name,
        ];

        $statement->close();
        $database->close();

        return $this->jsonResponse($response, $category, 201);
    }

    /**
     * Update only the fields included in the request.
     */
    public function updateCategory(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = $args['category_id'] ?? null;

        if (!isValidId($id)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Category ID must be a positive integer.'],
                400
            );
        }

        $data = $request->getParsedBody();

        if (!is_array($data) || $data === []) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Provide active or name to update.'],
                400
            );
        }

        $errors = [];

        // Reject unknown fields, including changes to the primary key.
        foreach (array_keys($data) as $field) {
            if (!in_array($field, ['active', 'name'], true)) {
                $errors[] = "Unknown field: {$field}.";
            }
        }

        $hasActive = array_key_exists('active', $data);
        $hasName = array_key_exists('name', $data);

        if ($hasActive && !isValidActive($data['active'])) {
            $errors[] = 'Active must be the integer 0 or 1.';
        }

        if ($hasName && !isValidText($data['name'], 500)) {
            $errors[] = 'Name must contain between 1 and 500 characters.';
        }

        if ($errors !== []) {
            return $this->jsonResponse(
                $response,
                ['errors' => $errors],
                400
            );
        }

        $categoryId = (int) $id;
        $database = createDatabaseConnection();

        // Lock the category until the update is complete.
        $database->begin_transaction();

        try {
            $statement = $database->prepare(
                'SELECT category_id, active, name
                 FROM category
                 WHERE category_id = ?
                 FOR UPDATE'
            );

            $statement->bind_param('i', $categoryId);
            $statement->execute();

            $result = $statement->get_result();
            $category = $result->fetch_assoc();

            $result->free();
            $statement->close();

            if ($category === null) {
                $database->rollback();

                return $this->jsonResponse(
                    $response,
                    ['error' => 'Category not found.'],
                    404
                );
            }

            // Preserve existing values for fields that were not sent.
            $active = $hasActive
                ? $data['active']
                : (int) $category['active'];

            $name = $hasName
                ? $data['name']
                : $category['name'];

            $statement = $database->prepare(
                'UPDATE category
                 SET active = ?, name = ?
                 WHERE category_id = ?'
            );

            $statement->bind_param('isi', $active, $name, $categoryId);
            $statement->execute();
            $statement->close();

            $database->commit();

            return $this->jsonResponse(
                $response,
                [
                    'category_id' => $categoryId,
                    'active' => $active,
                    'name' => $name,
                ],
                200
            );
        } catch (\Throwable $exception) {
            // Undo the transaction if a database operation fails.
            $database->rollback();
            throw $exception;
        } finally {
            $database->close();
        }
    }

    /**
     * Delete one category by its ID.
     */
    public function deleteCategory(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = $args['category_id'] ?? null;

        if (!isValidId($id)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Category ID must be a positive integer.'],
                400
            );
        }

        $categoryId = (int) $id;
        $database = createDatabaseConnection();

        $statement = $database->prepare(
            'DELETE FROM category WHERE category_id = ?'
        );

        $statement->bind_param('i', $categoryId);
        $statement->execute();

        $deletedRows = $statement->affected_rows;

        $statement->close();
        $database->close();

        if ($deletedRows === 0) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Category not found.'],
                404
            );
        }

        // A successful deletion returns no response body.
        return $response->withStatus(204);
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