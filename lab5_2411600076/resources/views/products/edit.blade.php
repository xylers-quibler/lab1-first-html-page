@extends('layouts.app')

@section('title', 'Edit Menu Item')

@section('content')
<div class="container">
    <h2 class="mb-3">Edit Menu Item</h2>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')
                @include('products._form')
            </form>
        </div>
    </div>
</div>
@endsection
