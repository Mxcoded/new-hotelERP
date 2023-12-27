<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytic extends Model
{
    use HasFactory;
    // Relationship with property (if analytics are specific to individual properties)
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Optionally, relationship with user (staff analyzing or responsible for the data)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationships with specific data sources (like reservations, guest feedback) can be handled dynamically
    // based on the analytic focus, rather than through direct database relationships

    // Add other model properties/methods as needed
}
