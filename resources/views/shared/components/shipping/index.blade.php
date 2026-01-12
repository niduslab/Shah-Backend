@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Shipping Rates</h2>
        <a href="{{ route('shipping.create') }}" class="btn btn-primary">Add New Shipping</a>
    </div>
    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Class</th>
                <th>Country</th>
                <th>Method</th>
                <th>Delivery Time</th>
                <th>Free Shipping Order</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shippingRates as $rate)
                <tr>
                    <td>{{ $rate->class }}</td>
                    <td>{{ $rate->country }}</td>
                    <td>{{ $rate->method }}</td>
                    <td>{{ $rate->delivery_time }}</td>
                    <td>{{ $rate->free_shipping_min_order }}</td>
                    <td>{{ $rate->status ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a href="{{ route('shipping.edit', $rate->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('shipping.destroy', $rate->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
