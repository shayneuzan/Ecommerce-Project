<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use RedBeanPHP\R;
use App\Database;

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

$app->get('/', function ($request, $response) {
    $response->getBody()->write('<h1>Traventa is running!</h1>');
    return $response;
});

// ─── 8. RUN ───────────────────────────────────────────────────────────────────
$app->run();
