<?php

declare(strict_types=1);

namespace App\Models;
use RedBeanPHP\R;

class DestinationModel {
    public function findAll(): array
    {
        return R::findAll('destination');
    }

    public function findById(int $id): mixed
    {
        $destination = R::load('destination', $id);
        return $destination->id ? $destination : null;
    }

    public function create(array $data): int
    {
        $destination = R::dispense('destination');
        // Use null coalescing to prevent "Undefined array key" errors
        $destination->city = $data['city'] ?? '';
        $destination->country = $data['country'] ?? '';
        $destination->description = $data['description'] ?? '';
        return R::store($destination);
    }

    public function update(int $id, array $data): bool
    {
        $selectedDestination = R::load('destination', $id);
        if (!$selectedDestination->id) return false;

        $selectedDestination->city = $data['city'] ?? $selectedDestination->city;
        $selectedDestination->country = $data['country'] ?? $selectedDestination->country;
        $selectedDestination->description = $data['description'] ?? $selectedDestination->description;

        R::store($selectedDestination);
        return true;
    }

    public function delete(int $id): bool
    {
        $selectedDestination = R::load('destination', $id);
        if (!$selectedDestination->id) return false;

        R::trash($selectedDestination);
        return true;
    }
}
