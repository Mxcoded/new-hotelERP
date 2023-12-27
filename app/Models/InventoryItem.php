<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;
     // Relationship with property
     public function property()
     {
         return $this->belongsTo(Property::class);
     }

     // Relationship with purchase orders
     public function purchaseOrders()
     {
         return $this->hasMany(PurchaseOrder::class);
     }

     // Relationship with suppliers
     public function suppliers()
     {
         return $this->belongsToMany(Supplier::class);
     }

     // Relationship with user (staff managing the inventory item)
     public function user()
     {
         return $this->belongsTo(User::class);
     }

     // Add other model properties/methods as needed
}
