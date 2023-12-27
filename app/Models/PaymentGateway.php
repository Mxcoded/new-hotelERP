<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;
    // Relationship with transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Relationship with reservations
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Relationship with properties
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    // Relationship with invoices (to track payments processed through this gateway)
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Add other model properties/methods as needed
}
