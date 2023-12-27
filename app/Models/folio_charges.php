<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class folio_charges extends Model
{
    use HasFactory;
    protected $table = 'folio_charges';

    protected $fillable = [
        'folio_id', 'description', 'amount', 'date_incurred', 'charge_type'
    ];

    public function folio()
    {
        return $this->belongsTo(Folio::class, 'folio_id');
    }

    // Additional methods as needed
}
