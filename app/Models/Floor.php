<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Floor extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'name',
        'start_room_number',
        'total_rooms',
        // Add other fillable fields as needed
    ];
    /**
     * Get the rooms associated with the floor.
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }
    /**
     * Get the property that the floor belongs to.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
