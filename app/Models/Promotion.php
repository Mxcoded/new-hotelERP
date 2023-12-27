<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    // Relationship with room types
    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class);
    }

    // Relationship with properties
    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    // Relationship with reservations
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Relationship with rates
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    // Relationship with guests (to track which guests have used the promotion)
    public function guests()
    {
        return $this->belongsToMany(Guest::class);
    }

    // Relationship with invoices (if promotions are reflected in billing)
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Relationship with users (if tracking which staff member created or managed the promotion)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Add other model properties/methods as needed
}
