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
        'notes',
        'guest_id',
        'room_id',
        'status',
        'checked_in_by'
    ];

    // Relationship with reservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relationship with guest
    // Updated to directly relate to Guest instead of through Reservation
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    // Relationship with room
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // Relationship with folios for billing
    // Assuming folio is directly linked to CheckIn
    public function folios()
    {
        return $this->hasMany(Folio::class);
    }

    // Relationship with the staff member who performed the check-in
    // Assuming User model represents staff
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    // Add other model properties/methods as needed
};
