<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
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

     // Optionally, relationship with user (staff who reported or resolved the issue)
     public function user()
     {
         return $this->belongsTo(User::class);
     }

     // Relationship with housekeeping logs (if they are used to report maintenance issues)
     public function housekeepingLogs()
     {
         return $this->hasMany(HousekeepingLog::class);
     }

     // Add other model properties/methods as needed
}
