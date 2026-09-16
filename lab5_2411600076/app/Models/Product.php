<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\InventoryTransaction;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'category',
        'quantity', 'reorder_level', 'unit_price', 'supplier'
    ];

    public function isLowStock()
    {
        return $this->quantity <= $this->reorder_level && $this->quantity > 0;
    }

    public function isOutOfStock()
    {
        return $this->quantity == 0;
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getStatusAttribute()
    {
        if ($this->isOutOfStock()) return 'Out of Stock';
        if ($this->isLowStock()) return 'Low Stock';
        return 'In Stock';
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }
}