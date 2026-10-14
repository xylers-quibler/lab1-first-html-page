{{-- Shared form for create.blade.php and edit.blade.php --}}
{{-- On create, $product is not set, so the fields start empty. --}}
{{-- On edit, $product is set, so the fields show the current values. --}}

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Menu Item Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">SKU</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-control" required>
            <option value="">-- Select --</option>
            @foreach(['Appetizer', 'Main Course', 'Dessert', 'Beverage'] as $category)
                <option value="{{ $category }}" {{ old('category', $product->category ?? '') == $category ? 'selected' : '' }}>
                    {{ $category }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Supplier</label>
        <input type="text" name="supplier" class="form-control" value="{{ old('supplier', $product->supplier ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', $product->quantity ?? 0) }}" min="0" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Reorder Level</label>
        <input type="number" name="reorder_level" class="form-control" value="{{ old('reorder_level', $product->reorder_level ?? 10) }}" min="0" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Unit Price (₱)</label>
        <input type="number" name="unit_price" class="form-control" value="{{ old('unit_price', $product->unit_price ?? '') }}" step="0.01" min="0" required>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
</div>

<button type="submit" class="btn btn-primary">Save Menu Item</button>
<a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
