<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'price', 'property_id'];

    // Relation to Property
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Add other relationships or custom methods as needed
}
