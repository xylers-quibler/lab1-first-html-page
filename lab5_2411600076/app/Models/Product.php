<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'sku', 'description', 'category',
        'quantity', 'reorder_level', 'unit_price', 'supplier'
    ];

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function latestTransaction()
    {
        return $this->hasOne(InventoryTransaction::class)->latestOfMany();
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock()
    {
        return $this->quantity <= 0;
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getStockStatusAttribute()
    {
        if ($this->isOutOfStock()) return 'Out of Stock';
        if ($this->isLowStock()) return 'Low Stock';
        return 'In Stock';
    }

    public function getStockStatusColorAttribute()
    {
        if ($this->isOutOfStock()) return 'danger';
        if ($this->isLowStock()) return 'warning';
        return 'success';
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}