<?php

namespace App\Services;

class PricingService
{
    public function highSeason(string $travelDate): bool
    {
        $Date = new \DateTime($travelDate);
        if ($Date->format('m') == 12 || $Date->format('m') == 1 || $Date->format('m') == 2 || $Date->format('m') == 6 || $Date->format('m') == 7 || $Date->format('m') == 8) {
            return true;
        } 
        return false;
    }

    public function calculateTotal(float $basePrice, string $travelDate, array $passengers): array
    {
        if ($this->highSeason($travelDate)) {
            $basePrice *= 1.2; // 20% increase in high season
        } else {
            $basePrice *= 0.9; // 10% discount in low season
        }

        $breackdown = [];
        $subtotal = 0.00;

        foreach ($passengers as $passenger) {
            $age = $passenger['age'];


            if($age <=2) {
                $type = 'infant';
                $price = 0; // infants travel free
            } elseif ($age <= 12) {
                $type = 'child';
                $price = $basePrice * 0.8; // 20% discount for children
            } elseif ($age >= 60) {
                $type = 'senior';
                $price = $basePrice * 0.8; // 20% discount for seniors
            } else {
                $type = 'adult';
                $price = $basePrice; // full price for adults
            }
            $breackdown[] = [
                // 'name' => $passenger['name'],
                'age' => $age,
                'type' => $type,
                'price' => round($price, 2)
            ];

            $subtotal += $price;
        }

        $totalPrice = $subtotal * 1.15; // add 15% tax

        return [
            'breakdown' => $breackdown,
            'subtotal' => round($subtotal, 2),
            'total' => round($totalPrice, 2)
        ];
    }
}