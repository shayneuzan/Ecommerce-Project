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
        $guide->title = $data['title'] ?? '';
        $guide->language = $data['language'] ?? '';
        $guide->price = isset($data['price']) ? (float) $data['price'] : 0.0;
        return R::store($guide);
    }

    public function update(int $id, array $data): bool
    {
        $SelectedGuide = R::load('guide', $id);
        if (!$SelectedGuide->id) return false;

        $SelectedGuide->title = $data['title'] ?? $SelectedGuide->title;
        $SelectedGuide->language = $data['language'] ?? $SelectedGuide->language;
        $SelectedGuide->price = isset($data['price']) ? (float) $data['price'] : $SelectedGuide->price;

        R::store($SelectedGuide);
        return true;
    }

    public function delete(int $id): bool
    {
        $SelectedGuide = R::load('guide', $id);
        if (!$SelectedGuide->id) return false;

        R::trash($SelectedGuide);
        return true;
    }
}
