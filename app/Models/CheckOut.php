<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckOut extends Model
{
    use HasFactory;
    // Relationship with reservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relationship with room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship with guest (through reservation)
    public function guest()
    {
        return $this->belongsToThrough(Guest::class, Reservation::class);
    }

    // Relationship with folios for billing
    public function folios()
    {
        return $this->hasManyThrough(Folio::class, Reservation::class);
    }

    // Add other model properties/methods as needed
}
