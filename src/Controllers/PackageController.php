<?php

//This class handles the packages listing page and single package detail page
//reads filters and search queries from the URL, asks the model for the right packages, 
// and passes them to the correct twig template 


declare(strict_types=1);

namespace App\Controllers;

use App\Models\PackageModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class PackageController
{
    public function __construct(
        private Environment $twig,
        private PackageModel $model,
        private string $basePath,
    ) {}

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
            'base_path' => $this->basePath,
            'app_lang'  => $_SESSION['lang'] ?? 'en',
            'packages'  => $packages,
            'countries' => $countries,
            'filters'   => $params,
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
            'base_path' => $this->basePath,
            'app_lang'  => $_SESSION['lang'] ?? 'en',
            'package'   => $package,
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}