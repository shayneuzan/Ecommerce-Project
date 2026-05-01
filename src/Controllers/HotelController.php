<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\HotelModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class HotelController
{
    private Environment $twig;
    private HotelModel $model;
    private string $basePath;

    public function __construct(Environment $twig, HotelModel $model , string $basePath)
    {
        $this->twig = $twig;
        $this->model = $model;
        $this->basePath = $basePath;
    }

    public function index(Request $request, Response $response): Response
    {
        $hotels = $this->model->findAll();
        
        $html = $this->twig->render('admin/hotels/index.html.twig', [
            'basePath' => $this->basePath,
            'hotels' => $hotels,
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $html = $this->twig->render('admin/hotels/create.html.twig', [
            'basePath' => $this->basePath,
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $this->model->create($data);
        
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $hotel = $this->model->findById($id);
        
        if (!$hotel || !$hotel->id) {
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }
        
        $html = $this->twig->render('admin/hotels/edit.html.twig', [
            'basePath' => $this->basePath,
            'hotel' => $hotel,
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();
        
        $this->model->update($id, $data);
        
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $this->model->delete($id);
        
        return $response->withHeader('', $this->basePath . '/')->withStatus(0);
    }
}
