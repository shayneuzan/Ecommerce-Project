<?php

declare(strict_types=1);

session_start();

use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use RedBeanPHP\R;
use App\Database;
use App\Controllers\DestinationController;
use App\Controllers\GuideController;
use App\Controllers\HotelController;
use App\Controllers\PackageController;
use App\Controllers\AdminController;
use App\Models\PackageModel;
use App\Models\DestinationModel;
use App\Models\GuideModel;
use App\Models\HotelModel;

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
$container->set(PackageController::class, fn(\DI\Container $c) => new PackageController(
    $twig,
    new PackageModel(),
    $basePath,
    new HotelModel(),
    new GuideModel(),
    new DestinationModel()
));

//register the admin controller with its dependencies
$container->set(AdminController::class, fn() => new AdminController(
    $twig,
    $basePath
));

//register the destination controller with its dependencies
$container->set(DestinationController::class, fn() => new DestinationController(
    $twig,
    new DestinationModel(),
    $basePath
));

//register the guide controller with its dependencies
$container->set(GuideController::class, fn() => new GuideController(
    $twig,
    new GuideModel(),
    $basePath
));

//register the hotel controller with its dependencies
$container->set(HotelController::class, fn() => new HotelController(
    $twig,
    new HotelModel(),
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

// Admin Routes
$app->get('/admin', [AdminController::class, 'index']);

// Admin Packages Routes
$app->get('/admin/packages/create', [PackageController::class, 'create']);
$app->post('/admin/packages/store', [PackageController::class, 'store']);
$app->get('/admin/packages/{id}/edit', [PackageController::class, 'edit']);
$app->post('/admin/packages/{id}/update', [PackageController::class, 'update']);
$app->post('/admin/packages/{id}/delete', [PackageController::class, 'destroy']);

// Admin Destinations Routes
$app->get('/admin/destinations/create', [DestinationController::class, 'create']);
$app->post('/admin/destinations', [DestinationController::class, 'store']);
$app->get('/admin/destinations/{id}/edit', [DestinationController::class, 'edit']);
$app->post('/admin/destinations/{id}/update', [DestinationController::class, 'update']);
$app->post('/admin/destinations/{id}/delete', [DestinationController::class, 'destroy']);

// Admin Hotels Routes
$app->get('/admin/hotels/create', [HotelController::class, 'create']);
$app->post('/admin/hotels', [HotelController::class, 'store']);
$app->get('/admin/hotels/{id}/edit', [HotelController::class, 'edit']);
$app->post('/admin/hotels/{id}/update', [HotelController::class, 'update']);
$app->post('/admin/hotels/{id}/delete', [HotelController::class, 'destroy']);

// Admin Guides Routes
$app->get('/admin/guides/create', [GuideController::class, 'create']);
$app->post('/admin/guides', [GuideController::class, 'store']);
$app->get('/admin/guides/{id}/edit', [GuideController::class, 'edit']);
$app->post('/admin/guides/{id}/update', [GuideController::class, 'update']);    
$app->post('/admin/guides/{id}/delete', [GuideController::class, 'destroy']);

//API endpoint for AJAX live search
$app->get('/api/packages/search', function ($request, $response) {
    $params   = $request->getQueryParams();
    $query    = trim($params['q'] ?? '');
    $model    = new PackageModel();
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
