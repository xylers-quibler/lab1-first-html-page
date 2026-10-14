@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="container">
    <h2 class="mb-3">Inventory Reports</h2>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Stock Movement Report</h5>
            <form method="GET" action="{{ route('reports.index') }}" class="d-flex gap-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                <button class="btn btn-primary btn-sm">Generate</button>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>Date</th><th>Menu Item</th><th>Type</th><th>Quantity</th><th>Reference</th><th>User</th></tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $transaction->product->name }}</td>
                            <td><span class="badge bg-{{ $transaction->type_color }}">{{ $transaction->transaction_type }}</span></td>
                            <td>{{ $transaction->signed_quantity > 0 ? '+' : '' }}{{ $transaction->signed_quantity }}</td>
                            <td>{{ $transaction->reference ?? 'N/A' }}</td>
                            <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">No movements in this date range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Low Stock Report</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>Menu Item</th><th>SKU</th><th>Quantity</th><th>Reorder Level</th><th>Status</th><th>Supplier</th></tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->reorder_level }}</td>
                            <td><span class="badge bg-{{ $product->stock_status_color }}">{{ $product->stock_status }}</span></td>
                            <td>{{ $product->supplier ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center">No low stock items.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Inventory Valuation Report</h5></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr><th>Menu Item</th><th>Category</th><th>Quantity</th><th>Unit Price</th><th>Total Value</th></tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>₱{{ number_format($product->unit_price, 2) }}</td>
                            <td>₱{{ number_format($product->total_value, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Total Inventory Value</td>
                            <td>₱{{ number_format($totalValue, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
