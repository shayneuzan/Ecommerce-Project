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
        // $package->price_child = $package->price_child;
        // $package->min_age = $package->min_age; // "Assignment made to same variable; did you mean to assign to $package->min_age?"

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

    //create a new destination, hotel, guide and package all at once from the admin form when filling it in
    public function create(array $data): int
    {
        //create the destination first
        $destination = R::dispense('destination');
        $destination->city = trim($data['city'] ?? '');
        $destination->country = trim($data['country'] ?? '');
        $destination->description = trim($data['destination_description'] ?? '');
        R::store($destination);

        //create the hotel linked to that destination
        $hotel = R::dispense('hotel');
        $hotel->destination_id = $destination->id;
        $hotel->hotel_name = trim($data['hotel_name'] ?? '');
        $hotel->address = trim($data['hotel_address'] ?? '');
        $hotel->rating = isset($data['hotel_rating']) ? (int) $data['hotel_rating'] : 3;
        R::store($hotel);

        //create the guide linked to that destination
        $guide = R::dispense('guide');
        $guide->destination_id = $destination->id;
        $guide->guide_name = trim($data['guide_name'] ?? '');
        $guide->language = trim($data['guide_language'] ?? '');
        $guide->price = isset($data['guide_price']) ? (float) $data['guide_price'] : 0.0;
        R::store($guide);

        //create the package linking the destination, hotel and guide together
        $package = R::dispense('package');
        $package->destination_id = $destination->id;
        $package->hotel_id = $hotel->id;
        $package->guide_id = $guide->id;
        $package->title = trim($data['title'] ?? '');
        $package->description = trim($data['description'] ?? '');
        $package->duration_days = isset($data['duration_days']) ? (int) $data['duration_days'] : 0;
        $package->price = isset($data['price']) ? (float) $data['price'] : 0.0;
        $package->price_child = isset($data['price_child']) ? (float) $data['price_child'] : 0.0;
        $package->min_age = isset($data['min_age']) ? (int) $data['min_age'] : 0;
        $package->available_slots = isset($data['available_slots']) ? (int) $data['available_slots'] : 10;
        $package->image_url = trim($data['image_url'] ?? '');

        return R::store($package);
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

    // POST /admin/packages/{id}/update — update a package with form data
    public function update(int $id, array $data): bool
    {
        $selectedPackage = R::load('package', $id);
        if (!$selectedPackage->id) return false;

        $selectedPackage->title = $data['title'] ?? $selectedPackage->title;
        $selectedPackage->description = $data['description'] ?? $selectedPackage->description;
        $selectedPackage->duration_days = isset($data['duration_days']) ? (int) $data['duration_days'] : $selectedPackage->duration_days;
        $selectedPackage->price = isset($data['price']) ? (float) $data['price'] : $selectedPackage->price;
        $selectedPackage->price_child = isset($data['price_child']) ? (float) $data['price_child'] : $selectedPackage->price_child;
        $selectedPackage->min_age = isset($data['min_age']) ? (int) $data['min_age'] : $selectedPackage->min_age;
        $selectedPackage->available_slots = isset($data['available_slots']) ? (int) $data['available_slots'] : $selectedPackage->available_slots;
        $selectedPackage->image_url = $data['image_url'] ?? $selectedPackage->image_url;
        $selectedPackage->destination_id = isset($data['destination_id']) ? (int) $data['destination_id'] : $selectedPackage->destination_id;
        $selectedPackage->hotel_id = isset($data['hotel_id']) ? (int) $data['hotel_id'] : $selectedPackage->hotel_id;
        $selectedPackage->guide_id = isset($data['guide_id']) ? (int) $data['guide_id'] : $selectedPackage->guide_id;

        R::store($selectedPackage);
        return true;
    }

    // POST /admin/packages/{id}/delete — delete a package
    public function delete(int $id): bool
    {
        $selectedPackage = R::load('package', $id);
        if (!$selectedPackage->id) return false;

        R::trash($selectedPackage);
        return true;
    }
}