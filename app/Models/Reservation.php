<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    //  use HasFactory;
    // Constants for reservation statuses
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CHECKED_IN = 'checked-in';
    const STATUS_NO_SHOW = 'no-show';
    const STATUS_CANCELED = 'canceled';
    protected $table = 'reservations';

    protected $fillable = [
        'guest_id',
        'room_id',
        'reservation_number',
        'reservation_date',
        'check_in_date',
        'check_out_date',
        'total_nights',
        'number_of_guests',
        'price', // Base room price
        'status',
        'payment_method',
        'payment_status',
        'amount_paid',
        'price_discount',
        'balance_amount',
        'special_requests',
        'cancellation_policy_id',
        'property_id',
        // Additional fields as needed
    ];

    // Existing relationships
    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function folio()
    {
        return $this->hasOne(Folio::class, 'reservation_id');
    }
    /**
     * Get the folios associated with the reservation.
     */
    public function folios()
    {
        return $this->hasMany(Folio::class);
    }

    public function cancellationPolicy()
    {
        return $this->belongsTo(CancellationPolicy::class, 'cancellation_policy_id');
    }

    public function guests()
    {
        return $this->belongsToMany(Guest::class, 'reservation_guest');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    public function checkOut()
    {
        return $this->hasOne(CheckOut::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // New relationships
    // Relationship with services (many-to-many)
    public function services()
    {
        return $this->belongsToMany(Service::class, 'reservation_service')
            ->withPivot('quantity', 'total_price')
            ->withTimestamps();
    }

    // Relationship with promotions or discounts (if applicable)
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'reservation_promotion')
            ->withPivot('discount_amount')
            ->withTimestamps();
    }

    // Additional methods as needed
    // Example: Calculate total price including services
    public function calculateTotalPrice()
    {
        // Implement logic to calculate total price
        // including room price, services, and any promotions or discounts
    }
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }


    /*
*   event listeners to capture and store the IP address
* @param Request $request
*@param return void()
*/
    protected static function booted()
    {
        static::created(function ($reservation) {
            $reservation->auditLogs()->create([
                'performed_by' => auth()->id(),
                'action' => 'created',
                'description' => 'Reservation created',
                'ip_address' => request()->ip(),
            ]);
        });

        static::updated(function ($reservation) {
            $reservation->auditLogs()->create([
                'performed_by' => auth()->id(),
                'action' => 'updated',
                'description' => 'Reservation updated',
                'ip_address' => request()->ip(),
            ]);
        });

        static::deleted(function ($reservation) {
            $reservation->auditLogs()->create([
                'performed_by' => auth()->id(),
                'action' => 'deleted',
                'description' => 'Reservation deleted',
                'ip_address' => request()->ip(),
            ]);
        });
    }


    // Add other model properties/methods as needed
}
