<?php

declare(strict_types=1);

session_start();

use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use RedBeanPHP\R;
use App\Database;
use App\Controllers\PackageController;
use App\Models\PackageModel;

require __DIR__ . '/vendor/autoload.php';

// ─── 1. ENVIRONMENT ───────────────────────────────────────────────────────────
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// ─── 2. DATABASE ──────────────────────────────────────────────────────────────
// RedBeanPHP setup goes here (MariaDB connection using .env values)
// Will be configured later 

App\Database::connect();
App\Database::seed();


// ─── 3. TEMPLATE ENGINE ───────────────────────────────────────────────────────
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig   = new Environment($loader, [
    'cache'       => false,
    'auto_reload' => true,
]);

// ─── 4. DEPENDENCY INJECTION CONTAINER ───────────────────────────────────────
$basePath = $_ENV['APP_BASE_PATH'] ?? '/traventa';

$container = new \DI\Container();
$container->set(Environment::class, $twig);

//register the package controller with its dependencies
$container->set(PackageController::class, fn() => new PackageController(
    $twig,
    new PackageModel(),
    $basePath
));

// Controllers will be registered here as the project grows

// ─── 5. APPLICATION ───────────────────────────────────────────────────────────
AppFactory::setContainer($container);
$app = AppFactory::create();

// Set the base path so Slim strips the sub-directory prefix from all routes.
$app->setBasePath($basePath);

// Parse incoming form data (needed for POST requests)
$app->addBodyParsingMiddleware();

// Enable the Slim routing middleware (required for route matching)
$app->addRoutingMiddleware();

// Register the error middleware (shows errors in development)
$app->addErrorMiddleware(true, true, true);


// ─── 6. MIDDLEWARE ────────────────────────────────────────────────────────────
// Auth and admin middleware will be registered here

// ─── 7. ROUTES ────────────────────────────────────────────────────────────────
// Routes will be added here as each feature is built

// ─── 7. ROUTES ────────────────────────────────────────────────────────────────
$app->get('/', function ($request, $response) use ($twig, $basePath) {
    $packages = \RedBeanPHP\R::findAll('package', 'LIMIT 4'); //limit to 4 packages as starter

    //attach destination city to each package
    foreach ($packages as $package) {
        $destination = \RedBeanPHP\R::load('destination', $package->destination_id);
        $package->city = $destination->city;
    }

    $html = $twig->render('home.html.twig', [
        'base_path' => $basePath,
        'app_lang'  => $_SESSION['lang'] ?? 'en',
        'packages'  => $packages,
    ]);

    $response->getBody()->write($html);
    return $response;
});

//packages listing and detail pages
$app->get('/packages',      [PackageController::class, 'index']);
$app->get('/packages/{id}', [PackageController::class, 'show']);

//API endpoint for AJAX live search
$app->get('/api/packages/search', function ($request, $response) {
    $params   = $request->getQueryParams();
    $query    = trim($params['q'] ?? '');
    $model    = new \App\Models\PackageModel();
    $packages = $query ? $model->search($query) : $model->findAll();

    $payload = array_map(fn($p) => [
        'id'              => (int) $p->id,
        'title'           => $p->title,
        'description'     => $p->description,
        'price'           => $p->price,
        'duration_days'   => (int) $p->duration_days,
        'available_slots' => (int) $p->available_slots,
        'image_url'       => $p->image_url,
        'city'            => $p->city,
    ], array_values($packages));

    $response->getBody()->write(json_encode($payload, JSON_THROW_ON_ERROR));
    return $response->withHeader('Content-Type', 'application/json');
});

// ─── 8. RUN ───────────────────────────────────────────────────────────────────
$app->run();
