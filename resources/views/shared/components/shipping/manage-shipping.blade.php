@extends('admin.dashboard')
@section('title', 'NidusCart - Manage Promotions')
@section('content')

<section class="content-main">
    <div class="content-header">
        <div>
            <h2 class="content-title card-title">Shipping Rates</h2>
            <p>Manage Shipping Rates</p>
        </div>
        <div>
            <a href="#" class="btn btn-light rounded font-md">Export</a>
            <a href="#" class="btn btn-light rounded font-md">Import</a>
        </div>
    </div>
    <div class="card mb-4">
        <header class="card-header">
            <div>
                <button class="btn btn-sm btn-outline-dark add-btn" data-bs-toggle="modal" data-bs-target="#shippingModal" onclick="$('#shippingForm')[0].reset();"><strong>Add New shipping</strong></button>
            </div>
        </header>
        <div class="card-body">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped table-hover data-table">
                        <thead>
                            <tr>
                                <th width="15%">Class</th>
                                <th width="15%">Country</th>
                                <th width="10%">Method</th>
                                <th width="15%">Delivery Time</th>
                                <th width="15%">Free Shipping Order</th>
                                <th width="10%">Status</th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shippingModal" tabindex="-1" aria-labelledby="shippingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="shippingModal">Create New Shipping</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="row justify-content-center align mt-5">
                            <div class="col-md-10 card p-4 mb-4" id="rateSection">
                                <h3 class="card-title mb-4 text-start">Add Shipping</h3>
                                <form id="shippingForm" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" id="id">
                                    <!-- Zone and Shipping Method Section -->
                                    <h5>Basic Shipping</h5> <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label for="class">Class *</label>
                                                        <input type="text" name="class" id="class" class="form-control" placeholder="standard-shipping">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label for="country">Shipping Country *</label>
                                                        <select name="country" id="country" class="form-control">
                                                            <option value="">Select Country</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label for="method">Shipping Method *</label>
                                                        <select name="method" id="method" class="form-control">
                                                            <option value="Standard">Standard</option>
                                                            <option value="Express">Express</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label for="delivery_time">Estimated Delivery Time *</label>
                                                        <select name="delivery_time" id="delivery_time" class="form-control">
                                                            <option value="1-3">1-3 delivery days</option>
                                                            <option value="3-5">3-5 delivery days</option>
                                                            <option value="5-7">5-7 delivery days</option>
                                                            <option value="7-10">7-10 delivery days</option>
                                                            <option value="15">15-20 delivery days</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                             <!-- Free Shipping Section -->
                                            <div class="row">
                                                <h5>Free Shipping</h5>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label for="free_shipping_min_order">Minimum Order Amount</label>
                                                        <input type="number" step="0.01" name="free_shipping_min_order" id="free_shipping_min_order" class="form-control" value="0.00">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-4">
                                                        <label for="free_shipping_min_order">Default Country Shipping Rate</label>
                                                        <input type="number" step="0.01" name="def_country_price" id="def_country_price" class="form-control" value="0.00">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-warning mt-2" id="add-area">Add Area</button> <br>

                                            <button type="submit" id="submitBtn" class="btn btn-outline-secondary mt-4">Create Shipping Method</button>
                                        </div>
                                <div class="col-md-6">
                                            <!-- Area Section -->
                                    <div id="area-section" class="border rounded p-2">
                                        <!-- Initial Area Selection -->
                                        <div class="area-count">
                                            <div class="form-group mb-4">
                                                <h5>State</h5>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 form-group mb-4">
                                                    <label for="state">Shipping State *</label>
                                                    <select name="weight_cost_rules[0][state]" id="state" class="form-control">
                                                        <option value="">Select State</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6 form-group mb-4">
                                                    <label for="city">Shipping City *</label>
                                                    <select name="weight_cost_rules[0][city]" id="city" class="form-control">
                                                        <option value="">Select City</option>
                                                    </select>
                                                </div>

                                                <!-- Cost Calculation Method Section -->
                                                <div class="col-md-6 form-group mb-4">
                                                    <label for="shipping_calculation_method">Calculate Cost</label>
                                                    <select name="weight_cost_rules[0][shipping_calculation_method]" id="shipping_calculation_method" class="form-control">
                                                        <option value="per_unit">Per unit cost</option>
                                                        <option value="rules">Based on rules</option>
                                                    </select>
                                                </div>

                                                <!-- Per Unit Cost Section -->
                                                <div class="col-md-6 form-group mb-4" id="per_unit_cost_section">
                                                    <label for="per_unit_cost">Per Unit Cost (kg)</label>
                                                    <input type="number" step="0.01" name="weight_cost_rules[0][per_unit_cost]" id="per_unit_cost" class="form-control" value="0.00">
                                                </div>
                                            </div>

                                            <!-- Rules Section -->
                                            <div class="form-group mb-4" id="rules_section" style="display: none;">
                                                <div class="w-50">
                                                    <label for="default_rule_cost">Default Cost if No Matching Rule</label>
                                                    <input type="number" step="0.01" name="default_rule_cost" id="default_rule_cost" class="form-control" value="0.00">
                                                </div>

                                                <div id="weight_cost_rules_container" class="mt-3 mb-4 border rounded p-2">
                                                    <!-- Default Rule -->
                                                    <div class="row weight-cost-rule mb-3">
                                                        <div class="col-md-6 form-group mb-2">
                                                            <label for="weight">Weight (kg) - Upto</label>
                                                            <input type="number" step="0.01" name="weight_cost_rules[0][0][weight]" class="form-control" value="0.00">
                                                        </div>

                                                        <div class="col-md-6 form-group mb-2">
                                                            <label for="cost">Cost</label>
                                                            <input type="number" step="0.01" name="weight_cost_rules[0][0][cost]" class="form-control" value="0.00">
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-primary mt-2" id="add-rule">Add Rule</button>
                                            </div>
                                        </div>
                                    </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <div id="message" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

{{-- <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script> --}}
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.7.4/axios.min.js"></script>
<script type="module">
   import { Country, State, City } from 'https://cdn.jsdelivr.net/npm/country-state-city@3.2.1/+esm';

    document.addEventListener('DOMContentLoaded', () => {
        // Populate countries
        const countrySelect = document.getElementById('country');
        const countries = Country.getAllCountries();
        countries.forEach(country => {
            const option = document.createElement('option');
            option.value = country.isoCode;
            option.textContent = country.name;
            countrySelect.appendChild(option);
        });

        // Handle country change to populate states
        countrySelect.addEventListener('change', (event) => {
            const selectedCountry = event.target.value;
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

        // Handle state change to populate cities
        document.getElementById('state').addEventListener('change', (event) => {
            const selectedState = event.target.value;
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Select City</option>'; // Reset cities

            if (selectedState) {
                const cities = City.getCitiesOfState(countrySelect.value, selectedState);
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.name;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
            }
        });

        // Initialize the form based on the selected method
        const shippingCalculationMethodSelect = document.getElementById('shipping_calculation_method');
        shippingCalculationMethodSelect.addEventListener('change', () => {
            const method = shippingCalculationMethodSelect.value;
            const perUnitCostSection = document.getElementById('per_unit_cost_section');
            const rulesSection = document.getElementById('rules_section');

            if (method === 'per_unit') {
                perUnitCostSection.style.display = 'block';
                rulesSection.style.display = 'none';
            } else if (method === 'rules') {
                perUnitCostSection.style.display = 'none';
                rulesSection.style.display = 'block';
            }
        });

        shippingCalculationMethodSelect.dispatchEvent(new Event('change')); // Initialize the form based on the selected method

        // Add Rule button
        document.getElementById('add-rule').addEventListener('click', () => {
            const container = document.getElementById('weight_cost_rules_container');
            const ruleIndex = container.querySelectorAll('.weight-cost-rule').length;

            const newRule = `
                <div class="weight-cost-rule mb-3 border p-3">
                    <h5>New Weight Rule</h5>

                   <div class="row">
                         <div class="col-md-6 form-group mb-2">
                        <label for="weight_${ruleIndex}">Weight (kg) - Upto</label>
                        <input type="number" step="0.01" name="weight_cost_rules[0][${ruleIndex+1}][weight]" class="form-control" value="0.00">
                    </div>

                    <div class="col-md-6 form-group mb-2">
                        <label for="cost_${ruleIndex}">Cost</label>
                        <input type="number" step="0.01" name="weight_cost_rules[0][${ruleIndex+1}][cost]" class="form-control" value="0.00">
                    </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newRule);
        });

        // Add Area button
        document.getElementById('add-area').addEventListener('click', () => {
            const container = document.getElementById('area-section');
            const areaCount = container.querySelectorAll('.area-count').length;

            const newArea = `<div class="area-count mt-4 border rounded p-2">
                <h5>State ${areaCount + 1}</h5>
                <div class="row">
                    <div class="form-group mb-4 col-6">
                        <label for="state_${areaCount}">Shipping State *</label>
                        <select name="weight_cost_rules_${areaCount}[state]" id="state_${areaCount}" class="form-control">
                            <option value="">Select State</option>
                        </select>
                    </div>

                    <div class="form-group mb-4 col-6">
                        <label for="city_${areaCount}">Shipping City *</label>
                        <select name="weight_cost_rules_${areaCount}[city]" id="city_${areaCount}" class="form-control">
                            <option value="">Select City</option>
                        </select>
                    </div>

                    <div class="form-group mb-4 col-6">
                        <label for="shipping_calculation_method_${areaCount}">Calculate Cost</label>
                        <select name="weight_cost_rules_${areaCount}[shipping_calculation_method]" id="shipping_calculation_method_${areaCount}" class="form-control">
                            <option value="per_unit">Per unit cost</option>
                            <option value="rules">Based on rules</option>
                        </select>
                    </div>

                    <div class="form-group mb-4 col-6" id="per_unit_cost_section_${areaCount}">
                        <label for="per_unit_cost_${areaCount}">Per Unit Cost (kg)</label>
                        <input type="number" step="0.01" name="weight_cost_rules_[0]${areaCount}[per_unit_cost]" id="per_unit_cost_${areaCount}" class="form-control" value="0.00">
                    </div>

                    <div class="form-group mb-4 col-12" id="rules_section_${areaCount}" style="display: none;">
                        <label for="default_weight_cost${areaCount}">Default Cost if No Matching Rule</label>
                        <input type="number" step="0.01" name="weight_cost_rules_[0]${areaCount}[default_weight_cost]" id="default_weight_cost${areaCount}" class="form-control" value="0.00">

                        <div id="weight_cost_rules_container_${areaCount}" class="mt-3 mb-4 border rounded p-2">
                            <div class="weight-cost-rule mb-3 row">
                                <div class="form-group mb-2 col-6">
                                    <label for="weight_${areaCount}">Weight (kg) - Upto</label>
                                    <input type="number" step="0.01" name="weight_cost_rules_[0]${areaCount}[weight]" class="form-control" value="0.00">
                                </div>

                                <div class="form-group mb-2 col-6">
                                    <label for="cost_${areaCount}">Cost</label>
                                    <input type="number" step="0.01" name="weight_cost_rules_[0]${areaCount}[cost]" class="form-control" value="0.00">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" id="add-rule-${areaCount}">Add Rule</button>
                    </div>
                </div>
            </div>`;

            container.insertAdjacentHTML('beforeend', newArea);

            const selectedCountry = document.getElementById('country').value;
            const stateSelect = document.getElementById(`state_${areaCount}`);
            const citySelect = document.getElementById(`city_${areaCount}`);
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

            document.getElementById(`state_${areaCount}`).addEventListener('change', (event) => {
            const selectedState = event.target.value;
            const citySelect = document.getElementById(`city_${areaCount}`);
            citySelect.innerHTML = '<option value="">Select City</option>'; // Reset cities

            if (selectedState) {
                const cities = City.getCitiesOfState(countrySelect.value, selectedState);
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.name;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
            }
        });

            // Add event listeners for newly created elements
document.getElementById(`shipping_calculation_method_${areaCount}`).addEventListener('change', function () {
    const method = this.value;
    const perUnitCostSection = document.getElementById(`per_unit_cost_section_${areaCount}`);
    const rulesSection = document.getElementById(`rules_section_${areaCount}`);

    if (method === 'per_unit') {
        perUnitCostSection.style.display = 'block';
        rulesSection.style.display = 'none';
    } else if (method === 'rules') {
        perUnitCostSection.style.display = 'none';
        rulesSection.style.display = 'block';
    }
});

document.getElementById(`add-rule-${areaCount}`).addEventListener('click', function () {
    const ruleContainer = document.getElementById(`weight_cost_rules_container_${areaCount}`);
    const ruleIndex = ruleContainer.querySelectorAll('.weight-cost-rule').length;

    const newRule = `
        <div class="weight-cost-rule mb-3 border p-3">
            <h5>New Weight Rule</h5>

            <div class="row">
                 <div class="col-md-6 form-group mb-2">
                    <label for="weight_${areaCount}_${ruleIndex}">Weight (kg) - Upto</label>
                    <input type="number" step="0.01" name="weight_cost_rules${areaCount}[${ruleIndex}][weight]" class="form-control" value="0.00">
                </div>

                <div class="col-md-6 form-group mb-2">
                    <label for="cost_${areaCount}_${ruleIndex}">Cost</label>
                    <input type="number" step="0.01" name="weight_cost_rules${areaCount}[${ruleIndex}][cost]" class="form-control" value="0.00">
                </div>
            </div>
        </div>
    `;
    ruleContainer.insertAdjacentHTML('beforeend', newRule);
});
});
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
const form = document.querySelector('#shippingForm');

form.addEventListener('submit', (event) => {
// alert('asdf');
event.preventDefault();

// Clear previous errors
clearErrors();

const formData = new FormData(form);

// Perform validation
let hasErrors = false;
// if (!formData.get('free_shipping_min_order') || formData.get('free_shipping_min_order') <= 0) {
//     setError('free_shipping_min_order', 'Minimum order amount must be greater than zero.');
//     hasErrors = true;
// }

if (!formData.get('class')) {
    setError('class', 'Shipping class is required.');
    hasErrors = true;
}

if (!formData.get('country')) {
    setError('country', 'Shipping country is required.');
    hasErrors = true;
}

if (!formData.get('method')) {
    setError('method', 'Shipping method is required.');
    hasErrors = true;
}

if (hasErrors) {
    return;
}

axios.post('/shipping/store', formDataToJSON(formData), {
    headers: {
        'Content-Type': 'application/json'
    }
})
.then(response => {
    // Handle success
    notyf.success(response.data.success || "Product saved successfully!");
    $('.data-table').DataTable().ajax.reload(null, false);
    // Optionally close the modal after a successful save
    document.getElementById('shippingForm').reset();
    $('#shippingModal').modal('hide');
})
.catch(error => {
    console.error('Error:', error);

    if (error.response) {
        // Extract the general message
        const generalMessage = error.response.data.message || 'An error occurred on the server.';

        // Extract validation errors if they exist
        let validationErrors = '';
        if (error.response.data.errors) {
            const errors = error.response.data.errors;
            validationErrors = Object.values(errors)
                .flat() // Flatten arrays of error messages
                .join(' '); // Join all messages into a single string
        }

        // Display the appropriate error message
        notyf.error(`${generalMessage} ${validationErrors}`);
    } else if (error.request) {
        notyf.error('No response received from the server. Please check your network connection.');
    } else {
        notyf.error(error.message || 'An unexpected error occurred.');
    }
});

});

function setError(field, message) {
const fieldElement = document.getElementById(field);
fieldElement.classList.add('is-invalid');
const errorElement = document.createElement('div');
errorElement.className = 'invalid-feedback';
errorElement.innerText = message;
fieldElement.parentElement.appendChild(errorElement);
}

function clearErrors() {
document.querySelectorAll('.is-invalid').forEach(element => element.classList.remove('is-invalid'));
document.querySelectorAll('.invalid-feedback').forEach(element => element.remove());
}

function formDataToJSON(formData) {
const object = {};
formData.forEach((value, key) => {
    if (key.includes('[')) {
        const keys = key.split(/\[|\]/).filter(k => k);
        keys.reduce((prev, curr, index) => {
            if (!prev[curr]) {
                prev[curr] = (index === keys.length - 1) ? value : {};
            }
            return prev[curr];
        }, object);
    } else {
        object[key] = value;
    }
});
return object;
}

});
</script>

<script type="text/javascript">
$(function () {
var table = $('.data-table').DataTable({
processing: true,
serverSide: true,
ajax: "{{ route('shipping.index') }}",
columns: [
    {data: 'shipping_class', name: 'shipping_class'},
    {data: 'country', name: 'country'},
    {data: 'method', name: 'method'},
    {data: 'delivery_time', name: 'delivery_time'},
    {data: 'free_shipping_min_order', name: 'free_shipping_min_order'},
    {data: 'status', name: 'status'},
    {data: 'action', name: 'action', orderable: false, searchable: false},
]
});
});

$(document).on('click', '.edit-shipping', function() {
// Get the shipping ID from the data attribute
document.getElementById('shippingForm').reset();
let shippingId = $(this).data('id');

// Send an Axios GET request to fetch the shipping data
axios.get(`/edit-shipping/${shippingId}/`)
.then(function(response) {
    let shippingData = response.data;
    let shippingDetails = JSON.parse(shippingData.shipping_details);
    console.log(shippingDetails);


    // Populate the form fields with the received data
    $('#id').val(shippingData.id);
    $('#class').val(shippingData.shipping_class);
    $('#country').val(shippingData.country);
    $('#method').val(shippingData.method);
    $('#delivery_time').val(shippingData.delivery_time);
    $('#free_shipping_min_order').val(shippingData.free_shipping_min_order);
    $('#def_country_price').val(shippingData.def_country_price);
    $('#submitBtn').text("Update Shipping Method");


    // Populate the area section if applicable
    if (shippingDetails.areas && shippingDetails.areas.length > 0) {
        shippingDetails.areas.forEach((area, index) => {
            console.log(area);
            if (index > 0) {
                $('#add-area').click(); // Add new area section
            }

            let areaSection = $(`#area-section .area-count:eq(${index})`);

            // Add the state, city, and cost calculation method values
            areaSection.find(`select[name="weight_cost_rules[${index}][state]"]`).val(area.state);
            areaSection.find(`select[name="weight_cost_rules[${index}][city]"]`).val(area.city);
            areaSection.find(`select[name="weight_cost_rules[${index}][shipping_calculation_method]"]`).val(area.shipping_calculation_method).change();

            // Check if cost calculation method is 'per_unit' or 'rules' and show the appropriate section
            if (area.shipping_calculation_method === 'per_unit') {
                areaSection.find(`input[name="weight_cost_rules[${index}][per_unit_cost]"]`).val(area.per_unit_cost);
            } else if (area.shipping_calculation_method === 'rules') {
                areaSection.find(`#default_rule_cost`).val(area.default_rule_cost);

                area.rules.forEach((rule, ruleIndex) => {
                    if (ruleIndex > 0) {
                        areaSection.find(`#add-rule-${index}`).click(); // Add new rule section
                    }

                    // Populate each rule
                    let ruleSection = areaSection.find(`#weight_cost_rules_container_${index} .weight-cost-rule:eq(${ruleIndex})`);
                    ruleSection.find(`input[name="weight_cost_rules[${index}][${ruleIndex}][weight]"]`).val(rule.weight);
                    ruleSection.find(`input[name="weight_cost_rules[${index}][${ruleIndex}][cost]"]`).val(rule.cost);
                });
            }
        });
    }

    // Show the modal
    $('#shippingModal').modal('show');
})
.catch(function(error) {
    console.log(error);
});
});

$(document).on('click', '.change-status', function(e) {
e.preventDefault();

var status = $(this).data('status');
var id = $(this).data('id');

// Ask for confirmation
if (confirm('Are you sure you want to change the status to ' + status + '?')) {
// Send AJAX request to update the status
$.ajax({
    url: '{{ route("update.shippingStatus") }}', // Using Laravel's route helper
    type: 'POST',
    data: {
        _token: '{{ csrf_token() }}',
        id: id,
        status: status
    },
    success: function(response) {
        if (response.success) {
            notyf.success(response.success);
        } else {
            notyf.error(response.error || 'An unexpected error occurred.');
        }
        $('.data-table').DataTable().ajax.reload(null, false);

    },
    error: function(xhr) {
        let errorMessage = 'An error occurred while updating the status.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        notyf.error(errorMessage);
    }
});
}
});

// delete promotion
$(document).ready(function() {
$(document).on('click', '.delete-shipping', function() {
var promotionId = $(this).data('id');
var token = $('meta[name="csrf-token"]').attr('content'); // Ensure you have CSRF token in your meta tags

if (confirm('Are you sure you want to delete this promotion?')) {
    $.ajax({
        url: '/shipping/delete/' + promotionId,
        type: 'DELETE',
        data: {
            "_token": token, // CSRF token
        },
        success: function(response) {
            notyf.success(response.success || "Shipping deleted!"); // Show success notification
            $('.data-table').DataTable().ajax.reload(null, false);
        },
        error: function(xhr) {
            notyf.error('An error occurred while trying to delete the promotion. Please try again.'); // Show error notification
        }
    });
}
});
});
</script>
@endsection
