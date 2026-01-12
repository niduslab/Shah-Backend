@extends('layouts.app')
@section('title', 'Weight Cost Rules | Niduscart')
@section('content')

<div class="container p-5">
    <div class="card mb-4">
        <header class="card-header">
            <h2>Manage Weight Cost Rules</h2>
            <button class="btn btn-primary mb-3" onclick="openCreateModal()">Add Weight Cost Rule</button>
        </header>

        <div class="card-body">
            <table id="weightCostRulesTable" class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Shipping Rate Country</th>
                        <th>State</th>
                        <th>City</th>
                        <th>Calculation Method</th>
                        <th>Per Unit Cost</th>
                        <th>Default Rule Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="weightCostRuleModal" tabindex="-1" aria-labelledby="weightCostRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="weightCostRuleForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="weightCostRuleModalLabel">Add Weight Cost Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="weightCostRuleId">
                    <div class="mb-3">
                        <label for="shipping_rate_id" class="form-label">Shipping Rate</label>
                        <select class="form-select" id="shipping_rate_id" name="shipping_rate_id" required>
                            <option value="">Select Shipping Rate</option>
                            @foreach ($shippingRates as $shippingRate)
                                <option value="{{ $shippingRate->id }}" data-country="{{ $shippingRate->country }}" data>{{ $shippingRate->class }}</option>
                            @endforeach
                        </select>
                    </div>
                     <!-- State Selection -->
                     <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <select class="form-select" id="state" name="state" required>
                            <option value="">Select State</option>
                        </select>
                    </div>

                    <!-- City Selection -->
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <select class="form-select" id="city" name="city">
                            <option value="">Select City</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="shipping_calculation_method" class="form-label">Calculation Method</label>
                        <select class="form-select" id="shipping_calculation_method" name="shipping_calculation_method" required>
                            <option value="per_unit">Per Unit</option>
                            <option value="rules">Rules</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="per_unit_cost" class="form-label">Per Unit Cost</label>
                        <input type="number" step="0.01" class="form-control" id="per_unit_cost" name="per_unit_cost">
                    </div>
                    <div class="mb-3">
                        <label for="default_rule_cost" class="form-label">Default Rule Cost</label>
                        <input type="number" step="0.01" class="form-control" id="default_rule_cost" name="default_rule_cost">
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
            const shippingRateSelect = document.getElementById('shipping_rate_id');
            let countrySelect = '{{ $shippingRates->first()->country }}';
            shippingRateSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const country = selectedOption.getAttribute('data-country');
                countrySelect = country;
                const selectedCountry = country;
                const stateSelect = document.getElementById('state');
                const citySelect = document.getElementById('city');
                stateSelect.innerHTML = '<option value="">Select State</option>'; // Reset areas
                citySelect.innerHTML = '<option value="">Select City</option>'; // Reset cities

                if (selectedCountry) {
                    const states = State.getStatesOfCountry(selectedCountry);
                    states.forEach(state => {
                        const option = document.createElement('option');
                        option.value = state.isoCode;
                        option.textContent = state.name;
                        stateSelect.appendChild(option);
                    });
                }
            });

            document.getElementById('state').addEventListener('change', (event) => {
            const selectedState = event.target.value;
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Select City</option>'; // Reset cities

            if (selectedState) {
                const cities = City.getCitiesOfState(countrySelect, selectedState);
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.name;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
            }
        });
       });
   </script>

<script type="text/javascript">
    // Initialize Datatable
    $(document).ready(function() {
        $('#weightCostRulesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('weight-cost-rules.index') }}',
            columns: [
                { data: 'id' },
                { data: 'country' }, // Add this line for the country
                { data: 'state' },
                { data: 'city' },
                { data: 'shipping_calculation_method' },
                { data: 'per_unit_cost' },
                { data: 'default_rule_cost' },
                { data: 'actions', orderable: false, searchable: false }
            ]
        });
    });

    // Open modal for creating or editing weight cost rule
    function openCreateModal() {
        $('#weightCostRuleForm').trigger("reset");
        $('#weightCostRuleId').val('');
        $('#weightCostRuleModalLabel').text("Add Weight Cost Rule");
        $('#weightCostRuleModal').modal('show');
    }

    function openEditModal(id) {
        $.get(`/weight-cost-rules/${id}`, function(data) {
            $('#weightCostRuleModalLabel').text("Edit Weight Cost Rule");
            $('#weightCostRuleId').val(data.id);
            $('#shipping_rate_id').val(data.shipping_rate_id);
            $('#state').val(data.state);
            $('#city').val(data.city);
            $('#shipping_calculation_method').val(data.shipping_calculation_method);
            $('#per_unit_cost').val(data.per_unit_cost);
            $('#default_rule_cost').val(data.default_rule_cost);
            $('#weightCostRuleModal').modal('show');
        });
    }

    // Handle form submission
    $('#weightCostRuleForm').submit(function(event) {
        event.preventDefault();
        let id = $('#weightCostRuleId').val();
        let method = id ? 'PUT' : 'POST';
        let url = id ? `/weight-cost-rules/${id}` : '{{ route('weight-cost-rules.store') }}';

        axios({
            method: method,
            url: url,
            data: $(this).serialize()
        })
        .then(response => {
            $('#weightCostRuleModal').modal('hide');
            $('#weightCostRulesTable').DataTable().ajax.reload();
            alert(response.data.message);
        })
        .catch(error => {
            console.error(error);
            alert('Error occurred while saving the rule.');
        });
    });
</script>

@endsection
