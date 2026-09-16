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
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection