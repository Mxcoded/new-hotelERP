<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    // Relationship with inventory items
    public function inventoryItems()
    {
        return $this->belongsToMany(InventoryItem::class);
    }

    // Relationship with purchase orders
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    // Relationship with properties
    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    // Relationship with users (staff who interact with the supplier)
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Add other model properties/methods as needed
}
