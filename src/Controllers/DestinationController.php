<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DestinationModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class DestinationController
{
    private Environment $twig;
    private DestinationModel $model;
    private string $basePath;

    public function __construct(Environment $twig, DestinationModel $model, string $basePath)
    {
        $this->twig = $twig;
        $this->model = $model;
        $this->basePath = $basePath;
    }

    public function index(Request $request, Response $response): Response
    {
        $destinations = $this->model->findAll();
        
        $html = $this->twig->render('admin/destinations/index.html.twig', [
            'basePath' => $this->basePath,
            'destinations' => $destinations,
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $html = $this->twig->render('admin/destinations/create.html.twig', [
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
        $destination = $this->model->findById($id);
        
        if (!$destination || !$destination->id) {
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }
        
        $html = $this->twig->render('admin/destinations/edit.html.twig', [
            'basePath' => $this->basePath,
            'destination' => $destination,
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
        
        return $response->withHeader('', $this->basePath . '')->withStatus(0);
    }
}