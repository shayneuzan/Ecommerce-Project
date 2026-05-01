<?php

declare(strict_types=1);

namespace App\Controllers;

use Twig\Environment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RedBeanPHP\R;

class AdminController {
    private Environment $twig;
    private string $basePath;


    public function __construct(Environment $twig, string $basePath)
    {
        $this->twig = $twig;
        $this->basePath = $basePath;
    }

    // Admin Dashboard
    public function index(Request $request, Response $response): Response
    {
        $packages = R::findAll('package');
        $bookings = R::findAll('booking');
        $guides = R::findAll('guide');
        $hotels = R::findAll('hotel');
        $destinations = R::findAll('destination');
        $user = R::findOne('user', 'id = ?', [$_SESSION['user_id'] ?? 0]);

        // If user doesn't exist, make a fake one for display purposes (since we don't have user management yet)
        if (!$user) {
            $user = new \stdClass(); // Using stdClass since we just need a simple object for display purposes
            $user->first_name = 'Admin';
            $user->last_name = 'Anonymous';
        }

        // Optimized: Calculate sum directly in the database to avoid N+1 query performance issues
        $total_earnings = (float) R::getCell('SELECT SUM(package.price) FROM booking JOIN package ON booking.package_id = package.id');

        $html = $this->twig->render('admin/index.html.twig', [
            'basePath' => $this->basePath,
            'packages' => $packages,
            'bookings' => $bookings,
            'guides' => $guides,
            'destinations' => $destinations,
            'hotels' => $hotels,
            'total_earnings' => $total_earnings,
            'user' => $user,
            'app_lang' => $_SESSION['lang'] ?? 'en',
        ]);
        
        $response->getBody()->write($html);
        return $response;
    }
}
