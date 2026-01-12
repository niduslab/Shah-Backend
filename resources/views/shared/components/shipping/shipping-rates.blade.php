@extends('layouts.app')
@section('title', 'Shipping Rates | Niduscart')
@section('content')

<div class="container p-5">
    <div class="card mb-4">
        <header class="card-header">
            <h2>Manage Shipping Rates</h2>
            <button class="btn btn-primary mb-3" onclick="openCreateModal()">Add Shipping Rate</button>
        </header>

    <div class="card-body">
        <table id="shippingRatesTable" class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Class</th>
                    <th>Country</th>
                    <th>Method</th>
                    <th>Delivery Time</th>
                    <th>Free Shipping Min Order</th>
                    <th>Default Country Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>



</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="shippingRateModal" tabindex="-1" aria-labelledby="shippingRateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="shippingRateForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="shippingRateModalLabel">Add Shipping Rate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="shippingRateId">
                    <div class="mb-3">
                        <label for="class" class="form-label">Class</label>
                        <input type="text" class="form-control" id="class" name="class" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-group">
                            <label for="country">Shipping Country *</label>
                            <select name="country" id="country" class="form-control">
                                <option value="">Select Country</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="method" class="form-label">Method</label>
                        <select class="form-select" id="method" name="method" required>
                            <option value="Standard">Standard</option>
                            <option value="Express">Express</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="delivery_time" class="form-label">Delivery Time</label>
                        {{-- <input type="text" class="form-control" id="delivery_time" name="delivery_time" required> --}}
                        <select name="delivery_time" id="delivery_time" class="form-control" required>
                            <option value="1-3 delivery days">1-3 delivery days</option>
                            <option value="3-5 delivery days">3-5 delivery days</option>
                            <option value="5-7 delivery days">5-7 delivery days</option>
                            <option value="7-10 delivery days">7-10 delivery days</option>
                            <option value="15-20 delivery days">15-20 delivery days</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="free_shipping_min_order" class="form-label">Free Shipping Min Order</label>
                        <input type="number" class="form-control" id="free_shipping_min_order" name="free_shipping_min_order" required>
                    </div>
                    <div class="mb-3">
                        <label for="def_country_price" class="form-label">Default Country Price</label>
                        <input type="number" class="form-control" id="def_country_price" name="def_country_price" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>
<script type="module">
 import { Country, State, City } from 'https://cdn.jsdelivr.net/npm/country-state-city@3.2.1/+esm';
    // Initialize Datatable
    $(document).ready(function() {
        const countrySelect = document.getElementById('country');
        const countries = Country.getAllCountries();
        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.isoCode;
            option.textContent = country.name;
            countrySelect.appendChild(option);
        });
    });
</script>

<script type="text/javascript">
    // Initialize Datatable
    $(document).ready(function() {
        $('#shippingRatesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('shipping-rates.index') }}',
            columns: [
                { data: 'id' },
                { data: 'class' },
                { data: 'country' },
                { data: 'method' },
                { data: 'delivery_time' },
                { data: 'free_shipping_min_order' },
                { data: 'def_country_price' },
                { data: 'status', render: function(data) { return data ? 'Active' : 'Inactive'; }},
                { data: 'actions', orderable: false, searchable: false }
            ]
        });
    });

    // Open modal for creating or editing shipping rate
    function openCreateModal() {
        $('#shippingRateForm').trigger("reset");
        $('#shippingRateId').val('');
        $('#shippingRateModalLabel').text("Add Shipping Rate");
        $('#shippingRateModal').modal('show');
    }

    function openEditModal(id) {
    $.get(`/shipping-rates/${id}`, function(data) { // Update this line
        $('#shippingRateModalLabel').text("Edit Shipping Rate");
        $('#shippingRateId').val(data.id);
        $('#class').val(data.class);
        $('#country').val(data.country);
        $('#method').val(data.method);
        $('#delivery_time').val(data.delivery_time);
        $('#free_shipping_min_order').val(data.free_shipping_min_order);
        $('#def_country_price').val(data.def_country_price);
        $('#status').val(data.status);
        $('#shippingRateModal').modal('show');
    }).fail(function(xhr) {
        console.error('Error fetching shipping rate data:', xhr.responseText);
        alert('Failed to fetch shipping rate data.');
    });
}


    // Submit form to add or update shipping rate
    $('#shippingRateForm').submit(function(e) {
        e.preventDefault();
        let id = $('#shippingRateId').val();
        let url = id ? `/shipping-rates/${id}` : "{{ route('shipping-rates.store') }}";
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: $('#shippingRateForm').serialize(),
            success: function(response) {
                $('#shippingRateModal').modal('hide');
                $('#shippingRatesTable').DataTable().ajax.reload();
                notyf.success(response.message);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                notyf.error(response.error || 'Something went wrong.');
            }
        });
    });

    // Delete shipping rate
    function deleteShippingRate(id) {
        if (confirm("Are you sure you want to delete this shipping rate?")) {
            $.ajax({
                url: `/shippingRates/${id}`,
                type: 'DELETE',
                success: function(response) {
                    $('#shippingRatesTable').DataTable().ajax.reload();
                    notyf.success(response.message);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Something went wrong.');
                    notyf.error(response.error || 'Something went wrong.');
                }
            });
        }
    }
</script>

@endsection


