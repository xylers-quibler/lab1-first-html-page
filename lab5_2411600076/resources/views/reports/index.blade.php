@extends('layouts.app')

@section('title', 'Inventory Reports')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📊 Inventory Reports</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    {{-- Overall Summary --}}
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Items</h5>
                    <h2 class="card-text fw-bold text-primary">{{ $totalItems }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h5 class="card-title">Total Quantity</h5>
                    <h2 class="card-text fw-bold text-info">{{ $totalQuantity }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card stat-card text-center">
                <div class="card-body">
                    <h5 class="card-title">Grand Total Value</h5>
                    <h2 class="card-text fw-bold text-success">₱{{ number_format($grandTotal, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Category Summary --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Summary by Category</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Items</th>
                            <th>Total Quantity</th>
                            <th>Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categorySummary as $cat)
                        <tr>
                            <td><strong>{{ $cat->category }}</strong></td>
                            <td>{{ $cat->total_items }}</td>
                            <td>{{ $cat->total_quantity }}</td>
                            <td>₱{{ number_format($cat->total_value, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-dark">
                            <th>GRAND TOTAL</th>
                            <th>{{ $totalItems }}</th>
                            <th>{{ $totalQuantity }}</th>
                            <th>₱{{ number_format($grandTotal, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Low Stock Report --}}
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0">⚠️ Low Stock Items (Need Restocking)</h5>
        </div>
        <div class="card-body">
            @if($lowStockItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>SKU</th><th>Name</th><th>Category</th>
                                <th>Current Qty</th><th>Reorder Level</th><th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockItems as $item)
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category }}</td>
                                <td class="text-warning fw-bold">{{ $item->quantity }}</td>
                                <td>{{ $item->reorder_level }}</td>
                                <td>{{ $item->supplier ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">✅ No low stock items. Everything is well-stocked!</p>
            @endif
        </div>
    </div>

    {{-- Out of Stock Report --}}
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">🚫 Out of Stock Items (Urgent)</h5>
        </div>
        <div class="card-body">
            @if($outOfStockItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>SKU</th><th>Name</th><th>Category</th><th>Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($outOfStockItems as $item)
                            <tr>
                                <td>{{ $item->sku }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->category }}</td>
                                <td>{{ $item->supplier ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted mb-0">✅ No out of stock items.</p>
            @endif
        </div>
    </div>
</div>
@endsection