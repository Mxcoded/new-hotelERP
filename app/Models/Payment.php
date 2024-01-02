<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'folio_id',
        'guest_id',
        'property_id',
        'amount',
        'payment_method',
        'payment_date',
        'transaction_id',
        'status',
        'refund_amount',
        'notes',
        'processed_by',
    ];

    // Relationships

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function folio()
    {
        return $this->belongsTo(Folio::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Add additional methods as necessary

}
