@extends('layouts.app')

@section('title', 'Record Transaction')

@section('content')
<div class="container">
    <h2 class="mb-3">Record Transaction</h2>

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
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Menu Item</label>
                        <select name="product_id" id="product_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-quantity="{{ $product->quantity }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        <div id="current_stock" class="form-text"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Transaction Type</label>
                        @foreach($types as $type)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transaction_type" id="type_{{ $loop->index }}" value="{{ $type }}" {{ old('transaction_type', 'Stock In') == $type ? 'checked' : '' }}>
                                <label class="form-check-label" for="type_{{ $loop->index }}">{{ $type }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="PO-1001 or SO-1001">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Transaction</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const stockText = document.getElementById('current_stock');
    

    function updateStock() {
        const selected = productSelect.options[productSelect.selectedIndex];
        const stock = selected.dataset.quantity;

        if (!stock) {
            stockText.textContent = '';
            quantityInput.removeAttribute('max');
            return;
        }

        stockText.textContent = 'Current stock: ' + stock;

        if (document.querySelector('input[name="transaction_type"]:checked').value === 'Stock Out') {
            quantityInput.max = stock;
        } else {
            quantityInput.removeAttribute('max');
        }
    }

    productSelect.addEventListener('change', updateStock);
    document.querySelectorAll('input[name="transaction_type"]').forEach(radio => radio.addEventListener('change', updateStock));
    updateStock();
</script>
@endpush
