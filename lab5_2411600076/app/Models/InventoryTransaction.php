<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'transaction_type', 'quantity', 'balance_after', 'reference', 'notes', 'user_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStockIn($query)
    {
        return $query->where('transaction_type', 'Stock In');
    }

    public function scopeStockOut($query)
    {
        return $query->where('transaction_type', 'Stock Out');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    public function getSignedQuantityAttribute()
    {
        if ($this->transaction_type === 'Stock Out') {
            return -$this->quantity;
        }

        return $this->quantity;
    }

    public function getTypeColorAttribute()
    {
        if ($this->transaction_type === 'Stock In') return 'success';
        if ($this->transaction_type === 'Stock Out') return 'danger';
        return 'info';
    }
}
