<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id', 'property_id', 'floor_id', 'number',
        'description', 'status', 'base_price', 'features'
    ];

    protected $casts = [
        'features' => 'array',
        // other casts
    ];

    // Relationships
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

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function housekeepingLogs()
    {
        return $this->hasMany(HousekeepingLog::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // Add other model methods as needed
}
