# Traventa – Vacation Package Booking Website

**ECommerce — Winter 2026 | Vanier College**  
Teacher: Tiago Bortoletto Vaz

## Project Description
Traventa is a vacation package booking website that allows users to search destinations and choose travel packages based on their preferences. Each package includes a fixed price with details such as destination, duration, hotel option, and guided tours. On the admin side, he's going to manage package information, and will also be able to view all the bookings placed by the user.

## Team Members
- Jodel Santos
- Shayne Uzan
- Daveena Patel
- Deven Shah-Phan 

## Tech Stack
- **Language:** PHP 8+
- **Framework:** Slim 4
- **Database:** MariaDB
- **ORM:** RedBeanPHP
- **Templates:** Twig
- **CSS:** Bootstrap
- **Architecture:** MVC
- **Hosting:** Railway.app
- **2FA:** robthree/twofactorauth

## Local Setup
1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and fill in your database credentials
4. Create a MariaDB database named `traventa` in phpMyAdmin
5. Visit `http://localhost/traventa/setup.php` to create all the database tables automatically
6. Visit `http://localhost/traventa` to seed the database and launch the app

## Features
- User registration & login with 2FA
- Browse & filter vacation packages
- Favourites system
- Booking & checkout
- Booking history
- AI chatbot (Google AI Studio)
- Admin dashboard (CRUD for packages)
- Internationalization (EN / FR)
