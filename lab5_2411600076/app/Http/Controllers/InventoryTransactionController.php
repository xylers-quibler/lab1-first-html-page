<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryTransaction::with(['product', 'user'])->recent();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('type')) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'products'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        $types = ['Stock In', 'Stock Out'];

        return view('transactions.create', compact('products', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'transaction_type' => 'required|in:Stock In,Stock Out',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::find($validated['product_id']);

        if ($validated['transaction_type'] === 'Stock Out' && $validated['quantity'] > $product->quantity) {
            return back()->withInput()->withErrors([
                'quantity' => 'Not enough stock. Only ' . $product->quantity . ' left.',
            ]);
        }

        $validated['user_id'] = Auth::id();

        DB::transaction(function () use ($validated) {
            InventoryTransaction::create($validated);
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction recorded successfully.');
    }

    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    public function destroy(InventoryTransaction $transaction)
    {
        $product = $transaction->product;
        $change = $transaction->signed_quantity;

        if ($product->quantity - $change < 0) {
            return back()->withErrors(['quantity' => 'Cannot delete: stock would become negative.']);
        }

        DB::transaction(function () use ($transaction, $product, $change) {
            $product->quantity = $product->quantity - $change;
            $product->save();

            $transaction->delete();
        });

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted and stock reversed.');
    }
}
