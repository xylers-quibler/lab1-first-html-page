@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap align-items-center pb-2 mb-3 border-bottom">
    <h2 class="text-primary-custom">Good day, {{ auth()->user()->name }}!</h2>
</div>

{{-- Stat Cards --}}
<section class="row mb-4">
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card shadow-hover border-primary-custom text-center h-100">
            <div class="card-body">
                <h5 class="card-title">Total Menu Items</h5>
                <h2 class="card-text fw-bold text-primary">{{ $totalProducts }}</h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card shadow-hover border-success-custom text-center h-100">
            <div class="card-body">
                <h5 class="card-title">Inventory Value</h5>
                <h2 class="card-text fw-bold text-success">₱{{ number_format($totalValue, 2) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card shadow-hover border-accent text-center h-100">
            <div class="card-body">
                <h5 class="card-title text-accent">Low Stock</h5>
                <h2 class="card-text fw-bold text-warning">{{ $lowStock }}</h2>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 mb-3">
        <div class="card stat-card shadow-hover text-center h-100">
            <div class="card-body">
                <h5 class="card-title">Out of Stock</h5>
                <h2 class="card-text fw-bold text-danger">{{ $outOfStock }}</h2>
            </div>
        </div>
    </div>
</section>

{{-- Low Stock Alert --}}
@if($lowStock + $outOfStock > 0)
    <div class="alert alert-warning">
        ⚠️ {{ $lowStock + $outOfStock }} item(s) need restocking.
        <a href="{{ route('products.index') }}" class="alert-link">View Menu Inventory</a>
    </div>
@endif

{{-- Recent Menu Items --}}
<div class="card shadow-hover">
    <div class="card-header">
        <h5 class="mb-0">Recent Menu Items</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr><th>SKU</th><th>Name</th><th>Category</th><th>Qty</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach($recentProducts as $product)
                    <tr>
                        <td>{{ $product->sku }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category }}</td>
                        <td>{{ $product->quantity }}</td>
                        <td>
                            <span class="badge bg-{{ $product->isOutOfStock() ? 'danger' : ($product->isLowStock() ? 'warning' : 'success') }}">
                                {{ $product->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 3 Charts Row (matches Lab 4) --}}
<section class="row mt-4">
    <div class="col-lg-5 mb-3">
        <div class="card shadow-hover h-100">
            <div class="card-header">
                <h5 class="mb-0">📊 Inventory Value by Category</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-3 mb-3">
        <div class="card shadow-hover h-100">
            <div class="card-header">
                <h5 class="mb-0">📦 Stock Status</h5>
            </div>
            <div class="card-body">
                <canvas id="stockChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card shadow-hover h-100">
            <div class="card-header">
                <h5 class="mb-0">🏆 Top 5 Menu Items by Value</h5>
            </div>
            <div class="card-body">
                <canvas id="topChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
</section>

{{-- Chart.js + Config --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Color palette: Stone & Sage (same as public/css/style.css and Lab 4)
    const C_PRIMARY = '#6B8F71';   // Sage
    const C_SECONDARY = '#C9A66B'; // Muted Gold
    const C_SUCCESS = '#6B8F71';   // Sage - In Stock
    const C_WARNING = '#C9A66B';   // Muted Gold - Low Stock
    const C_DANGER = '#2F3437';    // Charcoal - Out of Stock

    // Chart 1: Inventory Value by Category
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: @json($categorySummary->pluck('category')),
            datasets: [{
                label: 'Value (₱)',
                data: @json($categorySummary->pluck('total_value')),
                backgroundColor: C_PRIMARY,
                borderRadius: 6,
                maxBarThickness: 48
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // Chart 2: Stock Status
    new Chart(document.getElementById('stockChart'), {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock'],
            datasets: [{
                data: [{{ $inStockCount }}, {{ $lowStockCount }}, {{ $outOfStockCount }}],
                backgroundColor: [C_SUCCESS, C_WARNING, C_DANGER],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 12 } } } },
            cutout: '60%'
        }
    });

    // Chart 3: Top 5 by Value
    new Chart(document.getElementById('topChart'), {
        type: 'bar',
        data: {
            labels: @json($topProducts->pluck('name')),
            datasets: [{
                label: 'Value (₱)',
                data: @json($topProducts->pluck('total_value')),
                backgroundColor: C_SECONDARY,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });
</script>
@endsection