<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancellationPolicy extends Model
{
    use HasFactory;
    protected $table = 'cancellation_policies';

    protected $fillable = ['name', 'description', 'cancellation_deadline', 'cancellation_fee', 'is_refundable'];

    // Relationships with Reservation can be defined here if needed
}
