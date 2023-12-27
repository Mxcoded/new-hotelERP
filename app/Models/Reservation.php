<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';

    protected $fillable = [
        'guest_id',
        'room_id',
        'reservation_date',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'price',
        'status',
        'payment_method',
        'payment_status',
        'amount_paid',
        'balance_amount',
        'special_requests',
        'cancellation_policy_id',
        'property_id'
        // 'folio_id'
    ];


    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }
    // Relationship with room
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    // Relationship with folios (billing information)
    public function folio()
    {
        return $this->hasOne(Folio::class, 'reservation_id');
    }

    public function cancellationPolicy()
    {
        return $this->belongsTo(CancellationPolicy::class, 'cancellation_policy_id');
    }

    // Relationship with guests (many-to-many for group bookings)
    public function guests()
    {
        return $this->belongsToMany(Guest::class, 'reservation_guest');
    }

    // Relationship with user (if tracking which user made the reservation)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationships for check-in and check-out (if tracked as separate entities)
    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    public function checkOut()
    {
        return $this->hasOne(CheckOut::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
    // Add other model properties/methods as needed
}
