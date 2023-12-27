<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateType extends Model
{
    use HasFactory;
    // Relationship with rates
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    // Relationship with promotions
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

    // Optional: Relationship with users
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Other direct relationships with Guest, Transaction, and Folio are less common
    // and might be indirectly managed through other models like Reservation or Room

    // Add other model properties/methods as needed
}
