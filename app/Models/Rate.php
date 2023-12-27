<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    // Relationship with room type
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    // Relationship with property
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Optionally, relationship with promotions or discounts
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    // If rates are associated with specific reservations
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Add other model properties/methods as needed
}
