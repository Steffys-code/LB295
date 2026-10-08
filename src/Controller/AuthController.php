<?php

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

require_once __DIR__ . '/../authentication.php';

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