<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasFactory;

    protected $casts = [
        'applicable_services' => 'array', // Cast the JSON field to an array
        'is_global' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Define fillable properties to protect against mass-assignment
    protected $fillable = [
        'name', 'rate', 'type', 'applicable_services', 'is_global', 'is_active'
    ];

    // Relationship with reservations (if applying taxes to specific reservations)
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Relationship with folios (to apply taxes on billing)
    public function folios()
    {
        return $this->hasMany(Folio::class);
    }

    // Relationship with properties (if tax rates vary by property)
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    // Optionally, relationship with rates (if taxes are linked to specific rates)
    public function rates()
    {
        return $this->hasMany(Rate::class);
    }

    // Method to check if the tax is applicable to a given service
    public function isApplicableToService($serviceId)
    {
        // Check if the tax is global or if the service is in the applicable_services array
        return $this->is_global || in_array($serviceId, $this->applicable_services ?? []);
    }
    // public function isApplicableToService($serviceId)
    // {
    //     // If the tax is marked as global, it applies to all services
    //     if ($this->is_global) {
    //         return true;
    //     }

    //     // Ensure applicable_services is an array, even if it's null
    //     $applicableServices = $this->applicable_services ?? [];

    //     // Check if the service ID is in the applicable_services array
    //     return in_array($serviceId, $applicableServices);
    // }
    // Add other model properties/methods as needed
}
