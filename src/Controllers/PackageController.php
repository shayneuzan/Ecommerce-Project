<?php

//This class handles the packages listing page and single package detail page
//reads filters and search queries from the URL, asks the model for the right packages, 
// and passes them to the correct twig template 


declare(strict_types=1);

namespace App\Controllers;

use App\Models\PackageModel;
use App\Models\HotelModel;
use App\Models\GuideModel;
use App\Models\DestinationModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use App\Services\FlashHelper;

class PackageController
{
    public function __construct(
        private Environment $twig,
        private PackageModel $model,
        private string $basePath,
        private HotelModel $hotelModel,
        private GuideModel $guideModel,
        private DestinationModel $destinationModel,
    ) {
    }

    //GET /packages — show all packages with search and filter support
    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        //read filter values from the URL query string
        $country = $params['country'] ?? null;
        $days    = isset($params['days']) && $params['days'] !== '' ? (int) $params['days'] : null;
        $budget  = isset($params['budget']) && $params['budget'] !== '' ? (float) $params['budget'] : null;
        $search  = $params['search'] ?? null;

        //if there is a search query use search, otherwise use filters
        if ($search) {
            $packages = $this->model->search($search);
        } else {
            $packages = $this->model->filter($country, $days, $budget);
        }

        $countries = $this->model->getCountries();

        $html = $this->twig->render('packages/index.html.twig', [
            'base_path'        => $this->basePath,
            'app_lang'         => $_SESSION['lang'] ?? 'en',
            'packages'         => $packages,
            'countries'        => $countries,
            'filters'          => $params,
            'app_authenticated' => $_SESSION['authenticated'] ?? false,
            'app_user_name'    => $_SESSION['user_name'] ?? '',
            'app_role'         => $_SESSION['user_role'] ?? '',
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    //GET /packages/{id} — show a single package detail page
    public function show(Request $request, Response $response, array $args): Response
    {
        $package = $this->model->findById((int) $args['id']);

        //if package doesnt exist return a 404
        if (!$package) {
            $response->getBody()->write('Package not found');
            return $response->withStatus(404);
        }

        $html = $this->twig->render('packages/show.html.twig', [
            'base_path'         => $this->basePath,
            'app_lang'          => $_SESSION['lang'] ?? 'en',
            'package'           => $package,
            'app_authenticated' => $_SESSION['authenticated'] ?? false,
            'app_user_name'     => $_SESSION['user_name'] ?? '',
            'app_role'          => $_SESSION['user_role'] ?? '',
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    // Backend Admin Functions for managing packages (CRUD operations)
    // GET /packages/create — show the create package page
    public function create(Request $request, Response $response, array $args): Response {
        $destinations = $this->destinationModel->findAll();
        $hotels = $this->hotelModel->findAll();
        $guides = $this->guideModel->findAll();

        $html = $this->twig->render('admin/packages/create.html.twig', [
            'basePath'    => $this->basePath,
            'formAction'  => $this->basePath . '/admin/packages/store',
            'package'     => [],
            'app_lang'    => $_SESSION['lang'] ?? 'en',
            'destinations'=> $destinations,
            'hotels'      => $hotels,
            'guides'      => $guides,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    // POST /admin/packages/store — create a new package with form data
    public function store(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $this->model->create($data);
        $packageName = $data['package_name'] ?? 'Unknown Package';

        FlashHelper::add('success', "Package '$packageName' has been created successfully");
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }
    
    // GET /admin/packages/{id}/edit — show the edit package page
    public function edit(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];
        $package = $this->model->findById($id);
        
        if (!$package) { // Simplified check as findById now returns null if not found
            FlashHelper::add('danger', "Package ID: $id not found in the data records");
            return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
        }
        
        $destinations = $this->destinationModel->findAll();
        $hotels = $this->hotelModel->findAll();
        $guides = $this->guideModel->findAll();

        $html = $this->twig->render('admin/packages/edit.html.twig', [
            'basePath'    => $this-> basePath,
            'app_lang'    => $_SESSION['lang'] ?? 'en',
            'package'     => $package,
            'destinations'=> $destinations,
            'hotels'      => $hotels,
            'guides'      => $guides,
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    // GET /admin/packages/{id}/delete — delete a package
    public function destroy(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];
        $packageName = $this->model->findById($id)->title ?? 'Unknown Package';
        $this->model->delete($id);
        
        FlashHelper::add('success', "Package '$packageName' has been deleted successfully");
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }   

    // POST /admin/packages/{id}/update — update a package with form data
    public function update(Request $request, Response $response, array $args): Response {
        $id = (int) $args['id'];
        $data = $request->getParsedBody();
        
        $this->model->update($id, $data);
        $packageName = $this->model->findById($id)->title ?? 'Unknown Package';
        
        FlashHelper::add('success', "Package '$packageName' has been updated successfully");
        return $response->withHeader('Location', $this->basePath . '/admin')->withStatus(302);
    }
}
