<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'check_in_id', 'guest_id', 'room_id', 'check_out_time',
        'total_bill', 'amount_paid', 'outstanding_balance', 'payment_status',
        'notes', 'late_check_out',
    ];

    // Relationship with reservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relationship with check-in
    public function checkIn()
    {
        return $this->belongsTo(CheckIn::class, 'check_in_id');
    }

    // Direct relationship with the guest
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    // Direct relationship with the room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship with folios for billing
    public function folios()
    {
        return $this->hasManyThrough(Folio::class, Reservation::class);
    }

    // Additional methods or business logic as needed...
    // For example, methods to handle late check-out fees, final bill calculation, etc.
}
