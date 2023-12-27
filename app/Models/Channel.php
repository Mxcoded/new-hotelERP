<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;
    // Relationship with properties (if channels are linked to specific properties)
    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    // Relationship with reservations (to track which reservations came through which channel)
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Optionally, relationship with rates (if specific rates are offered on different channels)
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    // Add other model properties/methods as needed
}
