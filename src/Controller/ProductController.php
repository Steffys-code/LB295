<?php

use OpenApi\Attributes as OAT;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../validation.php';

#[OAT\Schema(
    schema: 'Product',
    type: 'object',
    required: [
        'product_id',
        'sku',
        'active',
        'id_category',
        'name',
        'image',
        'description',
        'price',
        'stock'
    ],
    properties: [
        new OAT\Property(
            property: 'product_id',
            type: 'integer',
            minimum: 1,
            example: 1
        ),
        new OAT\Property(
            property: 'sku',
            type: 'string',
            example: 'TEST-001'
        ),
        new OAT\Property(
            property: 'active',
            type: 'integer',
            enum: [0, 1],
            example: 1
        ),
        new OAT\Property(
            property: 'id_category',
            type: 'integer',
            nullable: true,
            description: 'Kategorie-ID oder null, wenn keine Kategorie zugeordnet ist.',
            example: 1
        ),
        new OAT\Property(
            property: 'name',
            type: 'string',
            example: 'Testprodukt'
        ),
        new OAT\Property(
            property: 'image',
            type: 'string',
            example: ''
        ),
        new OAT\Property(
            property: 'description',
            type: 'string',
            example: 'Ein Produkt zum Testen der API.'
        ),
        new OAT\Property(
            property: 'price',
            type: 'string',
            description: 'Der Preis wird als String zurückgegeben.',
            example: '19.90'
        ),
        new OAT\Property(
            property: 'stock',
            type: 'integer',
            minimum: 0,
            maximum: 2147483647,
            example: 10
        )
    ]
)]
#[OAT\Schema(
    schema: 'ProductFields',
    type: 'object',
    required: [
        'active',
        'id_category',
        'name',
        'image',
        'description',
        'price',
        'stock'
    ],
    properties: [
        new OAT\Property(
            property: 'active',
            type: 'integer',
            enum: [0, 1],
            description: '0 = inaktiv, 1 = aktiv',
            example: 1
        ),
        new OAT\Property(
            property: 'id_category',
            description: 'Positive Kategorie-ID oder null. '
                . 'Eine angegebene Kategorie muss existieren. '
                . 'Die API akzeptiert auch eine gültige ID als String.',
            oneOf: [
                new OAT\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 2147483647,
                    nullable: true
                ),
                new OAT\Schema(
                    type: 'string',
                    minLength: 1
                )
            ],
            example: null
        ),
        new OAT\Property(
            property: 'name',
            type: 'string',
            minLength: 1,
            maxLength: 500,
            description: 'Darf nicht nur aus Leerzeichen bestehen.',
            example: 'Testprodukt'
        ),
        new OAT\Property(
            property: 'image',
            type: 'string',
            maxLength: 1000,
            description: 'Bildangabe als Text. Ein leerer String ist erlaubt.',
            example: ''
        ),
        new OAT\Property(
            property: 'description',
            type: 'string',
            description: 'Beschreibung mit maximal 65535 Bytes. '
                . 'Ein leerer String ist erlaubt.',
            example: 'Ein Produkt zum Testen der API.'
        ),
               new OAT\Property(
            property: 'price',
            description: 'Nicht negativer Preis mit maximal zwei Nachkommastellen '
                . 'und maximal 63 Ziffern vor dem Dezimalpunkt. '
                . 'Als String oder Zahl übermittelbar; ein String wird empfohlen.',
            oneOf: [
                new OAT\Schema(
                    type: 'string',
                    pattern: '^[0-9]{1,63}(?:\.[0-9]{1,2})?$'
                ),
                new OAT\Schema(
                    type: 'number',
                    minimum: 0
                )
            ],
            example: '19.90'
        ),
        new OAT\Property(
            property: 'stock',
            type: 'integer',
            minimum: 0,
            maximum: 2147483647,
            example: 10
        )
    ]
)]
#[OAT\Schema(
    schema: 'ProductCreateInput',
    allOf: [
        new OAT\Schema(
            ref: '#/components/schemas/ProductFields'
        ),
        new OAT\Schema(
            type: 'object',
            required: ['sku'],
            properties: [
                new OAT\Property(
                    property: 'sku',
                    type: 'string',
                    minLength: 1,
                    maxLength: 100,
                    description: 'Artikelnummer. Darf nicht nur aus Leerzeichen bestehen.',
                    example: 'TEST-001'
                )
            ]
        )
    ],
    example: [
        'sku' => 'TEST-001',
        'active' => 1,
        'id_category' => null,
        'name' => 'Testprodukt',
        'image' => '',
        'description' => 'Ein Produkt zum Testen der API.',
        'price' => '19.90',
        'stock' => 10
    ]
)]
#[OAT\Schema(
    schema: 'ProductError',
    type: 'object',
    required: ['error'],
    properties: [
        new OAT\Property(
            property: 'error',
            type: 'string',
            example: 'Product not found.'
        )
    ]
)]
#[OAT\Schema(
    schema: 'ProductValidationErrors',
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
class ProductController
{
    /**
     * Return all products.
     */
    #[OAT\Get(
        path: '/api/v1/products',
        operationId: 'listProducts',
        tags: ['Produkte'],
        summary: 'Alle Produkte abrufen',
        description: 'Gibt alle Produkte nach ihrer internen ID sortiert zurück.',
        security: [['cookieAuth' => []]],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Liste der Produkte. Kann leer sein.',
                content: new OAT\JsonContent(
                    type: 'array',
                    items: new OAT\Items(
                        ref: '#/components/schemas/Product'
                    )
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError',
                    example: ['error' => 'Unauthorized.']
                )
            )
        ]
    )]
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
    #[OAT\Get(
        path: '/api/v1/product/{product_id}',
        operationId: 'getProduct',
        tags: ['Produkte'],
        summary: 'Ein Produkt über seine ID abrufen',
        security: [['cookieAuth' => []]],
        parameters: [
            new OAT\Parameter(
                name: 'product_id',
                in: 'path',
                required: true,
                description: 'Interne Produkt-ID, nicht die SKU',
                schema: new OAT\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 2147483647
                ),
                example: 1
            )
        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Produkt gefunden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/Product'
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Produkt-ID ist ungültig.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError',
                    example: [
                        'error' => 'Product ID must be a positive integer.'
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError',
                    example: ['error' => 'Unauthorized.']
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Produkt wurde nicht gefunden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError'
                )
            )
        ]
    )]
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
    #[OAT\Post(
        path: '/api/v1/product',
        operationId: 'createProduct',
        tags: ['Produkte'],
        summary: 'Ein Produkt erstellen',
        description: 'Alle acht bearbeitbaren Felder müssen übermittelt werden. '
            . 'Unbekannte Felder werden abgelehnt. '
            . 'Die interne Produkt-ID wird automatisch vergeben.',
        security: [['cookieAuth' => []]],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                ref: '#/components/schemas/ProductCreateInput'
            )
        ),
        responses: [
            new OAT\Response(
                response: 201,
                description: 'Produkt wurde erstellt.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/Product'
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Ungültiger Body, ungültige Felder '
                    . 'oder nicht vorhandene Kategorie.',
                content: new OAT\JsonContent(
                    oneOf: [
                        new OAT\Schema(
                            ref: '#/components/schemas/ProductError'
                        ),
                        new OAT\Schema(
                            ref: '#/components/schemas/ProductValidationErrors'
                        )
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError',
                    example: ['error' => 'Unauthorized.']
                )
            )
        ]
    )]
    public function createProduct(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        return $this->saveProduct($request, $response, null);
    }

    /**
     * Create or update a product using the SKU from the URL.
     */
    #[OAT\Put(
        path: '/api/v1/product/{sku}',
        operationId: 'updateProduct',
        tags: ['Produkte'],
        summary: 'Ein Produkt über seine SKU erstellen oder aktualisieren',
        description: 'Existiert die SKU bereits, werden alle bearbeitbaren Felder '
            . 'des Produkts ersetzt. Andernfalls wird ein neues Produkt erstellt. '
            . 'Die SKU stammt aus der URL. Eine SKU im Body wird überschrieben. '
            . 'Alle sieben übrigen Produktfelder müssen im Body enthalten sein. '
            . 'Unbekannte Felder werden abgelehnt.',
        security: [['cookieAuth' => []]],
        parameters: [
            new OAT\Parameter(
                name: 'sku',
                in: 'path',
                required: true,
                description: 'Artikelnummer, nicht die interne Produkt-ID. '
                    . 'Darf nicht nur aus Leerzeichen bestehen.',
                schema: new OAT\Schema(
                    type: 'string',
                    minLength: 1,
                    maxLength: 100
                ),
                example: 'TEST-001'
            )
        ],
        requestBody: new OAT\RequestBody(
            required: true,
            content: new OAT\JsonContent(
                ref: '#/components/schemas/ProductFields',
                example: [
                    'active' => 1,
                    'id_category' => null,
                    'name' => 'Testprodukt geändert',
                    'image' => '',
                    'description' => 'Aktualisierte Beschreibung.',
                    'price' => '24.90',
                    'stock' => 15
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Vorhandenes Produkt wurde aktualisiert.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/Product'
                )
            ),
            new OAT\Response(
                response: 201,
                description: 'Neues Produkt wurde erstellt.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/Product'
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Ungültige SKU, ungültiger Body, ungültige Felder '
                    . 'oder nicht vorhandene Kategorie.',
                content: new OAT\JsonContent(
                    oneOf: [
                        new OAT\Schema(
                            ref: '#/components/schemas/ProductError'
                        ),
                        new OAT\Schema(
                            ref: '#/components/schemas/ProductValidationErrors'
                        )
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError',
                    example: ['error' => 'Unauthorized.']
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Ein zuvor gefundenes Produkt ist beim Speichern '
                    . 'nicht mehr vorhanden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError'
                )
            )
        ]
    )]
    public function updateProduct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $sku = $args['sku'] ?? null;

        // Validate the SKU without converting it to an integer.
        if (
            !is_string($sku)
            || !isValidText($sku, 100)
        ) {
            return $this->jsonResponse(
                $response,
                ['error' => 'SKU must contain between 1 and 100 characters.'],
                400
            );
        }

        $data = $request->getParsedBody();

        if (!is_array($data)) {
            return $this->jsonResponse(
                $response,
                ['error' => 'A JSON object is required.'],
                400
            );
        }

        // Use the URL as the authoritative source of the SKU.
        $data['sku'] = $sku;

        // Validate all product data before querying the database.
        $errors = $this->validateProduct($data);

        if ($errors !== []) {
            return $this->jsonResponse(
                $response,
                ['errors' => $errors],
                400
            );
        }

        $database = createDatabaseConnection();

        try {
            // Find the internal ID of the product with this SKU.
            $statement = $database->prepare(
                'SELECT product_id
                 FROM product
                 WHERE sku = ?'
            );

            $statement->bind_param('s', $sku);
            $statement->execute();

            $result = $statement->get_result();
            $existingProduct = $result->fetch_assoc();

            $result->free();
            $statement->close();

            $productId = $existingProduct === null
                ? null
                : (int) $existingProduct['product_id'];
        } finally {
            $database->close();
        }

        // Pass the SKU to the existing validation and save logic.
        $request = $request->withParsedBody($data);

        // A null ID creates a product; an existing ID updates it.
        return $this->saveProduct(
            $request,
            $response,
            $productId
        );
    }

    /**
     * Delete a product.
     */
    #[OAT\Delete(
        path: '/api/v1/product/{product_id}',
        operationId: 'deleteProduct',
        tags: ['Produkte'],
        summary: 'Ein Produkt über seine ID löschen',
        security: [['cookieAuth' => []]],
        parameters: [
            new OAT\Parameter(
                name: 'product_id',
                in: 'path',
                required: true,
                description: 'Interne Produkt-ID, nicht die SKU',
                schema: new OAT\Schema(
                    type: 'integer',
                    minimum: 1,
                    maximum: 2147483647
                ),
                example: 1
            )
        ],
        responses: [
            new OAT\Response(
                response: 204,
                description: 'Produkt wurde gelöscht. Kein Response-Body.'
            ),
            new OAT\Response(
                response: 400,
                description: 'Produkt-ID ist ungültig.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError',
                    example: [
                        'error' => 'Product ID must be a positive integer.'
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'JWT-Cookie fehlt, ist ungültig oder abgelaufen.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError',
                    example: ['error' => 'Unauthorized.']
                )
            ),
            new OAT\Response(
                response: 404,
                description: 'Produkt wurde nicht gefunden.',
                content: new OAT\JsonContent(
                    ref: '#/components/schemas/ProductError'
                )
            )
        ]
    )]
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