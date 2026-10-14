<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        $movements = InventoryTransaction::with(['product', 'user'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->recent()
            ->get();

        $lowStockProducts = Product::lowStock()->orderBy('quantity')->get();

        $products = Product::orderBy('category')->orderBy('name')->get();
        $totalValue = $products->sum('total_value');

        return view('reports.index', compact('movements', 'lowStockProducts', 'products', 'totalValue', 'dateFrom', 'dateTo'));
    }
}
