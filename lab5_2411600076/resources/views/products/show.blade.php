@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container">
    <h2 class="mb-3">{{ $product->name }}</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>SKU:</strong> {{ $product->sku }}</p>
            <p><strong>Category:</strong> {{ $product->category }}</p>
            <p><strong>Description:</strong> {{ $product->description ?? 'N/A' }}</p>
            <p><strong>Quantity:</strong> {{ $product->quantity }}</p>
            <p><strong>Reorder Level:</strong> {{ $product->reorder_level }}</p>
            <p><strong>Unit Price:</strong> ₱{{ number_format($product->unit_price, 2) }}</p>
            <p><strong>Total Value:</strong> ₱{{ number_format($product->total_value, 2) }}</p>
            <p><strong>Supplier:</strong> {{ $product->supplier ?? 'N/A' }}</p>
            <p><strong>Status:</strong>
                <span class="badge bg-{{ $product->isOutOfStock() ? 'danger' : ($product->isLowStock() ? 'warning' : 'success') }}">
                    {{ $product->status }}
                </span>
            </p>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" onclick="return confirm('Delete this menu item?')">Delete</button>
        </form>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
    </div>

    {{-- Related inventory transactions (stock in / stock out history) --}}
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Inventory Transactions</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>Date</th><th>Type</th><th>Quantity</th><th>Reference</th><th>User</th></tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <span class="badge bg-{{ $transaction->transaction_type == 'Stock In' ? 'success' : 'warning' }}">
                                    {{ $transaction->transaction_type }}
                                </span>
                            </td>
                            <td>{{ $transaction->quantity }}</td>
                            <td>{{ $transaction->reference ?? 'N/A' }}</td>
                            <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">No transactions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
