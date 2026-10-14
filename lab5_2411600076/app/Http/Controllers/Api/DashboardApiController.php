<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_products' => Product::count(),
            'total_value' => (float) Product::sum(DB::raw('quantity * unit_price')),
            'low_stock' => Product::lowStock()->where('quantity', '>', 0)->count(),
            'out_of_stock' => Product::outOfStock()->count(),
            'updated_at' => now()->format('h:i:s A'),
        ]);
    }

    public function lowStock()
    {
        $products = Product::lowStock()->orderBy('quantity')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $product->quantity,
                'reorder_level' => $product->reorder_level,
                'status' => $product->stock_status,
                'color' => $product->stock_status_color,
            ];
        });

        return response()->json($products);
    }

    public function recentTransactions()
    {
        $transactions = InventoryTransaction::with(['product', 'user'])->recent()->take(5)->get()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'date' => $transaction->created_at->format('M d, h:i A'),
                'product' => $transaction->product->name ?? 'N/A',
                'type' => $transaction->transaction_type,
                'color' => $transaction->type_color,
                'quantity' => $transaction->signed_quantity,
                'user' => $transaction->user->name ?? 'N/A',
            ];
        });

        return response()->json($transactions);
    }

    public function productStock(Product $product)
    {
        return response()->json([
            'name' => $product->name,
            'quantity' => $product->quantity,
            'reorder_level' => $product->reorder_level,
        ]);
    }
}
