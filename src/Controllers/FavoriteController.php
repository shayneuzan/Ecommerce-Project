<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\FavoriteModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use App\Services\FlashHelper;

class FavoriteController{

private Environment $twig;
    private FavoriteModel $model;
    private string $basePath;

    public function __construct(Environment $twig, FavoriteModel $model , string $basePath)
    {
        $this->twig = $twig;
        $this->model = $model;
        $this->basePath = $basePath;
    } 

    public function toggle(Request $request, Response $response, array $args): Response{
        $userId = $_SESSION['user_id'] ?? null;
        $packageId = (int)$args['packageId'];

        if(!$userId){ 
            $response->getBody()->write(json_encode(['error' => 'Must be logged in to see fovorites']));

            return $response -> withHeader('Content-Type', 'application/json')
                             ->withStatus(401);
        }

        $isLiked = $this->model->toggle($userId, $packageId);

        $response->getBody()->write(json_encode(['liked' => $isLiked]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function index(Request $request, Response $response) : Response{
        $userId = $_SESSION['user_id'] ?? null;

        if(!$userId){
            return $response ->withHeader('Location', $this->basePath . '/auth/login')
                             ->withStatus(302);
        }

        $favorites = $this->model->getUserFavorites($userId);

        $html = $this->twig->render('favorite/fovorite.html.twig', [
            'base_path'         => $this->basePath,
            'favorites'        => $favorites,
            'app_lang'          => $_SESSION['lang'] ?? 'en',
            'app_authenticated' => $_SESSION['authenticated'] ?? false,
            'app_user_name'     => $_SESSION['user_name'] ?? '',
            'app_role'          => $_SESSION['user_role'] ?? '',
        ]);

        $response->getBody()->write($html);
        return $response;
    }



}