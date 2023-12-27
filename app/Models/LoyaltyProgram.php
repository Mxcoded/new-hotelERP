<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    use HasFactory;
     // Relationship with guests (who are members of the loyalty program)
     public function guests()
     {
         return $this->hasMany(Guest::class);
     }

     // Relationship with promotions (special offers available to loyalty program members)
     public function promotions()
     {
         return $this->hasMany(Promotion::class);
     }

     // Optionally, relationship with properties (if loyalty benefits vary by property)
     public function properties()
     {
         return $this->hasMany(Property::class);
     }

     // Add other model properties/methods as needed


}
