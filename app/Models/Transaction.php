<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
     // Relationship with invoice
     public function invoice()
     {
         return $this->belongsTo(Invoice::class);
     }

     // Relationship with guest
     public function guest()
     {
         return $this->belongsTo(Guest::class);
     }

     // Relationship with folio
     public function folio()
     {
         return $this->belongsTo(Folio::class);
     }

     // Relationship with property
     public function property()
     {
         return $this->belongsTo(Property::class);
     }

     // Relationship with room
     public function room()
     {
         return $this->belongsTo(Room::class);
     }

     // Relationship with room type
     public function roomType()
     {
         return $this->belongsTo(RoomType::class);
     }

     // Optionally, relationship with user (staff who processed the transaction)
     public function user()
     {
         return $this->belongsTo(User::class);
     }

     // Add other model properties/methods as needed
}
