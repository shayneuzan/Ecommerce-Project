<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\GuideModel;
use App\Models\DestinationModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class GuideController
{
    private Environment $twig;
    private GuideModel $model;
    private DestinationModel $destinationModel;
    private string $basePath;

    public function __construct(Environment $twig, GuideModel $model , string $basePath)
    {
        $this->twig = $twig;
        $this->model = $model;
        $this->basePath = $basePath;
        $this->destinationModel = new DestinationModel();
    }

    public function index(Request $request, Response $response): Response
    {
        $guides = $this->model->findAll();
        $destinations = $this->destinationModel->findAll();
        
        $html = $this->twig->render('admin/guides/index.html.twig', [
            'basePath' => $this->basePath,
            'guides' => $guides,
            'destinations' => $destinations,
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response): Response
    {
        $destinations = $this->destinationModel->findAll();

        $html = $this->twig->render('admin/guides/create.html.twig', [
            'basePath' => $this->basePath,
            'app_lang' => $_SESSION['lang'] ?? 'en',
            'destinations' => $destinations,
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
        $guide = $this->model->findById($id);
        $destinations = $this->destinationModel->findAll();
        
        if (!$guide || !$guide->id) {
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }
        
        $html = $this->twig->render('admin/guides/edit.html.twig', [
            'basePath' => $this->basePath,
            'guide' => $guide,
            'app_lang' => $_SESSION['lang'] ?? 'en', 
            'destinations' => $destinations,
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
