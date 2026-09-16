<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'The menu item name is required.',
            'name.max' => 'The name is too long (max 255 characters).',
            'sku.required' => 'The SKU is required.',
            'sku.unique' => 'This SKU already exists in the inventory.',
            'category.required' => 'Please select a category.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity cannot be negative.',
            'reorder_level.required' => 'Reorder level is required.',
            'unit_price.required' => 'Unit price is required.',
            'unit_price.numeric' => 'Unit price must be a valid number.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ], $messages);

        $product = Product::create($validated);

        // Record the initial stock as a transaction
        if ($validated['quantity'] > 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => 'Stock In',
                'quantity' => $validated['quantity'],
                'reference' => 'Initial Stock',
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Menu item added successfully.');
    }

    public function show(Product $product)
    {
        $transactions = $product->transactions()->latest()->get();
        return view('products.show', compact('product', 'transactions'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $messages = [
            'name.required' => 'The menu item name is required.',
            'name.max' => 'The name is too long (max 255 characters).',
            'sku.required' => 'The SKU is required.',
            'sku.unique' => 'This SKU already exists in the inventory.',
            'category.required' => 'Please select a category.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity cannot be negative.',
            'reorder_level.required' => 'Reorder level is required.',
            'unit_price.required' => 'Unit price is required.',
            'unit_price.numeric' => 'Unit price must be a valid number.',
            'unit_price.min' => 'Unit price cannot be negative.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
        ], $messages);

        // Detect quantity change
        $oldQuantity = $product->quantity;
        $newQuantity = $validated['quantity'];
        $difference = $newQuantity - $oldQuantity;

        $product->update($validated);

        // Record transaction if quantity changed
        if ($difference != 0) {
            InventoryTransaction::create([
                'product_id' => $product->id,
                'transaction_type' => $difference > 0 ? 'Stock In' : 'Stock Out',
                'quantity' => abs($difference),
                'reference' => 'Manual Adjustment',
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Menu item deleted successfully.');
    }
}