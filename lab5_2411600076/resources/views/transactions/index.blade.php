@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Inventory Transactions</h2>
        <div class="d-flex gap-2">
            @can('admin')
                <a href="{{ route('adjustments.create') }}" class="btn btn-outline-secondary">Adjust Stock</a>
            @endcan
            <a href="{{ route('transactions.create') }}" class="btn btn-primary">+ Record Transaction</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Menu Item</label>
                    <select name="product_id" class="form-select form-select-sm">
                        <option value="">All Items</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(['Stock In', 'Stock Out', 'Adjustment'] as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button class="btn btn-primary btn-sm w-100">Filter</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date/Time</th><th>Menu Item</th><th>Type</th><th>Quantity</th><th>Balance</th>
                            <th>Reference</th><th>User</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                            <td><a href="{{ route('products.show', $transaction->product) }}">{{ $transaction->product->name }}</a></td>
                            <td><span class="badge bg-{{ $transaction->type_color }}">{{ $transaction->transaction_type }}</span></td>
                            <td>{{ $transaction->signed_quantity > 0 ? '+' : '' }}{{ $transaction->signed_quantity }}</td>
                            <td>{{ $transaction->balance_after ?? 'N/A' }}</td>
                            <td>{{ $transaction->reference ?? 'N/A' }}</td>
                            <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-outline-info">View</a>
                                @can('admin')
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this transaction? The stock change will be reversed.')">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
