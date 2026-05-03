<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AdminMiddleware
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private string $basePath,
    ) {}

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        //check if user is logged in and is an admin
        if (($_SESSION['authenticated'] ?? false) === true && ($_SESSION['user_role'] ?? '') === 'admin') {
            return $handler->handle($request);
        }

        //if not admin redirect to homepage
        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->basePath . '/');
    }
}