@extends('layouts.app')

@section('title', 'Stock Adjustment')

@section('content')
<div class="container">
    <h2 class="mb-3">Stock Adjustment</h2>

    <div class="alert alert-info">
        Use this when the physical count in the kitchen or storage does not match the system
        (spoiled food, damaged items, loss or counting errors). The system records the difference as an adjustment.
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('adjustments.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Menu Item</label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">System Quantity</label>
                        <input type="text" id="system_quantity" class="form-control" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Physical Count</label>
                        <input type="number" name="physical_count" id="physical_count" class="form-control" value="{{ old('physical_count') }}" min="0" required>
                        <div id="difference" class="form-text"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reason</label>
                        <select name="reason" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason }}" {{ old('reason') == $reason ? 'selected' : '' }}>{{ $reason }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Adjustment</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const productSelect = document.getElementById('product_id');
    const systemQuantity = document.getElementById('system_quantity');
    const physicalCount = document.getElementById('physical_count');
    const differenceText = document.getElementById('difference');

    function showDifference() {
        if (systemQuantity.value === '' || physicalCount.value === '') {
            differenceText.textContent = '';
            return;
        }

        const difference = physicalCount.value - systemQuantity.value;
        differenceText.textContent = 'Difference: ' + (difference > 0 ? '+' : '') + difference;
    }

    async function loadStock() {
        systemQuantity.value = '';

        if (!productSelect.value) {
            showDifference();
            return;
        }

        try {
            const response = await fetch('/api/products/' + productSelect.value + '/stock');
            const data = await response.json();
            systemQuantity.value = data.quantity;
        } catch (error) {
            systemQuantity.value = 'Error';
        }

        showDifference();
    }

    productSelect.addEventListener('change', loadStock);
    physicalCount.addEventListener('input', showDifference);
    loadStock();
</script>
@endpush
