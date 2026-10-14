@extends('layouts.app')

@section('title', 'Add Menu Item')

@section('content')
<div class="container">
    <h2 class="mb-3">Add New Menu Item</h2>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf
                @include('products._form')
            </form>
        </div>
    </div>
</div>
@endsection
