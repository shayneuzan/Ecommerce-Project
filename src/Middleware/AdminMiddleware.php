<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\FlashHelper;

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

        //if logged in but not admin, redirect to login with access denied message
        if (($_SESSION['authenticated'] ?? false) === true && ($_SESSION['user_role'] ?? '') !== 'admin') {
            FlashHelper::add('danger', 'Access Denied: You do not have permission to access this page.');
            return $this->responseFactory->createResponse(302)
                ->withHeader('Location', $this->basePath . '/auth/login');
        }
        
        //if not admin redirect to homepage with access denied message
        FlashHelper::add('danger', 'Access Denied: Please log in to access this page.');
        return $this->responseFactory->createResponse(302)
            ->withHeader('Location', $this->basePath . '/auth/login');
    }
}
