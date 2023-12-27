<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    // Relationship with the sender (user who sent the message)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relationship with the receiver (user who received the message)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // Optionally, relationship with property (if messages are related to specific properties)
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Add other model properties/methods as needed
}
