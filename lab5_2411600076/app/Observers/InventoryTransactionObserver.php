<?php

namespace App\Observers;

use App\Models\InventoryTransaction;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class InventoryTransactionObserver
{
    public function creating(InventoryTransaction $transaction): void
    {
        $product = $transaction->product;

        if ($transaction->transaction_type === 'Stock Out' && $transaction->quantity > $product->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock. Only ' . $product->quantity . ' left.',
            ]);
        }

        if ($transaction->transaction_type === 'Adjustment' && $product->quantity + $transaction->quantity < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Adjustment would make the stock negative.',
            ]);
        }
    }

    public function created(InventoryTransaction $transaction): void
    {
        $product = $transaction->product;
        $before = $product->quantity;

        if ($transaction->transaction_type === 'Stock In') {
            $product->increment('quantity', $transaction->quantity);
        } elseif ($transaction->transaction_type === 'Stock Out') {
            $product->decrement('quantity', $transaction->quantity);
        } else {
            $product->quantity = $before + $transaction->quantity;
            $product->save();
        }

        $product->refresh();

        $transaction->balance_after = $product->quantity;
        $transaction->saveQuietly();

        if ($before > $product->reorder_level && $product->isLowStock()) {
            Notification::send(User::where('role', 'admin')->get(), new LowStockNotification($product));
        }
    }
}
