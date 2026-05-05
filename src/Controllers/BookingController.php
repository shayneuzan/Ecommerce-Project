<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BookingModel;
use App\Services\PricingService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;
use RedBeanPHP\R;

class BookingController
{

        private Environment $twig;
        private BookingModel $model;
        private PricingService $pricingService;
        private string $basePath;


    public function __construct(Environment $twig, BookingModel $model, PricingService $pricingService, string $basePath
    ) {
        $this->twig = $twig;
        $this->model = $model;
        $this->pricingService = $pricingService;
        $this->basePath = $basePath;
    } 

    public function showBooking(Request $request, Response $response, array $args): Response
    {
        $packageId = (int) $args['id'];
        $package = R::load('package', $packageId);

        if (!$package->id) {
            $response->getBody()->write('Package not found');
            return $response->withStatus(404);
        }

        $html = $this->twig->render('bookings/bookingPage.html.twig', [
            'basePath' => $this->basePath,
            'base_path' => $this->basePath,
            'package' => $package->export(),
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);
        $response->getBody()->write($html);
        return $response;
    }

    public function calculatePrice(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $basePrice = (float) ($data['base_price'] ?? 0);
        $travelDate = $data['travel_date'] ?? '';
        $passengers = $data['passengers'] ?? [];


        if(!$basePrice || !$travelDate || empty($passengers)) {
            $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $pricing = $this->pricingService->calculateTotal($basePrice, $travelDate, $passengers);

        $response->getBody()->write(json_encode($pricing));

        return $response->withHeader('Content-Type', 'application/json');
    }

    public function checkout(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        $packageId = (int) $args['id'];
        $basePrice = (float) ($data['base_price'] ?? 0);
        $travelDate = $data['travel_date'] ?? '';
        $passengers = $data['passengers'] ?? [];

        if(!$travelDate || !$packageId || empty($passengers)) {
            return $response->withHeader('Location', $this->basePath . '/packages/')->withStatus(302);
        }

        $pricing = $this->pricingService->calculateTotal($basePrice, $travelDate, $passengers);

        foreach($pricing['breakdown'] as $index => $passenger) {
                $passenger['first_name'] = $passengers[$index]['first_name'] ?? '';
                $passenger['last_name'] = $passengers[$index]['last_name'] ?? '';
        }

        $_SESSION['booking_data'] = [
            'package_id' => $packageId,
            'base_price' => $basePrice,
            'travel_date' => $travelDate,
            'passengers' => $pricing['breakdown'],
            'pricing' => $pricing
        ];

        $package = R::load('package', $packageId);

        $html = $this->twig->render('bookings/checkout.html.twig', [
            'basePath' => $this->basePath,
            'base_path' => $this->basePath,
            'package' => $package->export(),
            'travel_date' => $travelDate,
            'passengers' => $pricing['breakdown'],
            'pricing' => $pricing,
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);

        $response->getBody()->write($html);
        return $response;
    }

    public function payment(Request $request, Response $response): Response
    {
        $bookingData = $_SESSION['booking_data'] ?? null;

        if (!$bookingData) {
            return $response->withHeader('Location', $this->basePath . '/packages/')->withStatus(302);
        }

        // The login flow stores the authenticated user ID as $_SESSION['user_id'].
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/auth/login')->withStatus(302);
        }

        $bookingId = $this->model->create([
            'user_id' => $userId,
            'package_id' => $bookingData['package_id'],
            'travel_date' => $bookingData['travel_date'],
            'total_price' => $bookingData['pricing']['total']
        ]);

        foreach ($bookingData['passengers'] as $passenger) {
            $this->model->addPassengers($bookingId, [
                'first_name' => $passenger['first_name'] ?? '',
                'last_name' => $passenger['last_name'] ?? '',
                'age' => $passenger['age'] ?? 0,
                'passenger_type' => $passenger['type'] ?? 'adult',
                'price' => $passenger['price'] ?? 0.00
            ]);
        }

        $paymentSuccess = true;

        if($paymentSuccess) {
            $this->model->updatePaymentStatus($bookingId, 'paid');
            $this->model->updateStatus($bookingId, 'confirmed');

            unset($_SESSION['booking_data']);

            return $response->withHeader('Location', $this->basePath . '/booking/confirmation/' . $bookingId)->withStatus(302);
        } 

        $html = $this->twig->render('bookings/checkout.html.twig', [
            'basePath' => $this->basePath,
            'base_path' => $this->basePath,
            'error' => 'Payment failed. Please try again.',
            'breakdown' => $bookingData['passengers'],
            'passengers' => $bookingData['passengers'],
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);

        $response->getBody()->write($html);
        return $response;

    }

    public function confirmation(Request $request, Response $response, array $args): Response
    {
        $bookingId = (int) $args['id'];
        $booking = $this->model->findById($bookingId);

        if (!$booking || !$booking->id) {
            return $response->withHeader('Location', $this->basePath . '/packages/')->withStatus(302);
        }

        $passengers = $this->model->getPassengers($bookingId);
        $package = R::load('package', $booking->package_id);

        $html = $this->twig->render('bookings/confirmation.html.twig', [
            'basePath' => $this->basePath,
            'base_path' => $this->basePath,
            'booking' => $booking->export(),
            'passengers' => $passengers,
            'package' => $package->export(),
            'app_lang' => $_SESSION['lang'] ?? 'en'
        ]);

        $response->getBody()->write($html);
        return $response;
    }

}