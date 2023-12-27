<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    //relationship with Amenities
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class)->withTimestamps();
    }
   //relationship with Facilities
    public function facilities()
    {
        return $this->hasMany(Facility::class);
    }
    // Relationship with rooms
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

     /**
     * Get the room types associated with the property.
     */
    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    // Relationship with users (e.g., staff assigned to a property)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // If properties have specific reservations
    public function reservations()
    {
        return $this->hasManyThrough(Reservation::class, Room::class);
    }

    protected $fillable = [
            'name',
            'address',
            'phone',
            'email' ,
            'description',
            'active',
    ];
    // Add other model properties/methods as needed
}
