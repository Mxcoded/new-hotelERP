<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;
    // Relationship with reservations (many-to-many for group bookings)
    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'gender',
        'occupation',
        'date_of_birth',
        'nationality',
        'vip',
        'contact_type',
        'email',
        'country_code',
        'mobile_number',
        'country',
        'state',
        'city',
        'zip_code',
        'address',
        'identity_type',
        'identity_id',
        'front_id_image',
        'back_id_image',
        'guest_image',
        'preferences',
        'loyalty_program_id',
        // Add other fields as required
    ];
    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_guest');
    }

    // Relationship with feedbacks left by the guest
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    // Relationship with guest preferences
    public function preferences()
    {
        return $this->hasOne(GuestPreference::class);
    }

    // Relationship with the loyalty program (if applicable)
    public function loyaltyProgram()
    {
        return $this->belongsTo(LoyaltyProgram::class);
    }

    // Relationship with folios (for billing and transaction records)
    public function folios()
    {
        return $this->hasMany(Folio::class);
    }

    // Add other model properties/methods as needed
}
