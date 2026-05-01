<?php

declare(strict_types=1);

namespace App\Models;
use RedBeanPHP\R;

class GuideModel {
    public function findAll(): array
    {
        return R::findAll('guide');
    }

    public function findById(int $id): mixed
    {
        $guide = R::load('guide', $id);
        return $guide->id ? $guide : null;
    }
    
    public function create(array $data): int
    {
        $guide = R::dispense('guide');
        $guide->destination_id = $data['destination_id'] ?? 0;
        $guide->guide_name = $data['guide_name'] ?? '';
        $guide->language = $data['language'] ?? '';
        $guide->price = isset($data['price']) ? (float) $data['price'] : 0.0;
        return R::store($guide);
    }

    public function update(int $id, array $data): bool
    {
        $selectedGuide = R::load('guide', $id);
        if (!$selectedGuide->id) return false;

        $selectedGuide->destination_id = $data['destination_id'] ?? $selectedGuide->destination_id;
        $selectedGuide->guide_name = $data['guide_name'] ?? $selectedGuide->guide_name;
        $selectedGuide->language = $data['language'] ?? $selectedGuide->language;
        $selectedGuide->price = isset($data['price']) ? (float) $data['price'] : $selectedGuide->price;

        R::store($selectedGuide);
        return true;
    }

    public function delete(int $id): bool
    {
        $selectedGuide = R::load('guide', $id);
        if (!$selectedGuide->id) return false;

        R::trash($selectedGuide);
        return true;
    }
}
