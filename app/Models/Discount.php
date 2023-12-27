<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
     // Relationship with reservations
     public function reservations()
     {
         return $this->hasMany(Reservation::class);
     }

     // Relationship with folios
     public function folios()
     {
         return $this->hasMany(Folio::class);
     }

     // Relationship with guests
     public function guests()
     {
         return $this->hasMany(Guest::class);
     }

     // Relationship with room types
     public function roomTypes()
     {
         return $this->hasMany(RoomType::class);
     }

     // Relationship with properties
     public function properties()
     {
         return $this->hasMany(Property::class);
     }

     // Relationship with users (staff who manage or create discounts)
     public function users()
     {
         return $this->belongsToMany(User::class);
     }

     // Add other model properties/methods as needed
}
