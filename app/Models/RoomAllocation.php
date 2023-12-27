<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAllocation extends Model
{
    use HasFactory;

    // Relationship with room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship with reservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relationship with guest (if tracking room allocation to specific guests, especially in group bookings)
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    // Optionally, relationship with user (staff who allocated the room)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add other model properties/methods as needed
}
