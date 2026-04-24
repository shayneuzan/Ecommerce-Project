<?php

declare(strict_types=1);

namespace App;

use RedBeanPHP\R;

class Database
{
    public static function connect(): void
    {
        R::setup(
            'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS']
        );
        R::freeze(true); //do not change the database structure
    }


    public static function seed(): void
    {
        //avoid adding the same destination multiple time
        if (R::count('destination') > 0) return;

        // Destinations
        $paris = R::dispense('destination');
        $paris->city = 'Paris';
        $paris->country = 'France';
        $paris->description = 'The city of love, famous for the Eiffel Tower and world-class cuisine.';
        R::store($paris);

        $london = R::dispense('destination');
        $london->city = 'London';
        $london->country = 'United Kingdom';
        $london->description = 'A vibrant capital city with iconic landmarks and rich history.';
        R::store($london);

        $tokyo = R::dispense('destination');
        $tokyo->city = 'Tokyo';
        $tokyo->country = 'Japan';
        $tokyo->description = 'A fascinating blend of ultramodern and traditional culture.';
        R::store($tokyo);

        $rome = R::dispense('destination');
        $rome->city = 'Rome';
        $rome->country = 'Italy';
        $rome->description = 'The eternal city, home to the Colosseum, Vatican and incredible Italian food.';
        R::store($rome);

        $barcelona = R::dispense('destination');
        $barcelona->city = 'Barcelona';
        $barcelona->country = 'Spain';
        $barcelona->description = 'A lively coastal city known for Gaudi architecture, beaches and vibrant nightlife.';
        R::store($barcelona);

        $bali = R::dispense('destination');
        $bali->city = 'Bali';
        $bali->country = 'Indonesia';
        $bali->description = 'A tropical paradise with stunning temples, rice terraces and crystal clear beaches.';
        R::store($bali);

        $dubai = R::dispense('destination');
        $dubai->city = 'Dubai';
        $dubai->country = 'UAE';
        $dubai->description = 'A futuristic city in the desert with world record skyscrapers and luxury experiences.';
        R::store($dubai);

        $newyork = R::dispense('destination');
        $newyork->city = 'New York';
        $newyork->country = 'USA';
        $newyork->description = 'The city that never sleeps, packed with iconic landmarks, culture and entertainment.';
        R::store($newyork);

        // Hotels
        $hotel1 = R::dispense('hotel');
        $hotel1->destination_id = $paris->id;
        $hotel1->hotel_name = 'Le Grand Paris';
        $hotel1->address = '10 Rue de Rivoli, Paris';
        $hotel1->rating = 5;
        R::store($hotel1);

        $hotel2 = R::dispense('hotel');
        $hotel2->destination_id = $london->id;
        $hotel2->hotel_name = 'The London Crown';
        $hotel2->address = '22 Oxford Street, London';
        $hotel2->rating = 4;
        R::store($hotel2);

        $hotel3 = R::dispense('hotel');
        $hotel3->destination_id = $tokyo->id;
        $hotel3->hotel_name = 'Tokyo Sakura Inn';
        $hotel3->address = '5 Shinjuku Street, Tokyo';
        $hotel3->rating = 4;
        R::store($hotel3);

        $hotel4 = R::dispense('hotel');
        $hotel4->destination_id = $rome->id;
        $hotel4->hotel_name = 'Hotel Roma Imperiale';
        $hotel4->address = '3 Via Veneto, Rome';
        $hotel4->rating = 5;
        R::store($hotel4);

        $hotel5 = R::dispense('hotel');
        $hotel5->destination_id = $barcelona->id;
        $hotel5->hotel_name = 'Hotel Barcelona Sol';
        $hotel5->address = '18 Las Ramblas, Barcelona';
        $hotel5->rating = 4;
        R::store($hotel5);

        $hotel6 = R::dispense('hotel');
        $hotel6->destination_id = $bali->id;
        $hotel6->hotel_name = 'Bali Serenity Resort';
        $hotel6->address = '7 Ubud Rice Fields, Bali';
        $hotel6->rating = 5;
        R::store($hotel6);

        $hotel7 = R::dispense('hotel');
        $hotel7->destination_id = $dubai->id;
        $hotel7->hotel_name = 'Burj View Hotel';
        $hotel7->address = '1 Sheikh Zayed Road, Dubai';
        $hotel7->rating = 5;
        R::store($hotel7);

        $hotel8 = R::dispense('hotel');
        $hotel8->destination_id = $newyork->id;
        $hotel8->hotel_name = 'Manhattan Grand Hotel';
        $hotel8->address = '45 Times Square, New York';
        $hotel8->rating = 4;
        R::store($hotel8);

        // Guides
        $guide1 = R::dispense('guide');
        $guide1->destination_id = $paris->id;
        $guide1->guide_name = 'Sophie Martin';
        $guide1->language = 'English, French';
        $guide1->price = 120.00;
        R::store($guide1);

        $guide2 = R::dispense('guide');
        $guide2->destination_id = $london->id;
        $guide2->guide_name = 'James Wilson';
        $guide2->language = 'English';
        $guide2->price = 100.00;
        R::store($guide2);

        $guide3 = R::dispense('guide');
        $guide3->destination_id = $tokyo->id;
        $guide3->guide_name = 'Yuki Tanaka';
        $guide3->language = 'English, Japanese';
        $guide3->price = 110.00;
        R::store($guide3);

        $guide4 = R::dispense('guide');
        $guide4->destination_id = $rome->id;
        $guide4->guide_name = 'Marco Rossi';
        $guide4->language = 'English, Italian';
        $guide4->price = 115.00;
        R::store($guide4);

        $guide5 = R::dispense('guide');
        $guide5->destination_id = $barcelona->id;
        $guide5->guide_name = 'Carlos Fernandez';
        $guide5->language = 'English, Spanish';
        $guide5->price = 105.00;
        R::store($guide5);

        $guide6 = R::dispense('guide');
        $guide6->destination_id = $bali->id;
        $guide6->guide_name = 'Wayan Sari';
        $guide6->language = 'English, Balinese';
        $guide6->price = 90.00;
        R::store($guide6);

        $guide7 = R::dispense('guide');
        $guide7->destination_id = $dubai->id;
        $guide7->guide_name = 'Ahmed Al Rashid';
        $guide7->language = 'English, Arabic';
        $guide7->price = 130.00;
        R::store($guide7);

        $guide8 = R::dispense('guide');
        $guide8->destination_id = $newyork->id;
        $guide8->guide_name = 'Jessica Brown';
        $guide8->language = 'English';
        $guide8->price = 125.00;
        R::store($guide8);

        // Packages
        $pkg1 = R::dispense('package');
        $pkg1->destination_id = $paris->id;
        $pkg1->hotel_id = $hotel1->id;
        $pkg1->guide_id = $guide1->id;
        $pkg1->title = 'Paris Romantic Getaway';
        $pkg1->description = 'Experience the magic of Paris with a luxury stay and guided tour of the Eiffel Tower, Louvre and Montmartre.';
        $pkg1->duration_days = 5;
        $pkg1->price = 1299.00;
        $pkg1->available_slots = 10;
        $pkg1->image_url = 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800';
        R::store($pkg1);

        $pkg2 = R::dispense('package');
        $pkg2->destination_id = $london->id;
        $pkg2->hotel_id = $hotel2->id;
        $pkg2->guide_id = $guide2->id;
        $pkg2->title = 'London City Explorer';
        $pkg2->description = 'Discover London\'s iconic landmarks including Big Ben, Buckingham Palace and the Tower of London.';
        $pkg2->duration_days = 4;
        $pkg2->price = 999.00;
        $pkg2->available_slots = 15;
        $pkg2->image_url = 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=800';
        R::store($pkg2);

        $pkg3 = R::dispense('package');
        $pkg3->destination_id = $tokyo->id;
        $pkg3->hotel_id = $hotel3->id;
        $pkg3->guide_id = $guide3->id;
        $pkg3->title = 'Tokyo Cultural Adventure';
        $pkg3->description = 'Immerse yourself in Japanese culture with visits to ancient temples, sushi markets and Mount Fuji.';
        $pkg3->duration_days = 7;
        $pkg3->price = 1599.00;
        $pkg3->available_slots = 8;
        $pkg3->image_url = 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800';
        R::store($pkg3);

        $pkg4 = R::dispense('package');
        $pkg4->destination_id = $rome->id;
        $pkg4->hotel_id = $hotel4->id;
        $pkg4->guide_id = $guide4->id;
        $pkg4->title = 'Rome Ancient Wonders';
        $pkg4->description = 'Walk through history with visits to the Colosseum, Roman Forum, Vatican Museums and the Sistine Chapel.';
        $pkg4->duration_days = 6;
        $pkg4->price = 1199.00;
        $pkg4->available_slots = 12;
        $pkg4->image_url = 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800';
        R::store($pkg4);

        $pkg5 = R::dispense('package');
        $pkg5->destination_id = $barcelona->id;
        $pkg5->hotel_id = $hotel5->id;
        $pkg5->guide_id = $guide5->id;
        $pkg5->title = 'Barcelona Sun and Culture';
        $pkg5->description = 'Explore Gaudi\'s masterpieces, stroll Las Ramblas, relax on Barceloneta beach and enjoy tapas tours.';
        $pkg5->duration_days = 5;
        $pkg5->price = 1099.00;
        $pkg5->available_slots = 12;
        $pkg5->image_url = 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800';
        R::store($pkg5);

        $pkg6 = R::dispense('package');
        $pkg6->destination_id = $bali->id;
        $pkg6->hotel_id = $hotel6->id;
        $pkg6->guide_id = $guide6->id;
        $pkg6->title = 'Bali Tropical Escape';
        $pkg6->description = 'Unwind in paradise with rice terrace treks, temple visits, traditional Balinese spa treatments and sunset dinners.';
        $pkg6->duration_days = 8;
        $pkg6->price = 1399.00;
        $pkg6->available_slots = 10;
        $pkg6->image_url = 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800';
        R::store($pkg6);

        $pkg7 = R::dispense('package');
        $pkg7->destination_id = $dubai->id;
        $pkg7->hotel_id = $hotel7->id;
        $pkg7->guide_id = $guide7->id;
        $pkg7->title = 'Dubai Luxury Experience';
        $pkg7->description = 'Live like royalty with a Burj Khalifa visit, desert safari, luxury mall tours and a dhow cruise dinner.';
        $pkg7->duration_days = 5;
        $pkg7->price = 1899.00;
        $pkg7->available_slots = 8;
        $pkg7->image_url = 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800';
        R::store($pkg7);

        $pkg8 = R::dispense('package');
        $pkg8->destination_id = $newyork->id;
        $pkg8->hotel_id = $hotel8->id;
        $pkg8->guide_id = $guide8->id;
        $pkg8->title = 'New York City Highlights';
        $pkg8->description = 'Experience the best of NYC with visits to Times Square, Central Park, the Statue of Liberty and Broadway.';
        $pkg8->duration_days = 6;
        $pkg8->price = 1799.00;
        $pkg8->available_slots = 10;
        $pkg8->image_url = 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800';
        R::store($pkg8);
    }
}