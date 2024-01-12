<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folio extends Model {
    use HasFactory;
    protected $table = 'folios';

    // Fillable fields for a folio
    protected $fillable = [
        'reservation_id', 'check_in_id', 'guest_id', 'date_created',
        'total_charges', 'total_payments', 'balance', 'status'
    ];

    // If you have a table for tracking individual folio charges, you can define a relationship here

    public function charges() {
        return $this->hasMany( Folio_charges::class, 'folio_id' );
    }

    // Additional relationships or methods as needed

    // Relationship with reservation

    public function reservation() {
        return $this->belongsTo( Reservation::class );
    }

    // Relationship with guest

    public function guest() {
        return $this->belongsTo( Guest::class );
    }

    // Relationship with property

    public function property() {
        return $this->belongsTo( Property::class );
    }

    // Relationship with transactions

    public function transactions() {
        return $this->hasMany( Transaction::class );
    }

    public function checkIn() {
        return $this->belongsTo( CheckIn::class );
    }

    public function payments() {
        return $this->hasMany( Payment::class );
    }

    // Add other model properties/methods as needed
}
