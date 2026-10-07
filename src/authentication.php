<?php

use ReallySimpleJWT\Token;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

/**
 * Create a token after a successful login.
 */
function createJwt(string $username, array $config): string
{
    return Token::create(
        $username,
        $config['jwt']['secret'],
        time() + $config['jwt']['lifetime'],
        $config['jwt']['issuer']
    );
}

/**
 * Check the signature, expiration, algorithm and issuer.
 */
function isValidJwt(string $token, array $config): bool
{
    try {
        if (!Token::validate($token, $config['jwt']['secret'])) {
            return false;
        }

        if (!Token::validateExpiration($token)) {
            return false;
        }

        $header = Token::getHeader($token);
        $payload = Token::getPayload($token);

        return ($header['alg'] ?? null) === 'HS256'
            && ($payload['iss'] ?? null) === $config['jwt']['issuer'];
    } catch (\Exception $exception) {
        // Invalid or malformed tokens must not grant access.
        return false;
    }
}

/**
 * Create middleware for protected routes.
 */
function createAuthMiddleware(array $config): callable
{
    return function (
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ) use ($config): ResponseInterface {
        $authorization = $request->getHeaderLine('Authorization');

        // Expected header: Authorization: Bearer TOKEN
        if (preg_match('/^Bearer\s+(\S+)$/i', $authorization, $matches)) {
            if (isValidJwt($matches[1], $config)) {
                return $handler->handle($request);
            }
        }

        // Reject missing, invalid or expired tokens.
        $response = new Response();

        $response->getBody()->write(
            json_encode(['error' => 'Unauthorized.'])
        );

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('WWW-Authenticate', 'Bearer')
            ->withStatus(401);
    };
}