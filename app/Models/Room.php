<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id', 'property_id', 'floor_id', 'number',
        'description', 'is_available'
        // Include additional attributes here as needed
    ];

    // Define relationships
    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    // // Relationship with property
    // public function property()
    // {
    //     return $this->belongsTo(Property::class);
    // }

    // public function floor()
    // {
    //     return $this->belongsTo(Floor::class);
    // }
    // // Relationship with room type
    // public function roomType()
    // {
    //     return $this->belongsTo(RoomType::class);
    // }

    // Relationship with reservations
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // If the room has housekeeping logs
    public function housekeepingLogs()
    {
        return $this->hasMany(HousekeepingLog::class);
    }

    // If the room has maintenance requests
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // Add other model properties/methods as needed
}
