<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestPreference extends Model
{
    use HasFactory;
    // Relationship with guest
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    // Relationship with room (if preferences are for specific rooms)
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship with room type (if preferences are for types of rooms)
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    // Relationship with reservations
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Relationship with property
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Add other model properties/methods as needed
}
