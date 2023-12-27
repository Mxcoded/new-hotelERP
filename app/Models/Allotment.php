<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allotment extends Model
{
    use HasFactory;

    // Relationship with group (which the allotment is for)
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Relationship with property (where the allotment is located)
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Relationship with room type (type of rooms in the allotment)
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    // Optionally, relationship with reservations (specific reservations made from this allotment)
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Add other model properties/methods as needed
}
