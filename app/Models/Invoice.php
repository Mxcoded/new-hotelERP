<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'guest_id', 'reservation_id', 'property_id', 'invoice_number', 'total_amount',
        'issue_date', 'status', 'type', 'description'
    ];

    // Relationship with guest
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    // Relationship with reservation
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // Relationship with property
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Relationship with folios (to manage billing details for each guest or reservation)
    public function folios()
    {
        return $this->hasMany(Folio::class);
    }

    // Relationship with transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }


    // Scope query for master bills
    public function scopeMasterBills($query)
    {
        return $query->where('type', 'master_bill');
    }

    // Scope query for deposit slips
    public function scopeDepositSlips($query)
    {
        return $query->where('type', 'deposit_slip');
    }

    // Utility method to generate a master bill
    public static function generateMasterBill($reservationId)
    {
        // Logic to generate a master bill
    }

    // Utility method to generate a deposit slip
    public static function generateDepositSlip($reservationId, $amount)
    {
        // Logic to generate a deposit slip
    }

    // Method to generate unique invoice number
    public static function generateInvoiceNumber($propertyId)
    {
        $prefix = "BP{$propertyId}_";
        $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')
            ->latest('id')
            ->first();

        if ($lastInvoice) {
            $lastNumber = str_replace($prefix, '', $lastInvoice->invoice_number);
            $newNumber = sprintf('%07d', intval($lastNumber) + 1);
        } else {
            $newNumber = '0000001';
        }

        return $prefix . $newNumber;
    }

    // Call this method before saving a new invoice
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->property_id);
            }
        });
    }
    // Add other model properties/methods as needed
}
