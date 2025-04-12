@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ isset($product) ? 'Edit Product' : 'Add Product' }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if(isset($product))
                            @method('PUT')
                        @endif

                        <div class="form-group row mb-3">
                            <label for="code" class="col-md-4 col-form-label text-md-right">Code</label>
                            <div class="col-md-6">
                                <input id="code" type="text" class="form-control @error('code') is-invalid @enderror" name="code" value="{{ old('code', $product->code ?? '') }}" required>
                                @error('code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-right">Name</label>
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $product->name ?? '') }}" required>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="price" class="col-md-4 col-form-label text-md-right">Price</label>
                            <div class="col-md-6">
                                <input id="price" type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" name="price" value="{{ old('price', $product->price ?? '') }}" required>
                                @error('price')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="model" class="col-md-4 col-form-label text-md-right">Model</label>
                            <div class="col-md-6">
                                <input id="model" type="text" class="form-control @error('model') is-invalid @enderror" name="model" value="{{ old('model', $product->model ?? '') }}" required>
                                @error('model')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="description" class="col-md-4 col-form-label text-md-right">Description</label>
                            <div class="col-md-6">
                                <textarea id="description" class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="photo" class="col-md-4 col-form-label text-md-right">Photo</label>
                            <div class="col-md-6">
                                <input id="photo" type="file" class="form-control @error('photo') is-invalid @enderror" name="photo">
                                @error('photo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                @if(isset($product) && $product->photo)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $product->photo) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row mb-3">
                            <label for="in_stock" class="col-md-4 col-form-label text-md-right">In Stock</label>
                            <div class="col-md-6">
                                <select id="in_stock" class="form-control @error('in_stock') is-invalid @enderror" name="in_stock">
                                    <option value="1" {{ old('in_stock', $product->in_stock ?? '') == 1 ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('in_stock', $product->in_stock ?? '') == 0 ? 'selected' : '' }}>No</option>
                                </select>
                                @error('in_stock')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        @if(auth()->user()->hasRole('Employee'))
                        <div class="form-group row mb-3">
                            <label for="quantity" class="col-md-4 col-form-label text-md-right">Quantity</label>
                            <div class="col-md-6">
                                <input id="quantity" type="number" class="form-control @error('quantity') is-invalid @enderror" name="quantity" value="{{ old('quantity', $product->quantity ?? 0) }}" required min="0">
                                @error('quantity')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($product) ? 'Update' : 'Create' }}
                                </button>
                                <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 