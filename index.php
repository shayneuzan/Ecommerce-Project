<?php

declare(strict_types=1);

session_start();

use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use RedBeanPHP\R;
use App\Database;
use App\Controllers\AuthController;
use App\Controllers\DestinationController;
use App\Controllers\GuideController;
use App\Controllers\HotelController;
use App\Controllers\PackageController;
use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use App\Middleware\AdminMiddleware;
use App\Models\PackageModel;
use App\Models\DestinationModel;
use App\Models\GuideModel;
use App\Models\HotelModel;
use App\Services\OtpService;
use App\Services\FlashHelper;

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

// Make flash messages available in all Twig templates as 'flash'
$twig->addGlobal('flash', FlashHelper::get()); 

// ─── 4. DEPENDENCY INJECTION CONTAINER ───────────────────────────────────────
$basePath = $_ENV['APP_BASE_PATH'] ?? '/traventa';

$container = new \DI\Container();
$container->set(Environment::class, $twig);

 //register authcontroller 
$container->set(AuthController::class, fn() => new AuthController(
    $twig,
    new OtpService(),
    $basePath
));

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
$adminMiddleware = new AdminMiddleware($app->getResponseFactory(), $basePath);
$authMiddleware  = new AuthMiddleware($app->getResponseFactory(), $basePath); // not needed yet (will be used for favourites and bookings)

// ─── 7. ROUTES ────────────────────────────────────────────────────────────────
// Routes will be added here as each feature is built

// ─── 7. ROUTES ────────────────────────────────────────────────────────────────
$app->get('/', function ($request, $response) use ($twig, $basePath) {
    $packages = R::findAll('package', 'LIMIT 4'); //limit to 4 packages as starter

    //attach destination city to each package
    foreach ($packages as $package) {
        $destination = R::load('destination', $package->destination_id);
        $package->city = $destination->city;
    }

    $html = $twig->render('home.html.twig', [
        'base_path' => $basePath,
        'app_lang'  => $_SESSION['lang'] ?? 'en',
        'packages'  => $packages,
        'app_authenticated' => $_SESSION['authenticated'] ?? false,
        'app_user_name'    => $_SESSION['user_name'] ?? '',
        'app_role'         => $_SESSION['user_role'] ?? '',
    ]);

    $response->getBody()->write($html);
    return $response;
});

//packages listing and detail pages
$app->get('/packages',      [PackageController::class, 'index']);
$app->get('/packages/{id}', [PackageController::class, 'show']);

// Auth Routes
$app->group('/auth', function($group) {
    $group->get('/register', [AuthController::class, 'showRegister']);
    $group->post('/register', [AuthController::class, 'register']);
    $group->get('/login', [AuthController::class, 'showLogin']);
    $group->post('/login', [AuthController::class, 'login']);
    $group->get('/verify-2fa', [AuthController::class, 'showVerify']);
    $group->post('/verify-2fa', [AuthController::class, 'verify']);
    $group->post('/logout', [AuthController::class, 'logout']);
});

// Admin Routes — protected by AdminMiddleware so only admins can access
$app->group('/admin', function($group) {
    $group->get('', [AdminController::class, 'index']);

    // Admin Packages Routes
    $group->get('/packages/create', [PackageController::class, 'create']);
    $group->post('/packages/store', [PackageController::class, 'store']);
    $group->get('/packages/{id}/edit', [PackageController::class, 'edit']);
    $group->post('/packages/{id}/update', [PackageController::class, 'update']);
    $group->post('/packages/{id}/delete', [PackageController::class, 'destroy']);

    // Admin Destinations Routes
    $group->get('/destinations/create', [DestinationController::class, 'create']);
    $group->post('/destinations', [DestinationController::class, 'store']);
    $group->get('/destinations/{id}/edit', [DestinationController::class, 'edit']);
    $group->post('/destinations/{id}/update', [DestinationController::class, 'update']);
    $group->post('/destinations/{id}/delete', [DestinationController::class, 'destroy']);

    // Admin Hotels Routes
    $group->get('/hotels/create', [HotelController::class, 'create']);
    $group->post('/hotels', [HotelController::class, 'store']);
    $group->get('/hotels/{id}/edit', [HotelController::class, 'edit']);
    $group->post('/hotels/{id}/update', [HotelController::class, 'update']);
    $group->post('/hotels/{id}/delete', [HotelController::class, 'destroy']);

    // Admin Guides Routes
    $group->get('/guides/create', [GuideController::class, 'create']);
    $group->post('/guides', [GuideController::class, 'store']);
    $group->get('/guides/{id}/edit', [GuideController::class, 'edit']);
    $group->post('/guides/{id}/update', [GuideController::class, 'update']);
    $group->post('/guides/{id}/delete', [GuideController::class, 'destroy']);

})->add($adminMiddleware);

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
