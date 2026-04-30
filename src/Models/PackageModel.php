<?php

//This class handles all database queries related to packages
//fetches packages, searches by title, filters by country, days 
// and budget, and loads single package details with hotel, guide and destination info attached

declare(strict_types=1);

namespace App\Models;

use RedBeanPHP\R;

class PackageModel
{
    //get all packages with their city name attached
    public function findAll(): array
    {
        $packages = R::findAll('package');
        return $this->attachDestinations($packages);
    }

    //get a single package by ID with all its hotel, guide and destination details
    public function findById(int $id): mixed
    {
        $package = R::load('package', $id);
        if (!$package->id) return null;

        $destination = R::load('destination', $package->destination_id);
        $hotel = R::load('hotel', $package->hotel_id);
        $guide = R::load('guide', $package->guide_id);

        $package->city = $destination->city;
        $package->country = $destination->country;
        $package->destination_description = $destination->description;
        $package->hotel_name = $hotel->hotel_name;
        $package->hotel_address = $hotel->address;
        $package->hotel_rating = $hotel->rating;
        $package->guide_name = $guide->guide_name;
        $package->guide_language = $guide->language;
        $package->guide_price = $guide->price;
        $package->price_child = $package->price_child;
        $package->min_age = $package->min_age;

        return $package;
    }

    //search packages by title matching what the user typed
    public function search(string $query): array
    {
        $packages = R::findAll('package', 'WHERE title LIKE ?', ["%$query%"]);
        return $this->attachDestinations($packages);
    }

    //filter packages by country, max days and max budget
    public function filter(?string $country, ?int $days, ?float $budget): array
    {
        $conditions = [];
        $params = [];

        //only add each condition if the filter was actually set by the user
        if ($country) {
            $conditions[] = 'destination_id IN (SELECT id FROM destination WHERE country = ?)';
            $params[] = $country;
        }

        if ($days) {
            $conditions[] = 'duration_days <= ?';
            $params[] = $days;
        }

        if ($budget) {
            $conditions[] = 'price <= ?';
            $params[] = $budget;
        }

        //if no filters are set just return everything
        $sql = !empty($conditions) ? implode(' AND ', $conditions) : '1';
        $packages = R::findAll('package', 'WHERE ' . $sql, $params);
        return $this->attachDestinations($packages);
    }

    //get all destinations for the country dropdown in the filter sidebar
    public function getCountries(): array
    {
        return R::findAll('destination');
    }

    //attach the city and country from the destination table to each package
    private function attachDestinations(array $packages): array
    {
        foreach ($packages as $package) {
            $destination = R::load('destination', $package->destination_id);
            $package->city = $destination->city;
            $package->country = $destination->country;
        }
        return $packages;
    }
}