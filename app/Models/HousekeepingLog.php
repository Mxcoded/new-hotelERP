<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HousekeepingLog extends Model
{
    use HasFactory;

    // Relationship with room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship with property
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Optionally, relationship with user (housekeeping staff)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with maintenance requests (if applicable)
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // Add other model properties/methods as needed
}
