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
        $SelectedDestination = R::load('destination', $id);
        if (!$SelectedDestination->id) return false;

        $SelectedDestination->city = $data['city'] ?? $SelectedDestination->city;
        $SelectedDestination->country = $data['country'] ?? $SelectedDestination->country;
        $SelectedDestination->description = $data['description'] ?? $SelectedDestination->description;

        R::store($SelectedDestination);
        return true;
    }

    public function delete(int $id): bool
    {
        $SelectedDestination = R::load('destination', $id);
        if (!$SelectedDestination->id) return false;

        R::trash($SelectedDestination);
        return true;
    }
}
