<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\DestinationModel;
use App\Models\HotelModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use App\Services\FlashHelper;

class HotelController
{
    private Environment $twig;
    private HotelModel $model;
    private string $basePath;
    private DestinationModel $destinationModel;

    public function __construct(Environment $twig, HotelModel $model , string $basePath)
    {
        $this->twig = $twig;
        $this->model = $model;
        $this->basePath = $basePath;
        $this->destinationModel = new DestinationModel();
    }

    public function create(Request $request, Response $response): Response
    {
        $destinations = $this->destinationModel->findAll();
        $html = $this->twig->render('admin/hotels/create.html.twig', [
            'basePath' => $this->basePath,
            'destinations' => $destinations,
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        $this->model->create($data);
        $hotelName = $data['hotel_name'];
        
        FlashHelper::add('success', "Hotel '$hotelName' created successfully");
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $hotel = $this->model->findById($id);
        $destinations = $this->destinationModel->findAll();
        
        if (!$hotel || !$hotel->id) {
            FlashHelper::add('danger', "Hotel ID: $id not found in the data records");
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }
        
        $html = $this->twig->render('admin/hotels/edit.html.twig', [
            'basePath' => $this->basePath,
            'hotel' => $hotel,
            'destinations' => $destinations,
            'app_lang' => $_SESSION['lang'] ?? 'en',
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();
        
        $this->model->update($id, $data);
        $hotelName = $data['hotel_name'] ?? 'Unknown Hotel';
        
        FlashHelper::add('success', "Hotel '$hotelName' has been updated successfully");        
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $hotelName = $this->model->findById($id)->hotel_name ?? 'Unknown Hotel';

        $this->model->delete($id);
        
        FlashHelper::add('success', "Hotel '$hotelName' has been deleted successfully");        
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }
}
