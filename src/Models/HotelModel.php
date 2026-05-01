<?php

declare(strict_types=1);

namespace App\Models;
use RedBeanPHP\R;

class HotelModel {
    public function findAll(): array
    {
        return R::findAll('hotel');
    }

    public function findById(int $id): mixed
    {
        $hotel = R::load('hotel', $id);
        return $hotel->id ? $hotel : null;
    }

    public function create(array $data): int
    {
        $hotel = R::dispense('hotel');
        $hotel->hotel_name = $data['hotel_name'] ?? '';
        $hotel->address = $data['address'] ?? '';
        $hotel->rating = isset($data['rating']) ? (float) $data['rating'] : 0.0;
        return R::store($hotel);
    }

    public function update(int $id, array $data): bool {
        $SelectedHotel = R::load('hotel', $id);
        if (!$SelectedHotel->id) return false;

        $SelectedHotel->hotel_name = $data['hotel_name'] ?? $SelectedHotel->hotel_name;
        $SelectedHotel->address = $data['address'] ?? $SelectedHotel->address;
        $SelectedHotel->rating = isset($data['rating']) ? (float) $data['rating'] : $SelectedHotel->rating;

        R::store($SelectedHotel);
        return true;
    }

    public function delete(int $id): bool {
        $SelectedHotel = R::load('hotel', $id);
        if (!$SelectedHotel->id) return false;

        R::trash($SelectedHotel);
        return true;
    }
}
