<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    // Relationship with supplier (who the order is placed with)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Relationship with inventory items (items being ordered)
    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class);
    }

    // Optionally, relationship with property (if orders are specific to properties)
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Relationship with user (staff who created or managed the purchase order)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add other model properties/methods as needed
}
