<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'check_in_time',
        'check_in_date',
        'check_out_date',
        'notes',
        'guest_id',
        'room_id',
        'status',
        'checked_in_by',
        'expected_check_out_time',
        'room_key_card',
        'is_group_check_in',
        'additional_guests'
    ];

    // Casts for native types and JSON
    protected $casts = [
        'is_group_check_in' => 'boolean',
        'additional_guests' => 'array',
        'expected_check_out_time' => 'datetime'
    ];

    // Relationship with reservation (optional, may be null for direct check-ins)
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relationship with guest (directly related, not necessarily through Reservation)
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    // Relationship with the room assigned during check-in
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship with the staff member who performed the check-in
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    // Relationship with folios for billing (if directly linked to CheckIn)
    public function folios()
    {
        return $this->hasMany(Folio::class);
    }

    // Add other model properties/methods as needed
}
