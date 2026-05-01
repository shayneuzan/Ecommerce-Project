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
        // $package->min_age = $package->min_age;

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

    //create a new package record from admin form data
    public function create(array $data): int
    {
        $package = R::dispense('package');
        $package->title = $data['title'] ?? '';
        $package->description = $data['description'] ?? '';
        $package->duration_days = isset($data['duration_days']) ? (int) $data['duration_days'] : 0;
        $package->price = isset($data['price']) ? (float) $data['price'] : 0.0;
        $package->price_child = isset($data['price_child']) ? (float) $data['price_child'] : 0.0;
        $package->min_age = isset($data['min_age']) ? (int) $data['min_age'] : 0;
        $package->available_slots = isset($data['available_slots']) ? (int) $data['available_slots'] : 0;
        $package->image_url = $data['image_url'] ?? '';
        $package->destination_id = isset($data['destination_id']) ? (int) $data['destination_id'] : 0;
        $package->hotel_id = isset($data['hotel_id']) ? (int) $data['hotel_id'] : 0;
        $package->guide_id = isset($data['guide_id']) ? (int) $data['guide_id'] : 0;

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
        $SelectedPackage = R::load('package', $id);
        if (!$SelectedPackage->id) return false;

        $SelectedPackage->title = $data['title'] ?? $SelectedPackage->title;
        $SelectedPackage->description = $data['description'] ?? $SelectedPackage->description;
        $SelectedPackage->duration_days = isset($data['duration_days']) ? (int) $data['duration_days'] : $SelectedPackage->duration_days;
        $SelectedPackage->price = isset($data['price']) ? (float) $data['price'] : $SelectedPackage->price;
        $SelectedPackage->price_child = isset($data['price_child']) ? (float) $data['price_child'] : $SelectedPackage->price_child;
        $SelectedPackage->min_age = isset($data['min_age']) ? (int) $data['min_age'] : $SelectedPackage->min_age;
        $SelectedPackage->available_slots = isset($data['available_slots']) ? (int) $data['available_slots'] : $SelectedPackage->available_slots;
        $SelectedPackage->image_url = $data['image_url'] ?? $SelectedPackage->image_url;
        $SelectedPackage->destination_id = isset($data['destination_id']) ? (int) $data['destination_id'] : $SelectedPackage->destination_id;
        $SelectedPackage->hotel_id = isset($data['hotel_id']) ? (int) $data['hotel_id'] : $SelectedPackage->hotel_id;
        $SelectedPackage->guide_id = isset($data['guide_id']) ? (int) $data['guide_id'] : $SelectedPackage->guide_id;

        R::store($SelectedPackage);
        return true;
    }

    // POST /admin/packages/{id}/delete — delete a package
    public function delete(int $id): bool
    {
        $SelectedPackage = R::load('package', $id);
        if (!$SelectedPackage->id) return false;

        R::trash($SelectedPackage);
        return true;
    }
}