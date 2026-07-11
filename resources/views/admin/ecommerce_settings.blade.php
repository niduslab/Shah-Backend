@extends('layouts.app')
@section('title', 'NidusCart - Settings')

@section('content')
<section class="content-main">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 card shadow-sm p-4 mb-5 bg-body rounded bg-white">
                <h1 class="mb-4">E-commerce Settings</h1>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('ecommerce-settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-4">
                        <label for="currency">Currency:</label>
                        <select id="currency" name="currency" class="form-control">
                            <option value="BDT" {{ old('currency', $settings->currency ?? '') == 'BDT' ? 'selected' : '' }}>BDT</option>
                            <!-- Add more options as needed -->
                        </select>
                        @error('currency')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="currency_symbol">Currency Symbol:</label>
                        <select id="currency_symbol" name="currency_symbol" class="form-control">
                            <option value="৳" {{ old('currency_symbol', $settings->currency_symbol ?? '') == '৳' ? 'selected' : '' }}>৳</option>
                            <!-- Add more options as needed -->
                        </select>
                        @error('currency_symbol')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>



                    <div class="form-group mb-4">
                        <label for="vendor_commission">Vendor Commission:</label>
                        <input type="number" id="vendor_commission" name="vendor_commission" class="form-control" step="0.01" value="{{ old('vendor_commission', $settings->vendor_commission ?? '') }}">
                        @error('vendor_commission')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="admin_commission">Admin Commission:</label>
                        <input type="number" id="admin_commission" name="admin_commission" class="form-control" step="0.01" value="{{ old('admin_commission', $settings->admin_commission ?? '') }}">
                        @error('admin_commission')
                        <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="vendor_commission">Product Default Vat/Tax %:</label>
                        <input type="number" id="product_default_vat_tax" name="product_default_vat_tax" class="form-control"  step="0.01" value="{{ old('product_default_vat_tax', $settings->vendor_commission ?? '') }}">
                        @error('product_default_vat_tax')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="default_shipping_charge">Default Shipping Charge:</label>
                        <input type="number" id="default_shipping_charge" name="default_shipping_charge" class="form-control" step="0.01" value="{{ old('default_shipping_charge', $settings->default_shipping_charge ?? '') }}">
                        @error('default_shipping_charge')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" id="is_multivendor" name="is_multivendor" class="form-check-input" value="1" {{ old('is_multivendor', $settings->is_multivendor ?? 0) ? 'checked' : '' }}>
                        <label for="is_multivendor" class="form-check-label">Enable multivendor support?</label>
                        @error('is_multivendor')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
