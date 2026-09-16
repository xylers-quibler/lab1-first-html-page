<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalValue = Product::sum(DB::raw('quantity * unit_price'));
        $lowStock = Product::whereColumn('quantity', '<=', 'reorder_level')
                           ->where('quantity', '>', 0)->count();
        $outOfStock = Product::where('quantity', 0)->count();
        $inStockCount = Product::whereColumn('quantity', '>', 'reorder_level')->count();
        $lowStockCount = $lowStock;
        $outOfStockCount = $outOfStock;
        $recentProducts = Product::latest()->take(5)->get();

        // Chart data: Inventory Value by Category
        $categorySummary = Product::all()
            ->groupBy('category')
            ->map(function ($products, $category) {
                return (object) [
                    'category' => $category,
                    'total_value' => $products->sum(function ($p) {
                        return $p->quantity * $p->unit_price;
                    }),
                ];
            })
            ->values();

        // Chart data: Top 5 products by value
        $topProducts = Product::all()
            ->map(function ($p) {
                return (object) [
                    'name' => $p->name,
                    'total_value' => $p->quantity * $p->unit_price,
                ];
            })
            ->sortByDesc('total_value')
            ->take(5)
            ->values();

        return view('dashboard', compact(
            'totalProducts', 'totalValue', 'lowStock', 'outOfStock',
            'recentProducts', 'inStockCount', 'lowStockCount', 'outOfStockCount',
            'categorySummary', 'topProducts'
        ));
    }
}