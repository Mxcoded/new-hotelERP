<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
     // Relationship with guest (who provided the feedback)
     public function guest()
     {
         return $this->belongsTo(Guest::class);
     }

     // Relationship with reservation (associated with the feedback)
     public function reservation()
     {
         return $this->belongsTo(Reservation::class);
     }

     // Relationship with property (if feedback is specific to a property)
     public function property()
     {
         return $this->belongsTo(Property::class);
     }

     // Optionally, relationship with user (staff member who addressed the feedback)
     public function user()
     {
         return $this->belongsTo(User::class);
     }

     // Add other model properties/methods as needed
}
