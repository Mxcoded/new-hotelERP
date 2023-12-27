<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

     // Relationship with guest
     public function guest()
     {
         return $this->belongsTo(Guest::class);
     }

     // Relationship with reservation
     public function reservation()
     {
         return $this->belongsTo(Reservation::class);
     }

     // Relationship with property
     public function property()
     {
         return $this->belongsTo(Property::class);
     }

     // Relationship with folios (to manage billing details for each guest or reservation)
     public function folios()
     {
         return $this->hasMany(Folio::class);
     }

     // Relationship with transactions
     public function transactions()
     {
         return $this->hasMany(Transaction::class);
     }

     // Add other model properties/methods as needed
}
