<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

     // Relationship with reservations
     public function reservations()
     {
         return $this->hasMany(Reservation::class);
     }

     // Relationship with properties
     public function properties()
     {
         return $this->belongsToMany(Property::class);
     }

     // Relationship with allotments
     public function allotments()
     {
         return $this->hasMany(Allotment::class);
     }

     // Relationship with transactions (for tracking financial transactions related to the group)
     public function transactions()
     {
         return $this->hasManyThrough(Transaction::class, Reservation::class);
     }

     // Relationship with folios (for managing billing details for the group)
     public function folios()
     {
         return $this->hasManyThrough(Folio::class, Reservation::class);
     }

     // Optionally, relationship with guests
     public function guests()
     {
         return $this->hasManyThrough(Guest::class, Reservation::class);
     }

     // Add other model properties/methods as needed
}
