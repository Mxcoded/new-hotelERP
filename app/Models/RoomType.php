<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;
    // Relationship with rooms
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    // Relationship with rates
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    // Optionally, if room types have specific promotions or discounts
    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }
    protected $fillable = [
        'property_id',
        'name',
        'description',
        'base_price',
        'extra_person_charge',
        'extra_bed_charge',
        'max_occupancy',
        'bed_type',
        'is_accessible',
        'amenities',
        'size_type',
        'view'

    ];
    // Add other model properties/methods as needed

}
