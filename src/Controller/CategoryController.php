<?php

use OpenApi\Attributes as OAT;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../validation.php';

#[OAT\Schema(
    schema: 'Category',
    type: 'object',
    required: ['category_id', 'active', 'name'],
    properties: [
        new OAT\Property(
            property: 'category_id',
            type: 'integer',
            minimum: 1,
            description: 'Interne ID der Kategorie',
            example: 1
        ),
        new OAT\Property(
            property: 'active',
            type: 'integer',
            enum: [0, 1],
            description: '0 = inaktiv, 1 = aktiv',
            example: 1
        ),
        new OAT\Property(
            property: 'name',
            type: 'string',
            example: 'Getränke'
        )
    ]
)]
#[OAT\Schema(
    schema: 'CategoryError',
    type: 'object',
    required: ['error'],
    properties: [
        new OAT\Property(
            property: 'error',
            type: 'string',
            example: 'Category not found.'
        )
    ]
)]
#[OAT\Schema(
    schema: 'CategoryValidationErrors',
    type: 'object',
    required: ['errors'],
    properties: [
        new OAT\Property(
            property: 'errors',
            type: 'array',
            items: new OAT\Items(type: 'string'),
            example: ['Active must be the integer 0 or 1.']
        )
    ]
)]
class CategoryController
{
    /**
     * Return all categories.
     */
    #[OAT\Get(
        path: '/api/v1/categories',
        operationId: 'listCategories',
        tags: ['Kategorien'],
        summary: 'Alle Kategorien abrufen',
        description: 'Gibt alle Kategorien nach ihrer ID sortiert zurück.',
        security: [['cookieAuth' => []]],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Liste der Kategorien. Kann leer sein.',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(
                        ref: '#/components/schemas/Category'
                    )
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError',
                    example: ['error' => 'Unauthorized.']
                )
            )
        ]
    )]
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
    #[OAT\Get(
        path: '/api/v1/category/{category_id}',
        operationId: 'getCategory',
        tags: ['Kategorien'],
        summary: 'Eine Kategorie abrufen',
        security: [['cookieAuth' => []]],
        parameters: [
            new OAT\Parameter(
                name: 'category_id',
                in: 'path',
                required: true,
                description: 'Interne ID der Kategorie',
                schema: new OAT\Schema(
                    type: 'integer',
                    minimum: 1
                ),
                example: 1
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Kategorie gefunden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/Category'
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Kategorie-ID ist keine positive Ganzzahl.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError',
                    example: [
                        'error' => 'Category ID must be a positive integer.'
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError',
                    example: ['error' => 'Unauthorized.']
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Kategorie wurde nicht gefunden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError'
                )
            )
        ]
    )]
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
    #[OAT\Post(
        path: '/api/v1/category',
        operationId: 'createCategory',
        tags: ['Kategorien'],
        summary: 'Eine Kategorie erstellen',
        description: 'Erstellt eine Kategorie. Die ID wird automatisch vergeben.',
        security: [['cookieAuth' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                type: 'object',
                required: ['active', 'name'],
                properties: [
                    new OAT\Property(
                        property: 'active',
                        type: 'integer',
                        enum: [0, 1],
                        example: 1
                    ),
                    new OAT\Property(
                        property: 'name',
                        type: 'string',
                        minLength: 1,
                        maxLength: 500,
                        description: 'Darf nicht nur aus Leerzeichen bestehen.',
                        example: 'Testkategorie'
                    )
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Kategorie wurde erstellt.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/Category'
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Ungültiger Request-Body oder ungültige Felder.',
                content: new OAT\JsonContent(
                    oneOf: [
                        new OAT\Schema(
                            ref: '#/components/schemas/CategoryError'
                        ),
                        new OAT\Schema(
                            ref: '#/components/schemas/CategoryValidationErrors'
                        )
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError',
                    example: ['error' => 'Unauthorized.']
                )
            )
        ]
    )]
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
    #[OAT\Patch(
        path: '/api/v1/category/{category_id}',
        operationId: 'updateCategory',
        tags: ['Kategorien'],
        summary: 'Eine Kategorie teilweise ändern',
        description: 'Ändert active, name oder beide Felder. '
            . 'Nicht übermittelte Felder behalten ihren bisherigen Wert. '
            . 'Unbekannte Felder werden abgelehnt.',
        security: [['cookieAuth' => []]],
        parameters: [
            new OAT\Parameter(
                name: 'category_id',
                in: 'path',
                required: true,
                description: 'Interne ID der Kategorie',
                schema: new OAT\Schema(
                    type: 'integer',
                    minimum: 1
                ),
                example: 1
            )
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                type: 'object',
                minProperties: 1,
                additionalProperties: false,
                properties: [
                    new OAT\Property(
                        property: 'active',
                        type: 'integer',
                        enum: [0, 1],
                        example: 1
                    ),
                    new OAT\Property(
                        property: 'name',
                        type: 'string',
                        minLength: 1,
                        maxLength: 500,
                        description: 'Darf nicht nur aus Leerzeichen bestehen.',
                        example: 'Testkategorie geändert'
                    )
                ],
                example: ['name' => 'Testkategorie geändert']
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Kategorie wurde aktualisiert.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/Category'
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Ungültige ID, ungültiger Body oder ungültige Felder.',
                content: new OAT\JsonContent(
                    oneOf: [
                        new OAT\Schema(
                            ref: '#/components/schemas/CategoryError'
                        ),
                        new OAT\Schema(
                            ref: '#/components/schemas/CategoryValidationErrors'
                        )
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError',
                    example: ['error' => 'Unauthorized.']
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Kategorie wurde nicht gefunden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError'
                )
            )
        ]
    )]
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
    #[OAT\Delete(
        path: '/api/v1/category/{category_id}',
        operationId: 'deleteCategory',
        tags: ['Kategorien'],
        summary: 'Eine Kategorie löschen',
        security: [['cookieAuth' => []]],
        parameters: [
            new OAT\Parameter(
                name: 'category_id',
                in: 'path',
                required: true,
                description: 'Interne ID der Kategorie',
                schema: new OAT\Schema(
                    type: 'integer',
                    minimum: 1
                ),
                example: 1
            )
        ],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'Kategorie wurde gelöscht. Kein Response-Body.'
            ),
            new OAT\Response(
                response: 400,
                description: 'Kategorie-ID ist keine positive Ganzzahl.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError',
                    example: [
                        'error' => 'Category ID must be a positive integer.'
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError',
                    example: ['error' => 'Unauthorized.']
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Kategorie wurde nicht gefunden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/CategoryError'
                )
            )
        ]
    )]
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