<?php

use OpenApi\Attributes as OAT;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/../authentication.php';

#[OAT\Info(
    title: 'Produkt- und Kategorien-API',
    version: '1.0.0',
    description: 'REST-API für die Verwaltung von Produkten und Kategorien.'
)]
#[OAT\Server(
    url: '/',
    description: 'Aktueller Server'
)]
#[OAT\SecurityScheme(
    securityScheme: 'cookieAuth',
    type: 'apiKey',
    in: 'cookie',
    name: 'token',
    description: 'JWT im Cookie token. Das Cookie wird beim Login gesetzt.'
)]
class AuthController
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Check login credentials and return a JWT cookie.
     */
    #[OAT\Post(
        path: '/api/v1/authenticate',
        operationId: 'authenticate',
        tags: ['Authentifizierung'],
        summary: 'Anmelden und JWT-Cookie erhalten',
        description: 'Prüft die Zugangsdaten, setzt das JWT-Cookie token '
            . 'und gibt den JWT zusätzlich als JSON zurück.',
        security: [],
        requestBody: new OAT\RequestBody(
            required: true,
            description: 'Zugangsdaten für die Anmeldung',
            content: new OAT\JsonContent(
                type: 'object',
                required: ['username', 'password'],
                properties: [
                    new OAT\Property(
                        property: 'username',
                        type: 'string',
                        minLength: 1,
                        description: 'Benutzername',
                        example: 'demo'
                    ),
                    new OAT\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 1,
                        description: 'Passwort',
                        example: 'example-password'
                    )
                ]
            )
        ),
        responses: [
            new OAT\Response(
                response: 200,
                description: 'Anmeldung erfolgreich. Das JWT-Cookie wird gesetzt.',
                headers: [
                    new OAT\Header(
                        header: 'Set-Cookie',
                        description: 'Setzt das Cookie token mit Path=/api/v1, '
                            . 'Max-Age, HttpOnly und SameSite=Lax. '
                            . 'Bei HTTPS wird zusätzlich Secure gesetzt.',
                        schema: new OAT\Schema(
                            type: 'string'
                        )
                    )
                ],
                content: new OAT\JsonContent(
                    type: 'object',
                    required: ['token'],
                    properties: [
                        new OAT\Property(
                            property: 'token',
                            type: 'string',
                            description: 'JWT für authentifizierte API-Aufrufe'
                        )
                    ]
                )
            ),
            new OAT\Response(
                response: 400,
                description: 'Benutzername oder Passwort fehlt oder ist ungültig.',
                content: new OAT\JsonContent(
                    type: 'object',
                    required: ['error'],
                    properties: [
                        new OAT\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Username and password are required.'
                        )
                    ]
                )
            ),
            new OAT\Response(
                response: 401,
                description: 'Benutzername oder Passwort ist falsch.',
                content: new OAT\JsonContent(
                    type: 'object',
                    required: ['error'],
                    properties: [
                        new OAT\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Invalid username or password.'
                        )
                    ]
                )
            )
        ]
    )]
    public function authenticate(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $data = $request->getParsedBody();

        // Reject missing or invalid login fields.
        if (
            !is_array($data)
            || !isset($data['username'], $data['password'])
            || !is_string($data['username'])
            || !is_string($data['password'])
            || trim($data['username']) === ''
            || $data['password'] === ''
        ) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Username and password are required.'],
                400
            );
        }

        // Use credentials from the loaded application configuration.
        $credentials = $this->config['auth'] ?? null;

        if (
            !is_array($credentials)
            || !isset($credentials['username'], $credentials['password'])
            || !is_string($credentials['username'])
            || !is_string($credentials['password'])
            || trim($credentials['username']) === ''
            || $credentials['password'] === ''
        ) {
            throw new RuntimeException('Invalid login configuration.');
        }

        // Compare credentials without returning the stored values.
        $usernameMatches = hash_equals(
            $credentials['username'],
            $data['username']
        );

        $passwordMatches = hash_equals(
            $credentials['password'],
            $data['password']
        );

        if (!$usernameMatches || !$passwordMatches) {
            return $this->jsonResponse(
                $response,
                ['error' => 'Invalid username or password.'],
                401
            );
        }

        // Create a token after successful authentication.
        $token = createJwt($data['username'], $this->config);

        // Store the JWT in a cookie for subsequent API requests.
        $cookie = 'token=' . rawurlencode($token)
            . '; Path=/api/v1'
            . '; Max-Age=' . (int) $this->config['jwt']['lifetime']
            . '; HttpOnly'
            . '; SameSite=Lax';

        // Send cookies over HTTPS only when HTTPS is used.
        if ($request->getUri()->getScheme() === 'https') {
            $cookie .= '; Secure';
        }

        $response = $response->withAddedHeader('Set-Cookie', $cookie);

        return $this->jsonResponse(
            $response,
            ['token' => $token],
            200
        );
    }

    /**
     * Create a JSON response with the given status code.
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