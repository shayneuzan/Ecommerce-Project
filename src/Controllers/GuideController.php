<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\GuideModel;
use App\Models\DestinationModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use App\Services\FlashHelper;

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
        $guideName = $data['guide_name'];

        $this->model->create($data);
        
        FlashHelper::add('success', "Guide '$guideName' created successfully");
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $guide = $this->model->findById($id);
        $destinations = $this->destinationModel->findAll();
        
        if (!$guide || !$guide->id) {
            FlashHelper::add('danger', "Guide ID: $id not found in the data records");
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
        $guideName = $data['guide_name'];

        FlashHelper::add('success', "Guide '$guideName' updated successfully");
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $guideName = $this->model->findById($id)->guide_name ?? 'Unknown Guide';
        $data = $this->model->delete($id);

        if (!$data) {
            FlashHelper::add('danger', "Guide ID: $id not found in the data records");
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }

        FlashHelper::add('success', "Guide '$guideName' deleted successfully");
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }
}
