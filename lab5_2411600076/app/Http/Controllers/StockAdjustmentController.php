<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    private $reasons = ['Physical Count', 'Spoiled or Expired', 'Damaged Goods', 'Theft or Loss', 'System Error'];

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $reasons = $this->reasons;

        return view('adjustments.create', compact('products', 'reasons'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'physical_count' => 'required|integer|min:0',
            'reason' => 'required|in:' . implode(',', $this->reasons),
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $difference = $validated['physical_count'] - $product->quantity;

        if ($difference == 0) {
            return back()->withInput()->withErrors(['physical_count' => 'The physical count matches the system quantity. No adjustment needed.']);
        }

        $notes = 'Reason: ' . $validated['reason'];

        if (!empty($validated['notes'])) {
            $notes = $notes . ' - ' . $validated['notes'];
        }

        DB::transaction(function () use ($product, $difference, $notes) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'Adjustment',
                'quantity' => $difference,
                'reference' => 'ADJ-' . now()->format('YmdHis'),
                'notes' => $notes,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Stock adjusted for ' . $product->name . ' (' . ($difference > 0 ? '+' : '') . $difference . ').');
    }
}
