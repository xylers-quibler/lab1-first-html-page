@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="container">
    <h2 class="mb-3">Transaction #{{ $transaction->id }}</h2>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h5 class="mb-0">Transaction Details</h5></div>
                <div class="card-body">
                    <p><strong>Type:</strong> <span class="badge bg-{{ $transaction->type_color }}">{{ $transaction->transaction_type }}</span></p>
                    <p><strong>Quantity:</strong> {{ $transaction->signed_quantity > 0 ? '+' : '' }}{{ $transaction->signed_quantity }}</p>
                    <p><strong>Reference:</strong> {{ $transaction->reference ?? 'N/A' }}</p>
                    <p><strong>Notes:</strong> {{ $transaction->notes ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Date:</strong> {{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card mb-3">
                <div class="card-header"><h5 class="mb-0">Menu Item</h5></div>
                <div class="card-body">
                    <p><strong>Name:</strong> <a href="{{ route('products.show', $transaction->product) }}">{{ $transaction->product->name }}</a></p>
                    <p><strong>SKU:</strong> {{ $transaction->product->sku }}</p>
                    <p class="mb-0"><strong>Current Stock:</strong> {{ $transaction->product->quantity }}
                        <span class="badge bg-{{ $transaction->product->stock_status_color }}">{{ $transaction->product->stock_status }}</span>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="mb-0">Recorded By</h5></div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $transaction->user->name ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ $transaction->user->email ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Back to List</a>
    @can('admin')
        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger" onclick="return confirm('Delete this transaction? The stock change will be reversed.')">Delete</button>
        </form>
    @endcan
</div>
@endsection
