<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    // Relationship with user (staff who generated the report)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Optionally, relationship with properties (if reports are specific to properties)
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Relationship with specific data points (like reservations, transactions) can be handled dynamically
    // based on the type of report, rather than through direct database relationships

    // Add other model properties/methods as needed
}
