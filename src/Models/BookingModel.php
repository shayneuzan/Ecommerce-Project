<?php

declare(strict_types=1);

namespace App\Models;
use RedBeanPHP\R;

class BookingModel {
    public function findAll(): array
    {
        return R::findAll('booking');
    }

    public function findById(int $id): mixed
    {
        $booking = R::load('booking', $id);
        return $booking->id ? $booking : null;
    }

    public function create(array $data): int
    {
        $booking = R::dispense('booking');
        $booking->user_id = $data['user_id'] ?? 0;
        $booking->package_id = $data['package_id'] ?? 0;
        $booking->reference = $this->generateReference();
        $booking->travel_date = $data['travel_date'] ?? '';
        $booking->total_price = $data['total_price'] ?? 0.00;
        $booking->status = 'pending'; // default status
        $booking->payment_status = 'unpaid'; // default payment status
        $booking->created_at = date('Y-m-d H:i:s');
        return (int) R::store($booking); 
    }

    public function delete(int $id): bool
    {
        $SelectedBooking = R::load('booking', $id);
        if (!$SelectedBooking->id) return false;

        R::trash($SelectedBooking);
        return true;
    }

    private function generateReference(): string
    {
        $date = Date('Ymd');
        $uniquePart = strtoupper(bin2hex(random_bytes(4)));
        return "TRV-$date-$uniquePart";
    }

    public function addPassengers(int $bookingId, array $passengers): int
    {
        $passenger = R::dispense('bookingpassenger');
        $passenger->booking_id = $bookingId;
        $passenger->first_name = $passengers['first_name'] ?? '';
        $passenger->last_name = $passengers['last_name'] ?? '';
        $passenger->age = $passengers['age'] ?? 0;
        $passenger->passenger_type = $passengers['passenger_type'] ?? 'adult'; // default to adult if not specified
        $passenger->price = $passengers['price'] ?? 0.00;
        return (int) R::store($passenger);
    }

    public function getPassengers(int $bookingId): array
    {
        return R::find('bookingpassenger', 'booking_id = ?', [$bookingId]);
    }

    public function updateStatus(int $bookingId, string $status): bool
    {
        $SelectedBooking = R::load('booking', $bookingId);
        if (!$SelectedBooking->id) return false;

        $SelectedBooking->status = $status;
        R::store($SelectedBooking);
        return true;
    }

    public function updatePaymentStatus(int $bookingId, string $paymentStatus): bool
    {
        $SelectedBooking = R::load('booking', $bookingId);
        if (!$SelectedBooking->id) return false;

        $SelectedBooking->payment_status = $paymentStatus;
        R::store($SelectedBooking);
        return true;
    }
}