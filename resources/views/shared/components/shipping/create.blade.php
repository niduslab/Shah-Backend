@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>{{ isset($shippingRate) ? 'Edit Shipping Rate' : 'Create New Shipping Rate' }}</h2>

    <form action="{{ isset($shippingRate) ? route('shipping.update', $shippingRate->id) : route('shipping.store') }}" method="POST">
        @csrf
        @if(isset($shippingRate))
            @method('PUT')
        @endif

        <div class="form-group mt-3">
            <label for="class">Class</label>
            <input type="text" id="class" name="class" class="form-control" value="{{ $shippingRate->class ?? old('class') }}" required>
        </div>

        <div class="form-group mt-3">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" class="form-control" value="{{ $shippingRate->country ?? old('country') }}" required>
        </div>

        <div class="form-group mt-3">
            <label for="method">Method</label>
            <select id="method" name="method" class="form-control" required>
                <option value="Standard" {{ (isset($shippingRate) && $shippingRate->method == 'Standard') ? 'selected' : '' }}>Standard</option>
                <option value="Express" {{ (isset($shippingRate) && $shippingRate->method == 'Express') ? 'selected' : '' }}>Express</option>
            </select>
        </div>

        <div class="form-group mt-3">
            <label for="delivery_time">Delivery Time</label>
            <input type="text" id="delivery_time" name="delivery_time" class="form-control" value="{{ $shippingRate->delivery_time ?? old('delivery_time') }}" required>
        </div>

        <div class="form-group mt-3">
            <label for="free_shipping_min_order">Free Shipping Minimum Order</label>
            <input type="number" step="0.01" id="free_shipping_min_order" name="free_shipping_min_order" class="form-control" value="{{ $shippingRate->free_shipping_min_order ?? old('free_shipping_min_order') }}">
        </div>

        <div class="form-group mt-3">
            <label for="def_country_price">Default Country Shipping Rate</label>
            <input type="number" step="0.01" id="def_country_price" name="def_country_price" class="form-control" value="{{ $shippingRate->def_country_price ?? old('def_country_price') }}">
        </div>

        <div class="form-group mt-3">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="1" {{ (isset($shippingRate) && $shippingRate->status) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (isset($shippingRate) && !$shippingRate->status) ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-4">{{ isset($shippingRate) ? 'Update' : 'Create' }} Shipping Rate</button>
    </form>
</div>
@endsection
