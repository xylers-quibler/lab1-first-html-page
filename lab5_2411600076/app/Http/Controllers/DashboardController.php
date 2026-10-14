<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalValue = Product::sum(DB::raw('quantity * unit_price'));
        $lowStock = Product::lowStock()->where('quantity', '>', 0)->count();
        $outOfStock = Product::outOfStock()->count();
        $inStockCount = Product::whereColumn('quantity', '>', 'reorder_level')->count();
        $lowStockCount = $lowStock;
        $outOfStockCount = $outOfStock;

        $recentProducts = Product::latest('updated_at')->take(5)->get();
        $lowStockProducts = Product::lowStock()->orderBy('quantity')->get();
        $recentTransactions = InventoryTransaction::with(['product', 'user'])->recent()->take(5)->get();

        $categorySummary = Product::all()
            ->groupBy('category')
            ->map(function ($products, $category) {
                return (object) [
                    'category' => $category,
                    'total_value' => $products->sum('total_value'),
                ];
            })
            ->values();

        $topProducts = Product::all()
            ->sortByDesc('total_value')
            ->take(5)
            ->map(function ($product) {
                return (object) [
                    'name' => $product->name,
                    'total_value' => $product->total_value,
                ];
            })
            ->values();

        return view('dashboard', compact(
            'totalProducts', 'totalValue', 'lowStock', 'outOfStock',
            'inStockCount', 'lowStockCount', 'outOfStockCount',
            'recentProducts', 'lowStockProducts', 'recentTransactions',
            'categorySummary', 'topProducts'
        ));
    }
}
